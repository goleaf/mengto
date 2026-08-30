<?php

declare(strict_types=1);

use App\Actions\CancelForumEvent;
use App\Actions\PublishForumEvent;
use App\Actions\RevokeForumEventInvitation;
use App\Actions\TransitionForumEventStatus;
use App\Actions\UpdateForumEvent;
use App\Data\RegisterForForumEventData;
use App\Data\UpdateForumEventData;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventVerificationStatus;
use App\Enums\ForumEventVisibility;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfilePermission;
use App\Livewire\Forum\ForumEventDirectory;
use App\Livewire\Forum\ForumEventWorkspace;
use App\Models\ForumEvent;
use App\Models\ForumEventHistory;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventParticipationTransition;
use App\Models\ForumEventRegistration;
use App\Models\ForumNotification;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\PetProfile;
use App\Models\PetProfileManager;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\SocialAccountBlock;
use App\Models\User;
use App\Services\ForumEventRegistrationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function meetupRegistrationData(
    string $key,
    int $guestCount = 0,
    array $petProfileIds = [],
): RegisterForForumEventData {
    return new RegisterForForumEventData(
        attendanceFormat: ForumEventFormat::Physical,
        guestCount: $guestCount,
        petProfileId: null,
        requirementsNote: null,
        photoConsent: ForumEventPhotoConsent::AskFirst,
        requirementsAccepted: true,
        idempotencyKey: $key,
        petProfileIds: $petProfileIds,
    );
}

function meetupUpdateData(ForumEvent $event, array $overrides = []): UpdateForumEventData
{
    return new UpdateForumEventData(
        title: (string) ($overrides['title'] ?? $event->title),
        summary: (string) ($overrides['summary'] ?? $event->summary),
        type: $overrides['type'] ?? $event->type,
        visibility: $overrides['visibility'] ?? $event->visibility,
        registrationPolicy: $overrides['registrationPolicy'] ?? $event->registration_policy,
        petParticipationMode: $overrides['petParticipationMode'] ?? $event->pet_participation_mode,
        capacity: array_key_exists('capacity', $overrides) ? $overrides['capacity'] : $event->capacity,
        waitlistEnabled: (bool) ($overrides['waitlistEnabled'] ?? $event->waitlist_enabled),
        locationScope: $overrides['locationScope'] ?? $event->location_scope,
        exactLocation: $overrides['exactLocation'] ?? $event->exact_location,
        attendanceRequirements: $overrides['attendanceRequirements'] ?? $event->attendance_requirements,
        accessibilityInformation: $overrides['accessibilityInformation'] ?? $event->accessibility_information,
        animalWelfareRules: (string) ($overrides['animalWelfareRules'] ?? $event->animal_welfare_rules),
        emergencyContactPlan: (string) ($overrides['emergencyContactPlan'] ?? $event->emergency_contact_plan),
        idempotencyKey: (string) ($overrides['idempotencyKey'] ?? str()->uuid()),
    );
}

test('organization membership never broadens a private meetup visibility', function (): void {
    $organizer = User::factory()->create();
    $member = User::factory()->create();
    $organization = Organization::factory()->forOwner($organizer)->create();
    OrganizationMembership::factory()
        ->for($organization)
        ->for($member)
        ->active()
        ->create();
    $private = ForumEvent::factory()->forOrganizer($organizer)->create([
        'responsible_organization_id' => $organization->id,
        'visibility' => ForumEventVisibility::Private,
        'title' => 'Private organization planning walk',
        'location_scope' => 'Restricted riverside area',
    ]);

    expect(ForumEvent::query()->visibleTo($member)->whereKey($private)->exists())
        ->toBeFalse()
        ->and(Gate::forUser($member)->allows('view', $private))->toBeFalse();

    $this->actingAs($member)
        ->get(route('meetups.index', ['period' => 'all']))
        ->assertOk()
        ->assertDontSee($private->title)
        ->assertDontSee('Restricted riverside area');
});

test('pending invitee can reach only the safe invitation response boundary', function (): void {
    $recipient = User::factory()->create();
    $event = ForumEvent::factory()->invitationOnly()->create([
        'title' => 'Invitation-only quiet pet social',
        'location_scope' => 'Vilnius',
        'exact_location' => 'Private gate code 9713',
    ]);
    ForumEventInvitation::factory()
        ->for($event, 'event')
        ->for($recipient, 'recipient')
        ->create();

    $response = $this->actingAs($recipient)->get(route('meetups.show', $event));

    $response
        ->assertOk()
        ->assertSee($event->title)
        ->assertSee(__('forum_events.notifications.invitation_title'))
        ->assertDontSee('Private gate code 9713')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

    expect(Gate::forUser($recipient)->allows('viewAccessDetails', $event))->toBeFalse();
});

test('public draft is absent from every directory period and direct outsider access', function (): void {
    $organizer = User::factory()->create();
    $outsider = User::factory()->create();
    $draft = ForumEvent::factory()->forOrganizer($organizer)->draft()->create([
        'visibility' => ForumEventVisibility::Public,
        'title' => 'Unpublished meetup draft marker',
    ]);

    foreach (['upcoming', 'past', 'all'] as $period) {
        $this->actingAs($outsider)
            ->get(route('meetups.index', ['period' => $period]))
            ->assertOk()
            ->assertDontSee($draft->title);
    }

    $this->actingAs($outsider)
        ->get(route('meetups.show', $draft))
        ->assertForbidden();

    $this->actingAs($organizer)
        ->get(route('meetups.show', $draft))
        ->assertOk()
        ->assertSee($draft->title);
});

test('canonical account block prevents meetup discovery direct access and registration', function (): void {
    $organizer = User::factory()->create();
    $blocked = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->forOrganizer($organizer)->create([
        'title' => 'Blocked contact meetup marker',
    ]);
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($blocked, 'user')
        ->confirmed()
        ->create();
    SocialAccountBlock::factory()->create([
        'blocker_user_id' => $organizer->id,
        'blocked_user_id' => $blocked->id,
        'created_by_user_id' => $organizer->id,
    ]);

    expect(ForumEvent::query()->visibleTo($blocked)->whereKey($event)->exists())
        ->toBeFalse()
        ->and(Gate::forUser($blocked)->allows('view', $event))->toBeFalse()
        ->and(Gate::forUser($blocked)->allows('register', $event))->toBeFalse()
        ->and(Gate::forUser($blocked)->allows('viewAccessDetails', $event))->toBeFalse()
        ->and(Gate::forUser($blocked)->allows('sendMessage', $event))->toBeFalse();

    $this->actingAs($blocked)
        ->get(route('meetups.index'))
        ->assertOk()
        ->assertDontSee($event->title);

    $this->actingAs($blocked)
        ->get(route('meetups.show', $event))
        ->assertForbidden();

    expect(fn () => app(ForumEventRegistrationService::class)->register(
        $blocked,
        $event,
        meetupRegistrationData('meetup-blocked-register-000001'),
    ))->toThrow(AuthorizationException::class);
});

test('confirmed invite-only attendee keeps safe meetup access after invitation expiry', function (): void {
    $attendee = User::factory()->create();
    $event = ForumEvent::factory()->invitationOnly()->create();
    ForumEventInvitation::factory()
        ->for($event, 'event')
        ->for($attendee, 'recipient')
        ->create([
            'status' => ForumEventInvitationStatus::Accepted,
            'expires_at' => now()->subMinute(),
            'responded_at' => now()->subDay(),
        ]);
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($attendee, 'user')
        ->confirmed()
        ->create();

    expect(Gate::forUser($attendee)->allows('view', $event))->toBeTrue()
        ->and(ForumEvent::query()->visibleTo($attendee)->whereKey($event)->exists())
        ->toBeTrue();

    $this->actingAs($attendee)
        ->get(route('meetups.show', $event))
        ->assertOk()
        ->assertSee($event->title);
});

test('registration operation binds active scope and rejects changed payload replay', function (): void {
    $attendee = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->create(['capacity' => 10]);
    $service = app(ForumEventRegistrationService::class);
    $key = 'meetup-registration-replay-000001';

    $registration = $service->register(
        $attendee,
        $event,
        meetupRegistrationData($key),
    );
    $replayed = $service->register(
        $attendee,
        $event,
        meetupRegistrationData($key),
    );

    expect($replayed->is($registration))->toBeTrue()
        ->and($registration->active_scope_key)->toBeString()->not->toBe('')
        ->and(ForumEventRegistration::query()
            ->where('active_scope_key', $registration->active_scope_key)
            ->count())->toBe(1)
        ->and(ForumEventParticipationTransition::query()
            ->where('forum_event_registration_id', $registration->id)
            ->where('to_status', ForumEventRegistrationStatus::Confirmed->value)
            ->count())->toBe(1);

    expect(fn () => $service->register(
        $attendee,
        $event,
        meetupRegistrationData($key, guestCount: 1),
    ))->toThrow(ValidationException::class);
});

test('only an active pet owner or active care or social manager may attach a pet to meetup participation', function (): void {
    $owner = User::factory()->create(['email_verified_at' => now()]);
    $caregiver = User::factory()->create(['email_verified_at' => now()]);
    $outsider = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->create(['capacity' => 10]);
    $pet = PetProfile::factory()->for($owner)->privateProfile()->create();
    PetProfileManager::factory()->for($pet, 'profile')->for($caregiver)->create([
        'role' => PetManagerRole::Caregiver,
        'status' => PetManagerStatus::Active,
    ]);
    $service = app(ForumEventRegistrationService::class);

    $ownerRegistration = $service->register(
        $owner,
        $event,
        meetupRegistrationData('meetup-owner-pet-000000000001', petProfileIds: [$pet->id]),
    );
    $caregiverRegistration = $service->register(
        $caregiver,
        $event,
        meetupRegistrationData('meetup-caregiver-pet-00000001', petProfileIds: [$pet->id]),
    );

    expect($ownerRegistration->pets()->whereKey($pet)->exists())->toBeTrue()
        ->and($caregiverRegistration->pets()->whereKey($pet)->exists())->toBeTrue();

    expect(fn () => $service->register(
        $outsider,
        $event,
        meetupRegistrationData('meetup-foreign-pet-0000000001', petProfileIds: [$pet->id]),
    ))->toThrow(ValidationException::class);
});

test('view-only pet managers cannot represent a pet at a meetup', function (): void {
    $owner = User::factory()->create();
    $previousOwner = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->create();
    $pet = PetProfile::factory()->for($owner)->create();
    PetProfileManager::factory()->for($pet, 'profile')->for($previousOwner)->create([
        'role' => PetManagerRole::PreviousOwner,
        'status' => PetManagerStatus::Active,
    ]);

    expect(fn () => app(ForumEventRegistrationService::class)->register(
        $previousOwner,
        $event,
        meetupRegistrationData('meetup-view-only-manager-0001', petProfileIds: [$pet->id]),
    ))->toThrow(ValidationException::class);
});

test('pending expired revoked and explicitly denied pet access cannot be used for meetup participation', function (
    PetManagerStatus $status,
    ?array $overrides,
    ?string $endsAt,
): void {
    $owner = User::factory()->create();
    $manager = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->create();
    $pet = PetProfile::factory()->for($owner)->create();
    PetProfileManager::factory()->for($pet, 'profile')->for($manager)->create([
        'role' => PetManagerRole::Caregiver,
        'status' => $status,
        'permission_overrides' => $overrides,
        'ends_at' => $endsAt,
        'revoked_at' => $status === PetManagerStatus::Revoked ? now() : null,
    ]);

    expect(fn () => app(ForumEventRegistrationService::class)->register(
        $manager,
        $event,
        meetupRegistrationData('meetup-invalid-pet-access-0001', petProfileIds: [$pet->id]),
    ))->toThrow(ValidationException::class);
})->with([
    'pending invitation' => [PetManagerStatus::Invited, null, null],
    'expired term' => [PetManagerStatus::Active, null, '-1 minute'],
    'revoked access' => [PetManagerStatus::Revoked, null, null],
    'denied representation' => [PetManagerStatus::Active, ['deny' => [
        PetProfilePermission::ManageCare->value,
        PetProfilePermission::ManageSocial->value,
    ]], null],
]);

test('exact meetup location is absent from html and livewire state unless participation is confirmed', function (
    ForumEventRegistrationStatus $status,
    bool $canSeeExactLocation,
): void {
    $viewer = User::factory()->create();
    $event = ForumEvent::factory()->create([
        'title' => 'Privacy boundary meetup',
        'location_scope' => 'Vilnius city centre',
        'exact_location' => 'SECRET-LOCATION-MARKER gate 7712',
    ]);
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($viewer, 'user')
        ->create(['status' => $status]);

    $response = $this->actingAs($viewer)->get(route('meetups.show', $event));

    $response->assertOk()->assertSee('Vilnius city centre');

    if ($canSeeExactLocation) {
        $response->assertSee('SECRET-LOCATION-MARKER gate 7712');
    } else {
        $response->assertDontSee('SECRET-LOCATION-MARKER gate 7712');
    }
})->with([
    'requested' => [ForumEventRegistrationStatus::Pending, false],
    'waitlisted' => [ForumEventRegistrationStatus::Waitlisted, false],
    'removed' => [ForumEventRegistrationStatus::CancelledByOrganizer, false],
    'confirmed' => [ForumEventRegistrationStatus::Confirmed, true],
]);

test('canonical create edit and management routes enforce server authorization without mutating state', function (): void {
    $organizer = User::factory()->create(['email_verified_at' => now()]);
    $outsider = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->forOrganizer($organizer)->create();
    $originalUpdatedAt = $event->updated_at;

    $createResponse = $this->actingAs($organizer)
        ->get(route('meetups.create'))
        ->assertOk()
        ->assertSee(__('forum_events.page.create_heading'));
    $createXPath = responseXPath($createResponse);
    expect($createXPath->query('//main//h1')->length)->toBe(1)
        ->and($createXPath->query('//main//fieldset/legend')->length)->toBeGreaterThanOrEqual(3)
        ->and($createXPath->query('//main//button[@type="submit"]')->length)->toBeGreaterThan(0);
    $this->actingAs($organizer)->get(route('meetups.edit', $event))->assertOk();
    $this->actingAs($organizer)->get(route('meetups.manage', $event))->assertOk();

    $this->actingAs($outsider)->get(route('meetups.edit', $event))->assertForbidden();
    $this->actingAs($outsider)->get(route('meetups.manage', $event))->assertForbidden();

    expect($event->refresh()->updated_at->equalTo($originalUpdatedAt))->toBeTrue();
});

test('only the organizer can publish a complete future draft and publication is audited', function (): void {
    $organizer = User::factory()->create(['email_verified_at' => now()]);
    $outsider = User::factory()->create(['email_verified_at' => now()]);
    $draft = ForumEvent::factory()->forOrganizer($organizer)->draft()->create();

    expect(fn () => app(PublishForumEvent::class)->handle($outsider, $draft))
        ->toThrow(AuthorizationException::class);

    $published = app(PublishForumEvent::class)->handle($organizer, $draft);

    expect($published->status)->toBe(ForumEventStatus::Scheduled)
        ->and(ForumEventHistory::query()
            ->where('forum_event_id', $draft->id)
            ->where('event_type', 'published')
            ->count())->toBe(1)
        ->and(ForumEvent::query()->visibleTo($outsider)->whereKey($draft)->exists())
        ->toBeTrue();

    $pastDraft = ForumEvent::factory()->forOrganizer($organizer)->draft()->create([
        'starts_at' => now()->subHours(2),
        'ends_at' => now()->subHour(),
    ]);

    expect(fn () => app(PublishForumEvent::class)->handle($organizer, $pastDraft))
        ->toThrow(ValidationException::class);
});

test('material organizer edits are authorized capacity safe encrypted and notify confirmed attendees without leaking location', function (): void {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create(['capacity' => 3]);
    ForumEventRegistration::factory()->for($event, 'event')->for($attendee, 'user')->confirmed()->create([
        'guest_count' => 1,
    ]);
    $update = app(UpdateForumEvent::class);

    expect(fn () => $update->handle(
        $outsider,
        $event,
        meetupUpdateData($event, ['title' => 'Forged organizer edit']),
    ))->toThrow(AuthorizationException::class);

    expect(fn () => $update->handle(
        $organizer,
        $event,
        meetupUpdateData($event, ['capacity' => 1]),
    ))->toThrow(ValidationException::class);

    $updated = $update->handle($organizer, $event, meetupUpdateData($event, [
        'capacity' => 4,
        'locationScope' => 'Vilnius riverside area',
        'exactLocation' => 'EDITED-SECRET-LOCATION gate 4455',
    ]));

    expect($updated->capacity)->toBe(4)
        ->and($updated->exact_location)->toBe('EDITED-SECRET-LOCATION gate 4455')
        ->and((string) DB::table('forum_events')->whereKey($event)->value('exact_location'))
        ->not->toContain('EDITED-SECRET-LOCATION')
        ->and(ForumNotification::query()->where('user_key', $attendee->actor_key)->count())
        ->toBe(1)
        ->and(ForumNotification::query()->where('user_key', $attendee->actor_key)->value('body'))
        ->not->toContain('EDITED-SECRET-LOCATION');
});

test('organizer removal revokes participant location access and deterministically promotes the waitlist', function (): void {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $waitlistedUser = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create([
        'capacity' => 1,
        'exact_location' => 'REMOVAL-SECRET-LOCATION gate 1122',
    ]);
    $confirmed = ForumEventRegistration::factory()
        ->for($event, 'event')->for($attendee, 'user')->confirmed()->create();
    $waitlisted = ForumEventRegistration::factory()
        ->for($event, 'event')->for($waitlistedUser, 'user')->waitlisted(1)->create();

    expect(fn () => app(ForumEventRegistrationService::class)->remove($outsider, $confirmed))
        ->toThrow(AuthorizationException::class);

    app(ForumEventRegistrationService::class)->remove($organizer, $confirmed);

    expect($confirmed->refresh()->status)->toBe(ForumEventRegistrationStatus::CancelledByOrganizer)
        ->and($confirmed->active_scope_key)->toBeNull()
        ->and($waitlisted->refresh()->status)->toBe(ForumEventRegistrationStatus::Confirmed)
        ->and(Gate::forUser($attendee)->allows('viewAccessDetails', $event))->toBeFalse();

    $this->actingAs($attendee)
        ->get(route('meetups.show', $event))
        ->assertOk()
        ->assertDontSee('REMOVAL-SECRET-LOCATION');
});

test('stale organizer approval rechecks participant block account and pet authority', function (): void {
    $organizer = User::factory()->create();
    $owner = User::factory()->create();
    $participant = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->forOrganizer($organizer)->approvalRequired()->create();
    $pet = PetProfile::factory()->for($owner)->create();
    $manager = PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($participant)
        ->create(['role' => PetManagerRole::Caregiver]);
    $service = app(ForumEventRegistrationService::class);
    $registration = $service->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-stale-approval-pet-0001', petProfileIds: [$pet->id]),
    );
    $manager->forceFill([
        'status' => PetManagerStatus::Revoked,
        'revoked_at' => now(),
    ])->save();

    expect(fn () => $service->review($organizer, $registration, true))
        ->toThrow(ValidationException::class)
        ->and($registration->refresh()->status)->toBe(ForumEventRegistrationStatus::Pending);
});

test('waitlist promotion expires stale pet authority and promotes the next eligible participant', function (): void {
    $organizer = User::factory()->create();
    $owner = User::factory()->create();
    $occupant = User::factory()->create();
    $staleParticipant = User::factory()->create();
    $eligibleParticipant = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->withCapacity(1)->create();
    $pet = PetProfile::factory()->for($owner)->create();
    $manager = PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($staleParticipant)
        ->create(['role' => PetManagerRole::Caregiver]);
    $service = app(ForumEventRegistrationService::class);
    $confirmed = $service->register(
        $occupant,
        $event,
        meetupRegistrationData('meetup-waitlist-occupant-000001'),
    );
    $stale = $service->register(
        $staleParticipant,
        $event,
        meetupRegistrationData('meetup-waitlist-stale-pet-00001', petProfileIds: [$pet->id]),
    );
    $eligible = $service->register(
        $eligibleParticipant,
        $event,
        meetupRegistrationData('meetup-waitlist-next-eligible-001'),
    );
    $manager->forceFill([
        'status' => PetManagerStatus::Revoked,
        'revoked_at' => now(),
    ])->save();

    $service->cancel($occupant, $confirmed);

    expect($stale->refresh()->status)->toBe(ForumEventRegistrationStatus::Expired)
        ->and($stale->active_scope_key)->toBeNull()
        ->and($eligible->refresh()->status)->toBe(ForumEventRegistrationStatus::Confirmed)
        ->and($eligible->waitlist_position)->toBeNull();
});

test('check in revalidates current pet representation authority', function (): void {
    $organizer = User::factory()->create();
    $owner = User::factory()->create();
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create();
    $pet = PetProfile::factory()->for($owner)->create();
    $manager = PetProfileManager::factory()
        ->for($pet, 'profile')
        ->for($participant)
        ->create(['role' => PetManagerRole::Caregiver]);
    $registration = app(ForumEventRegistrationService::class)->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-checkin-current-pet-00001', petProfileIds: [$pet->id]),
    );
    $manager->forceFill([
        'status' => PetManagerStatus::Revoked,
        'revoked_at' => now(),
    ])->save();

    expect(fn () => app(ForumEventRegistrationService::class)->checkIn(
        $organizer,
        $registration,
        'manual',
    ))->toThrow(ValidationException::class)
        ->and($registration->refresh()->status)->toBe(ForumEventRegistrationStatus::Confirmed);
});

test('pet age eligibility uses the selected occurrence date', function (): void {
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->create([
        'starts_at' => now()->addMonth()->startOfDay(),
        'ends_at' => now()->addMonth()->startOfDay()->addHours(2),
        'maximum_animal_age_months' => 13,
    ]);
    $pet = PetProfile::factory()->for($participant)->create([
        'birth_date' => now()->subMonths(11)->startOfDay(),
    ]);
    $laterOccurrence = ForumEventOccurrence::factory()->for($event, 'event')->create([
        'starts_at' => now()->addMonths(4)->startOfDay(),
        'ends_at' => now()->addMonths(4)->startOfDay()->addHours(2),
        'capacity' => $event->capacity,
    ]);
    $data = meetupRegistrationData(
        'meetup-occurrence-age-boundary-0001',
        petProfileIds: [$pet->id],
    );

    expect(fn () => app(ForumEventRegistrationService::class)->register(
        $participant,
        $event,
        new RegisterForForumEventData(
            attendanceFormat: $data->attendanceFormat,
            guestCount: $data->guestCount,
            petProfileId: $data->petProfileId,
            requirementsNote: $data->requirementsNote,
            photoConsent: $data->photoConsent,
            requirementsAccepted: $data->requirementsAccepted,
            idempotencyKey: $data->idempotencyKey,
            petProfileIds: $data->petProfileIds,
            occurrenceId: $laterOccurrence->id,
        ),
    ))->toThrow(ValidationException::class);
});

test('vaccination guidance remains informational and does not manufacture verification evidence', function (): void {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->approvalRequired()->create([
        'vaccination_requirements' => 'Please follow your veterinarian guidance.',
    ]);
    $pet = PetProfile::factory()->for($participant)->create();
    $service = app(ForumEventRegistrationService::class);
    $registration = $service->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-informational-vaccine-0001', petProfileIds: [$pet->id]),
    );
    $service->review($organizer, $registration, true);

    $petRegistration = $registration->registrationPets()->sole();

    expect($petRegistration->eligibility_status)->toBe(ForumEventVerificationStatus::Confirmed)
        ->and($petRegistration->verification_source)->toBe(ForumEventVerificationStatus::ReportedByParticipant);
});

test('pending invitation revocation is organizer scoped and removes private meetup access', function (): void {
    $organizer = User::factory()->create();
    $invitee = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->invitationOnly()->create();
    $invitation = ForumEventInvitation::factory()
        ->for($event, 'event')
        ->for($invitee, 'recipient')
        ->create();

    expect(fn () => app(RevokeForumEventInvitation::class)->handle($outsider, $invitation))
        ->toThrow(AuthorizationException::class)
        ->and(Gate::forUser($invitee)->allows('view', $event))->toBeTrue();

    app(RevokeForumEventInvitation::class)->handle($organizer, $invitation);

    expect($invitation->refresh()->status)->toBe(ForumEventInvitationStatus::Revoked)
        ->and(Gate::forUser($invitee)->allows('view', $event))->toBeFalse();
});

test('public meetup exposes aggregate attendance without private participant or pet identity', function (): void {
    $attendee = User::factory()->create(['name' => 'PRIVATE-ATTENDEE-MARKER']);
    $viewer = User::factory()->create();
    $event = ForumEvent::factory()->create();
    $pet = PetProfile::factory()->for($attendee)->privateProfile()->create([
        'name' => 'PRIVATE-PET-MARKER',
    ]);
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')->for($attendee, 'user')->confirmed()->create([
            'pet_profile_id' => $pet->id,
        ]);
    $registration->pets()->attach($pet->id, [
        'eligibility_status' => 'confirmed',
        'verification_source' => 'reported_by_participant',
    ]);

    $this->actingAs($viewer)
        ->get(route('meetups.show', $event))
        ->assertOk()
        ->assertDontSee('PRIVATE-ATTENDEE-MARKER')
        ->assertDontSee('PRIVATE-PET-MARKER');
});

test('organizer management does not reveal a private pet without independent profile access', function (): void {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create();
    $pet = PetProfile::factory()->for($participant)->privateProfile()->create([
        'name' => 'ORGANIZER-PRIVATE-PET-MARKER',
    ]);
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($participant, 'user')
        ->confirmed()
        ->create(['pet_profile_id' => $pet->id]);
    $registration->pets()->attach($pet->id, [
        'eligibility_status' => ForumEventVerificationStatus::Confirmed->value,
        'verification_source' => ForumEventVerificationStatus::ReportedByParticipant->value,
    ]);

    Livewire::actingAs($organizer)
        ->test(ForumEventWorkspace::class, [
            'eventId' => $event->id,
            'workspaceMode' => 'manage',
        ])
        ->assertDontSee('ORGANIZER-PRIVATE-PET-MARKER')
        ->assertSee(__('forum_events.labels.private_pet'));
});

test('soft deleted participating pet remains a safe historical projection', function (): void {
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->create();
    $pet = PetProfile::factory()->for($participant)->create([
        'name' => 'SOFT-DELETED-PET-HISTORY',
    ]);
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($participant, 'user')
        ->confirmed()
        ->create(['pet_profile_id' => $pet->id]);
    $registration->pets()->attach($pet->id, [
        'eligibility_status' => ForumEventVerificationStatus::Confirmed->value,
        'verification_source' => ForumEventVerificationStatus::ReportedByParticipant->value,
    ]);
    $pet->delete();

    Livewire::actingAs($participant)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertSee('SOFT-DELETED-PET-HISTORY');
});

test('create form clears hidden place and format dependent values', function (): void {
    $organizer = User::factory()->create();

    Livewire::actingAs($organizer)
        ->test(ForumEventDirectory::class, ['createOnly' => true])
        ->set('form.exactLocation', 'Hidden manual address')
        ->set('form.placeId', 123)
        ->assertSet('form.exactLocation', '')
        ->set('form.locationScope', 'Hidden location')
        ->set('form.venueId', 456)
        ->set('form.format', 'online')
        ->assertSet('form.placeId', null)
        ->assertSet('form.venueId', null)
        ->assertSet('form.locationScope', '')
        ->assertSet('form.exactLocation', '')
        ->set('form.onlineUrl', 'https://events.example.test/meetup')
        ->set('form.format', 'physical')
        ->assertSet('form.onlineUrl', '');
});

test('historical participant cannot leave or trigger waitlist promotion after meetup start', function (): void {
    $participant = User::factory()->create();
    $waitlistedUser = User::factory()->create();
    $event = ForumEvent::factory()->create([
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
        'capacity' => 1,
    ]);
    $confirmed = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($participant, 'user')
        ->confirmed()
        ->create();
    $waitlisted = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($waitlistedUser, 'user')
        ->waitlisted(1)
        ->create();

    expect(fn () => app(ForumEventRegistrationService::class)->cancel($participant, $confirmed))
        ->toThrow(AuthorizationException::class)
        ->and($confirmed->refresh()->status)->toBe(ForumEventRegistrationStatus::Confirmed)
        ->and($waitlisted->refresh()->status)->toBe(ForumEventRegistrationStatus::Waitlisted);
});

test('directory projections separate discovery participation and pending invitations with contextual status', function (): void {
    $user = User::factory()->create();
    $organized = ForumEvent::factory()->forOrganizer($user)->create(['title' => 'MY-ORGANIZED-MEETUP']);
    $attending = ForumEvent::factory()->create(['title' => 'MY-ATTENDING-MEETUP']);
    ForumEventRegistration::factory()->for($attending, 'event')->for($user, 'user')->waitlisted()->create();
    $invited = ForumEvent::factory()->invitationOnly()->create(['title' => 'MY-INVITED-MEETUP']);
    ForumEventInvitation::factory()->for($invited, 'event')->for($user, 'recipient')->create();
    $unrelated = ForumEvent::factory()->create(['title' => 'UNRELATED-DISCOVERY-MEETUP']);

    Livewire::actingAs($user)
        ->test(ForumEventDirectory::class)
        ->set('scope', 'my')
        ->assertSee($organized->title)
        ->assertSee($attending->title)
        ->assertSee(ForumEventRegistrationStatus::Waitlisted->label())
        ->assertDontSee($unrelated->title)
        ->set('scope', 'invitations')
        ->assertSee($invited->title)
        ->assertDontSee($attending->title);
});

test('detail and organizer management queries remain bounded as participation grows', function (): void {
    $organizer = User::factory()->create();
    $viewer = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create();
    ForumEventRegistration::factory()->for($event, 'event')->create();

    $renderQueries = function (User $actor, string $mode) use ($event): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($actor)
            ->test(ForumEventWorkspace::class, [
                'eventId' => $event->id,
                'workspaceMode' => $mode,
            ])->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    $singleViewerQueries = $renderQueries($viewer, 'detail');
    $singleManagerQueries = $renderQueries($organizer, 'manage');
    ForumEventRegistration::factory()->count(39)->for($event, 'event')->create();
    $manyViewerQueries = $renderQueries($viewer, 'detail');
    $manyManagerQueries = $renderQueries($organizer, 'manage');

    expect($manyViewerQueries)->toBeLessThanOrEqual($singleViewerQueries + 1)
        ->and($manyManagerQueries)->toBeLessThanOrEqual($singleManagerQueries + 1);
});

test('suspended accounts cannot create join or manage meetups and an inactive organizer closes admission', function (): void {
    $suspended = User::factory()->suspended()->create();
    $active = User::factory()->create();
    $activeEvent = ForumEvent::factory()->create();
    $suspendedOrganizerEvent = ForumEvent::factory()->forOrganizer($suspended)->create();

    expect(Gate::forUser($suspended)->allows('create', ForumEvent::class))->toBeFalse()
        ->and(Gate::forUser($suspended)->allows('register', $activeEvent))->toBeFalse()
        ->and(Gate::forUser($suspended)->allows('update', $suspendedOrganizerEvent))->toBeFalse()
        ->and(Gate::forUser($active)->allows('register', $suspendedOrganizerEvent))->toBeFalse();

    expect(fn () => app(ForumEventRegistrationService::class)->register(
        $active,
        $suspendedOrganizerEvent,
        meetupRegistrationData('suspended-organizer-join-0001'),
    ))->toThrow(AuthorizationException::class);
});

test('cancellation preserves history revokes participation access and sends deduplicated privacy safe notices', function (): void {
    $organizer = User::factory()->create();
    $participants = User::factory()->count(3)->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create([
        'exact_location' => 'CANCELLED-SECRET-LOCATION gate 9931',
    ]);
    $registrations = collect([
        ForumEventRegistration::factory()->confirmed(),
        ForumEventRegistration::factory()->pending(),
        ForumEventRegistration::factory()->waitlisted(),
    ])->map(fn ($factory, int $index) => $factory
        ->for($event, 'event')
        ->for($participants[$index], 'user')
        ->create());

    $action = app(CancelForumEvent::class);
    $action->handle(
        $organizer,
        $event,
        'weather-safety',
        'Severe weather makes this gathering unsafe.',
        'meetup-cancel-notification-0001',
    );
    $action->handle(
        $organizer,
        $event->refresh(),
        'weather-safety',
        'Severe weather makes this gathering unsafe.',
        'meetup-cancel-notification-0001',
    );

    expect($event->refresh()->status)->toBe(ForumEventStatus::Cancelled)
        ->and($registrations->map->refresh()->pluck('status')->unique()->all())
        ->toBe([ForumEventRegistrationStatus::Cancelled])
        ->and($registrations->map->active_scope_key->filter())->toBeEmpty()
        ->and(ForumNotification::query()->where('type', 'event-cancelled')->count())->toBe(3)
        ->and(ForumNotification::query()
            ->where('type', 'event-cancelled')
            ->where('body', 'like', '%CANCELLED-SECRET-LOCATION%')
            ->exists())->toBeFalse()
        ->and(ForumEventParticipationTransition::query()
            ->whereIn('forum_event_registration_id', $registrations->pluck('id'))
            ->where('to_status', ForumEventRegistrationStatus::Cancelled->value)
            ->count())->toBe(3)
        ->and(Gate::forUser($participants[0])->allows('viewAccessDetails', $event))->toBeFalse()
        ->and(Gate::forUser($participants[0])->allows('register', $event))->toBeFalse();
});

test('cancelled private meetup remains safe history for its former participant only', function (): void {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create([
        'visibility' => ForumEventVisibility::Private,
        'title' => 'CANCELLED-PRIVATE-HISTORY-MARKER',
        'exact_location' => 'CANCELLED-PRIVATE-SECRET gate 8819',
    ]);
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($participant, 'user')
        ->confirmed()
        ->create();

    app(CancelForumEvent::class)->handle(
        $organizer,
        $event,
        'weather-safety',
        'Severe weather makes this gathering unsafe.',
        'meetup-private-cancel-history-0001',
    );

    expect(Gate::forUser($participant)->allows('view', $event->refresh()))->toBeTrue()
        ->and(Gate::forUser($participant)->allows('viewAccessDetails', $event))->toBeFalse()
        ->and(ForumEvent::query()->visibleTo($participant)->whereKey($event)->exists())->toBeTrue()
        ->and(ForumEvent::query()->visibleTo($outsider)->whereKey($event)->exists())->toBeFalse();

    $this->actingAs($participant)
        ->get(route('meetups.show', $event))
        ->assertOk()
        ->assertSee('CANCELLED-PRIVATE-HISTORY-MARKER')
        ->assertDontSee('CANCELLED-PRIVATE-SECRET');

    $this->actingAs($participant)
        ->get(route('meetups.index', ['scope' => 'my', 'period' => 'all']))
        ->assertOk()
        ->assertSee('CANCELLED-PRIVATE-HISTORY-MARKER');

    $this->actingAs($outsider)->get(route('meetups.show', $event))->assertForbidden();
});

test('generic status transition cannot bypass the complete cancellation workflow', function (): void {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->create();
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($participant, 'user')
        ->confirmed()
        ->create();

    expect(fn () => app(TransitionForumEventStatus::class)->handle(
        $organizer,
        $event,
        ForumEventStatus::Cancelled,
        'weather-safety',
        'meetup-generic-cancel-guard-0001',
    ))->toThrow(ValidationException::class)
        ->and($event->refresh()->status)->not->toBe(ForumEventStatus::Cancelled)
        ->and($registration->refresh()->status)->toBe(ForumEventRegistrationStatus::Confirmed);
});

test('meetup factory capacity and historical states create coherent records', function (): void {
    $bounded = ForumEvent::factory()->withCapacity(7)->create();
    $past = ForumEvent::factory()->past()->create();

    expect($bounded->capacity)->toBe(7)
        ->and($past->hasEnded())->toBeTrue()
        ->and($past->status)->toBe(ForumEventStatus::Completed);
});

test('rejoining after a terminal cancellation creates a new active generation and preserves history', function (): void {
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->withCapacity(5)->create();
    $service = app(ForumEventRegistrationService::class);
    $first = $service->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-first-generation-0001'),
    );
    $service->cancel($participant, $first);
    $second = $service->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-second-generation-0001'),
    );

    expect($second->id)->not->toBe($first->id)
        ->and($first->refresh()->status)->toBe(ForumEventRegistrationStatus::Cancelled)
        ->and($first->active_scope_key)->toBeNull()
        ->and($second->status)->toBe(ForumEventRegistrationStatus::Confirmed)
        ->and($second->active_scope_key)->not->toBeNull()
        ->and(ForumEventRegistration::query()
            ->where('forum_event_id', $event->id)
            ->where('user_id', $participant->id)
            ->count())->toBe(2)
        ->and($event->registrationFor($participant)?->is($second))->toBeTrue();
});

test('livewire leave after rejoin cancels the latest active registration generation', function (): void {
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->withCapacity(5)->create();
    $service = app(ForumEventRegistrationService::class);
    $first = $service->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-livewire-first-generation-0001'),
    );
    $service->cancel($participant, $first);
    $second = $service->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-livewire-second-generation-001'),
    );

    Livewire::actingAs($participant)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->call('cancelRegistration')
        ->assertHasNoErrors();

    expect($first->refresh()->status)->toBe(ForumEventRegistrationStatus::Cancelled)
        ->and($second->refresh()->status)->toBe(ForumEventRegistrationStatus::Cancelled);
});

test('organizer decline releases the active scope so a participant may request again', function (): void {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->approvalRequired()->create();
    $service = app(ForumEventRegistrationService::class);
    $first = $service->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-declined-generation-000001'),
    );
    $service->review($organizer, $first, false);
    $second = $service->register(
        $participant,
        $event,
        meetupRegistrationData('meetup-after-decline-generation-01'),
    );

    expect($first->refresh()->status)->toBe(ForumEventRegistrationStatus::Declined)
        ->and($first->active_scope_key)->toBeNull()
        ->and($second->status)->toBe(ForumEventRegistrationStatus::Pending)
        ->and($second->id)->not->toBe($first->id);
});

test('confirmed participant reveals a linked place only through a scoped audited grant', function (): void {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $place = Place::factory()->for($organizer, 'owner')->private()->create([
        'exact_address' => 'PLACE-SECRET-ADDRESS gate 4821',
        'private_instructions' => 'PLACE-SECRET-INSTRUCTIONS gate 4821',
    ]);
    $event = ForumEvent::factory()
        ->forOrganizer($organizer)
        ->for($place)
        ->create(['exact_location' => null]);
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($attendee, 'user')
        ->confirmed()
        ->create();
    $component = Livewire::actingAs($attendee)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertDontSee('PLACE-SECRET-ADDRESS')
        ->assertDontSee('PLACE-SECRET-INSTRUCTIONS')
        ->call('revealPlaceExactLocation')
        ->assertSee('PLACE-SECRET-ADDRESS')
        ->assertSee('PLACE-SECRET-INSTRUCTIONS');

    expect(PlaceAccessAudit::query()
        ->where('place_id', $place->id)
        ->where('user_id', $attendee->id)
        ->where('event_id', $event->id)
        ->where('event_type', 'exact-location-viewed')
        ->exists())->toBeTrue();

    app(ForumEventRegistrationService::class)->remove($organizer, $registration);

    $component->call('revealPlaceExactLocation')->assertForbidden();
});

test('organizer can save an incomplete private draft but cannot publish it until required details are complete', function (): void {
    $organizer = User::factory()->create();

    Livewire::actingAs($organizer)
        ->test(ForumEventDirectory::class)
        ->set('form.title', 'Early meetup idea')
        ->set('form.summary', '')
        ->set('form.locationScope', '')
        ->set('form.animalWelfareRules', '')
        ->set('form.emergencyContactPlan', '')
        ->call('saveDraft')
        ->assertHasNoErrors();

    $draft = ForumEvent::query()->where('organizer_user_id', $organizer->id)->sole();

    expect($draft->status)->toBe(ForumEventStatus::Draft)
        ->and($draft->summary)->toBe('')
        ->and($draft->location_scope)->toBeNull()
        ->and(ForumEvent::query()->visibleTo(User::factory()->create())->whereKey($draft)->exists())
        ->toBeFalse();

    expect(fn () => app(PublishForumEvent::class)->handle($organizer, $draft))
        ->toThrow(ValidationException::class);

    app(UpdateForumEvent::class)->handle($organizer, $draft, meetupUpdateData($draft, [
        'summary' => 'A complete, welcoming and safely organized community meetup.',
        'locationScope' => 'Vilnius riverside park area',
        'animalWelfareRules' => 'Keep animals supervised and use a leash where appropriate.',
        'emergencyContactPlan' => 'Contact the organizer and local emergency services if needed.',
        'idempotencyKey' => 'complete-meetup-draft-update-0001',
    ]));
    $published = app(PublishForumEvent::class)->handle($organizer, $draft->refresh());

    expect($published->status)->toBe(ForumEventStatus::Scheduled)
        ->and($published->published_at)->not->toBeNull()
        ->and($published->occurrences()->where('status', ForumEventStatus::Scheduled->value)->exists())
        ->toBeTrue();
});

test('requested and waitlisted users never receive the linked place reveal control', function (
    ForumEventRegistrationStatus $status,
): void {
    $organizer = User::factory()->create();
    $viewer = User::factory()->create();
    $place = Place::factory()->for($organizer, 'owner')->private()->create([
        'exact_address' => 'PLACE-HIDDEN-FROM-NONCONFIRMED gate 8201',
    ]);
    $event = ForumEvent::factory()->forOrganizer($organizer)->for($place)->create();
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($viewer, 'user')
        ->create(['status' => $status]);

    Livewire::actingAs($viewer)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertDontSee('PLACE-HIDDEN-FROM-NONCONFIRMED')
        ->assertDontSee(__('forum_events.actions.reveal_exact_place'));
})->with([
    'requested' => [ForumEventRegistrationStatus::Pending],
    'waitlisted' => [ForumEventRegistrationStatus::Waitlisted],
]);

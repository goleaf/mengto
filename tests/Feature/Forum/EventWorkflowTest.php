<?php

declare(strict_types=1);

use App\Actions\BackfillForumEvents;
use App\Actions\CancelForumEvent;
use App\Actions\CreateForumEvent;
use App\Actions\InviteToForumEvent;
use App\Actions\PublishForumEventUpdate;
use App\Actions\RescheduleForumEvent;
use App\Actions\RespondToForumEventInvitation;
use App\Actions\SendForumEventMessage;
use App\Actions\SubmitForumEventReport;
use App\Actions\SubmitForumEventReview;
use App\Data\CreateForumEventData;
use App\Data\RegisterForForumEventData;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventMessageAudience;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;
use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Enums\ForumEventVisibility;
use App\Livewire\Forum\ForumEventDirectory;
use App\Livewire\Forum\ForumEventWorkspace;
use App\Models\ExpertProfile;
use App\Models\ForumEvent;
use App\Models\ForumEventHistory;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventMessage;
use App\Models\ForumEventParticipationOperation;
use App\Models\ForumNotification;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventReview;
use App\Models\ForumEventUpdate;
use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use App\Models\ForumReport;
use App\Models\Taxon;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Services\ForumEventRegistrationService;
use Carbon\CarbonImmutable;
use Database\Seeders\ForumModerationDefinitionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('event vaccination requirements defer browser synchronization until submit', function () {
    $view = file_get_contents(resource_path('views/livewire/forum/forum-event-directory.blade.php'));

    expect($view)
        ->toBeString()
        ->toContain('wire:model="form.vaccinationRequirements"')
        ->not->toContain('wire:model.live.debounce.400ms="form.vaccinationRequirements"');
});

uses(RefreshDatabase::class);

test('event migration is reversible without touching legacy group activity data', function () {
    $activity = ForumGroupActivity::factory()->create();
    $activityId = $activity->id;
    $migration = require database_path(
        'migrations/2026_07_31_001230_create_forum_event_tables.php',
    );

    $migration->down();

    expect(Schema::hasTable('forum_events'))->toBeFalse()
        ->and(Schema::hasTable('forum_event_registrations'))->toBeFalse()
        ->and(Schema::hasColumn('forum_group_activities', 'forum_event_id'))->toBeFalse()
        ->and(ForumGroupActivity::query()->whereKey($activityId)->exists())->toBeTrue();

    $migration->up();

    expect(Schema::hasTable('forum_events'))->toBeTrue()
        ->and(Schema::hasTable('forum_event_registrations'))->toBeTrue()
        ->and(Schema::hasColumn('forum_group_activities', 'forum_event_id'))->toBeTrue()
        ->and(ForumGroupActivity::query()->whereKey($activityId)->exists())->toBeTrue();
});

function forumEventCreateData(
    string $token = 'event-create-token-0001',
    ForumEventFormat $format = ForumEventFormat::Physical,
    ForumEventVisibility $visibility = ForumEventVisibility::Public,
    ForumEventRegistrationPolicy $registrationPolicy = ForumEventRegistrationPolicy::Open,
    ?int $capacity = 20,
    ?CarbonImmutable $registrationOpensAt = null,
    ?CarbonImmutable $registrationClosesAt = null,
): CreateForumEventData {
    $startsAt = CarbonImmutable::now()->addWeek()->startOfHour();

    return new CreateForumEventData(
        title: 'Accessible neighborhood animal walk',
        summary: 'A calm community gathering with clear welfare and accessibility guidance.',
        type: ForumEventType::Walk,
        visibility: $visibility,
        format: $format,
        startsAt: $startsAt,
        endsAt: $startsAt->addHours(2),
        timezone: 'Europe/Vilnius',
        capacity: $capacity,
        registrationPolicy: $registrationPolicy,
        waitlistEnabled: true,
        locationScope: $format === ForumEventFormat::Online ? null : 'Vilnius',
        exactLocation: $format === ForumEventFormat::Online ? null : 'Private meeting point',
        onlineUrl: $format === ForumEventFormat::Physical
            ? null
            : 'https://events.example.test/session',
        attendanceRequirements: 'Bring individual water and allow animals enough space.',
        vaccinationRequirements: null,
        vaccinationJurisdiction: null,
        minimumAnimalAgeMonths: null,
        maximumAnimalAgeMonths: null,
        accessibilityInformation: 'Step-free route and quiet rest point are available.',
        costMinor: 0,
        currency: 'EUR',
        refundPolicy: null,
        photoConsentMode: ForumEventPhotoConsent::AskFirst,
        animalWelfareRules: 'Handlers must prioritize animal comfort and may leave at any time.',
        emergencyContactPlan: 'Contact the local emergency service and event organizer when needed.',
        groupId: null,
        taxonIds: [],
        locale: 'en',
        idempotencyKey: $token,
        registrationOpensAt: $registrationOpensAt,
        registrationClosesAt: $registrationClosesAt,
    );
}

function forumEventRegistrationData(
    string $token,
    int $guestCount = 0,
    ForumEventFormat $format = ForumEventFormat::Physical,
): RegisterForForumEventData {
    return new RegisterForForumEventData(
        attendanceFormat: $format,
        guestCount: $guestCount,
        petProfileId: null,
        requirementsNote: 'Please keep the meeting point accessible.',
        photoConsent: ForumEventPhotoConsent::Declined,
        requirementsAccepted: true,
        idempotencyKey: $token,
    );
}

test('event creation is authorized validated encrypted audited and idempotent', function () {
    $event = app(CreateForumEvent::class)->handle(
        $this->authenticatedUser,
        forumEventCreateData(),
    );
    $sameEvent = app(CreateForumEvent::class)->handle(
        $this->authenticatedUser,
        forumEventCreateData(),
    );

    expect($sameEvent->is($event))->toBeTrue()
        ->and(ForumEvent::query()->count())->toBe(1)
        ->and($event->organizer_user_id)->toBe($this->authenticatedUser->id)
        ->and($event->status)->toBe(ForumEventStatus::Scheduled)
        ->and(ForumEventHistory::query()
            ->where('forum_event_id', $event->id)
            ->where('event_type', 'created')
            ->count())->toBe(1)
        ->and(DB::table('forum_events')->where('id', $event->id)->value('exact_location'))
        ->not->toBe('Private meeting point')
        ->and($event->toArray())->not->toHaveKeys([
            'creation_idempotency_key',
            'exact_location',
            'online_url',
            'emergency_contact_plan',
        ]);

    $unverified = User::factory()->unverified()->create();

    expect(fn () => app(CreateForumEvent::class)->handle(
        $unverified,
        forumEventCreateData('event-create-token-0002'),
    ))->toThrow(AuthorizationException::class);
});

test('event validation enforces format dates welfare and paid-event safeguards', function () {
    $data = forumEventCreateData(
        token: 'event-create-token-0003',
        format: ForumEventFormat::Online,
    );
    $invalid = new CreateForumEventData(
        title: $data->title,
        summary: $data->summary,
        type: $data->type,
        visibility: $data->visibility,
        format: $data->format,
        startsAt: $data->startsAt,
        endsAt: $data->startsAt->subMinute(),
        timezone: $data->timezone,
        capacity: $data->capacity,
        registrationPolicy: $data->registrationPolicy,
        waitlistEnabled: $data->waitlistEnabled,
        locationScope: null,
        exactLocation: null,
        onlineUrl: null,
        attendanceRequirements: $data->attendanceRequirements,
        vaccinationRequirements: $data->vaccinationRequirements,
        vaccinationJurisdiction: $data->vaccinationJurisdiction,
        minimumAnimalAgeMonths: $data->minimumAnimalAgeMonths,
        maximumAnimalAgeMonths: $data->maximumAnimalAgeMonths,
        accessibilityInformation: $data->accessibilityInformation,
        costMinor: 1000,
        currency: $data->currency,
        refundPolicy: null,
        photoConsentMode: $data->photoConsentMode,
        animalWelfareRules: 'short',
        emergencyContactPlan: $data->emergencyContactPlan,
        groupId: null,
        taxonIds: [],
        locale: $data->locale,
        idempotencyKey: $data->idempotencyKey,
    );

    expect(fn () => app(CreateForumEvent::class)->handle(
        $this->authenticatedUser,
        $invalid,
    ))->toThrow(ValidationException::class);
});

test('event detail presents complete scoped requirements taxonomy club and verified organizer state', function () {
    $organizer = User::factory()->create();
    $group = ForumGroup::factory()
        ->for($organizer, 'owner')
        ->create(['name' => 'Accessible animal club']);
    $taxon = Taxon::factory()->create();
    TaxonVersion::factory()
        ->for($taxon)
        ->create([
            'scientific_name' => 'Canis lupus familiaris',
            'canonical_name' => 'Canis lupus familiaris',
            'normalized_scientific_name' => 'canis lupus familiaris',
            'is_active_version' => true,
        ]);
    ExpertProfile::factory()
        ->for($organizer, 'owner')
        ->create(['owner_key' => $organizer->actor_key]);
    $event = ForumEvent::factory()
        ->for($organizer, 'organizer')
        ->for($group, 'group')
        ->create([
            'organizer_key' => $organizer->actor_key,
            'organizer_name' => $organizer->name,
            'vaccination_requirements' => 'Current vaccination required under local venue rules.',
            'vaccination_jurisdiction' => 'Lithuania',
            'minimum_animal_age_months' => 14,
            'maximum_animal_age_months' => 144,
            'accessibility_information' => 'Step-free entrance and a quiet rest room.',
            'cost_minor' => 2500,
            'currency' => 'EUR',
            'refund_policy' => 'Full refund until forty-eight hours before the event.',
            'photo_consent_mode' => ForumEventPhotoConsent::AskFirst,
            'animal_welfare_rules' => 'Animals may leave immediately when they show discomfort.',
        ]);
    $event->taxa()->attach($taxon->id, ['is_primary' => true]);

    Livewire::actingAs($organizer)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertSee(__('forum_events.detail.verified_organizer'))
        ->assertSee('Accessible animal club')
        ->assertSee('Canis lupus familiaris')
        ->assertSee('Current vaccination required under local venue rules.')
        ->assertSee('Lithuania')
        ->assertSee('Step-free entrance and a quiet rest room.')
        ->assertSee('Full refund until forty-eight hours before the event.')
        ->assertSee(ForumEventPhotoConsent::AskFirst->label())
        ->assertSee('Animals may leave immediately when they show discomfort.')
        ->assertSee('14')
        ->assertSee('144');
});

test('private event grants a pending invite response boundary but withholds participant access', function () {
    $event = ForumEvent::factory()->invitationOnly()->create();
    $invited = User::factory()->create();
    $outsider = User::factory()->create();
    $invitation = ForumEventInvitation::factory()
        ->for($event, 'event')
        ->for($invited, 'recipient')
        ->create();

    expect(Gate::forUser($invited)->allows('view', $event))->toBeTrue()
        ->and(Gate::forUser($invited)->allows('viewAccessDetails', $event))->toBeFalse()
        ->and(Gate::forUser($outsider)->allows('view', $event))->toBeFalse();

    $invitation->forceFill([
        'status' => ForumEventInvitationStatus::Accepted,
        'responded_at' => now(),
    ])->save();

    expect(Gate::forUser($invited)->allows('view', $event))->toBeTrue()
        ->and(ForumEvent::query()->visibleTo($invited)->whereKey($event)->exists())->toBeTrue()
        ->and(ForumEvent::query()->visibleTo($outsider)->whereKey($event)->exists())->toBeFalse();
});

test('event invitations are owner-only replay-safe and grant private access after acceptance', function () {
    $organizer = User::factory()->create();
    $recipient = User::factory()->create();
    $event = ForumEvent::factory()
        ->invitationOnly()
        ->for($organizer, 'organizer')
        ->create([
            'organizer_key' => $organizer->actor_key,
            'organizer_name' => $organizer->name,
        ]);

    $invitation = app(InviteToForumEvent::class)->handle(
        $organizer,
        $event,
        $recipient,
        CarbonImmutable::now()->addWeek(),
        'event-invite-token-0001',
    );
    $sameInvitation = app(InviteToForumEvent::class)->handle(
        $organizer,
        $event,
        $recipient,
        CarbonImmutable::now()->addDays(8),
        'event-invite-token-0002',
    );

    expect($sameInvitation->is($invitation))->toBeTrue()
        ->and(ForumEventInvitation::query()->count())->toBe(1);

    $accepted = app(RespondToForumEventInvitation::class)
        ->handle($recipient, $invitation, true);

    expect($accepted->status)->toBe(ForumEventInvitationStatus::Accepted)
        ->and(Gate::forUser($recipient)->allows('view', $event))->toBeTrue()
        ->and(ForumEventHistory::query()
            ->where('forum_event_id', $event->id)
            ->where('event_type', 'invitation-responded')
            ->count())->toBe(1);

    expect(fn () => app(InviteToForumEvent::class)->handle(
        User::factory()->create(),
        $event,
        User::factory()->create(),
        CarbonImmutable::now()->addWeek(),
        'event-invite-token-0003',
    ))->toThrow(AuthorizationException::class);
});

test('terminal meetup invitations remain historical when a recipient is invited again', function (): void {
    $organizer = User::factory()->create();
    $recipient = User::factory()->create();
    $event = ForumEvent::factory()->invitationOnly()->forOrganizer($organizer)->create();
    $action = app(InviteToForumEvent::class);
    $first = $action->handle(
        $organizer,
        $event,
        $recipient,
        CarbonImmutable::now()->addWeek(),
        'event-invite-generation-0001',
    );

    app(RespondToForumEventInvitation::class)->handle($recipient, $first, false);

    $second = $action->handle(
        $organizer,
        $event,
        $recipient,
        CarbonImmutable::now()->addWeeks(2),
        'event-invite-generation-0002',
    );

    expect($second->id)->not->toBe($first->id)
        ->and($first->refresh()->status)->toBe(ForumEventInvitationStatus::Declined)
        ->and($second->status)->toBe(ForumEventInvitationStatus::Pending)
        ->and(ForumEventInvitation::query()
            ->where('forum_event_id', $event->id)
            ->where('invited_user_id', $recipient->id)
            ->count())->toBe(2);
});

test('an invitation idempotency key cannot be replayed for another recipient', function (): void {
    $organizer = User::factory()->create();
    $firstRecipient = User::factory()->create();
    $secondRecipient = User::factory()->create();
    $event = ForumEvent::factory()->invitationOnly()->forOrganizer($organizer)->create();
    $action = app(InviteToForumEvent::class);
    $key = 'event-invite-idempotency-scope-001';
    $action->handle($organizer, $event, $firstRecipient, CarbonImmutable::now()->addWeek(), $key);

    expect(fn () => $action->handle(
        $organizer,
        $event,
        $secondRecipient,
        CarbonImmutable::now()->addWeek(),
        $key,
    ))->toThrow(ValidationException::class);

    expect(ForumEventInvitation::query()->where('idempotency_key', $key)->count())->toBe(1);
});

test('an expired meetup invitation is persisted as terminal when the recipient responds', function (): void {
    $organizer = User::factory()->create();
    $recipient = User::factory()->create();
    $event = ForumEvent::factory()->invitationOnly()->forOrganizer($organizer)->create();
    $invitation = ForumEventInvitation::factory()
        ->for($event, 'event')
        ->for($organizer, 'inviter')
        ->for($recipient, 'recipient')
        ->create([
            'status' => ForumEventInvitationStatus::Pending,
            'expires_at' => now()->subMinute(),
        ]);

    expect(fn () => app(RespondToForumEventInvitation::class)->handle(
        $recipient,
        $invitation,
        true,
    ))->toThrow(ValidationException::class);

    expect($invitation->refresh()->status)->toBe(ForumEventInvitationStatus::Expired)
        ->and($invitation->active_pair_key)->toBeNull()
        ->and($invitation->responded_at)->not->toBeNull();
});

test('cancelling a meetup revokes pending invitations and they cannot be accepted later', function (): void {
    $organizer = User::factory()->create();
    $recipient = User::factory()->create();
    $event = ForumEvent::factory()->invitationOnly()->forOrganizer($organizer)->create();
    $invitation = app(InviteToForumEvent::class)->handle(
        $organizer,
        $event,
        $recipient,
        CarbonImmutable::now()->addWeek(),
        'event-invite-cancelled-meetup-001',
    );

    app(CancelForumEvent::class)->handle(
        $organizer,
        $event,
        'organizer-cancelled',
        'The meetup cannot proceed safely as scheduled.',
        'event-cancel-with-pending-invite-01',
    );

    expect($invitation->refresh()->status)->toBe(ForumEventInvitationStatus::Revoked)
        ->and($invitation->active_pair_key)->toBeNull();

    $replayed = app(RespondToForumEventInvitation::class)->handle(
        $recipient,
        $invitation,
        true,
    );

    expect($replayed->status)->toBe(ForumEventInvitationStatus::Revoked);
});

test('capacity counts guests and cancelling a registration promotes the waitlist', function () {
    $event = ForumEvent::factory()->create(['capacity' => 2]);
    $first = User::factory()->create(['email_verified_at' => now()]);
    $second = User::factory()->create(['email_verified_at' => now()]);
    $registrations = app(ForumEventRegistrationService::class);

    $confirmed = $registrations->register(
        $first,
        $event,
        forumEventRegistrationData('event-register-token-0001', guestCount: 1),
    );
    $sameRegistration = $registrations->register(
        $first,
        $event,
        forumEventRegistrationData('event-register-token-0002', guestCount: 0),
    );
    $waitlisted = $registrations->register(
        $second,
        $event,
        forumEventRegistrationData('event-register-token-0003'),
    );

    expect($confirmed->status)->toBe(ForumEventRegistrationStatus::Confirmed)
        ->and($sameRegistration->is($confirmed))->toBeTrue()
        ->and($waitlisted->status)->toBe(ForumEventRegistrationStatus::Waitlisted)
        ->and($waitlisted->waitlist_position)->toBe(1)
        ->and($registrations->remainingSeats($event))->toBe(0)
        ->and(DB::table('forum_event_registrations')
            ->where('id', $confirmed->id)
            ->value('requirements_note'))->not->toBe('Please keep the meeting point accessible.');

    $registrations->cancel($first, $confirmed);

    expect($confirmed->refresh()->status)->toBe(ForumEventRegistrationStatus::Cancelled)
        ->and($waitlisted->refresh()->status)->toBe(ForumEventRegistrationStatus::Confirmed)
        ->and($waitlisted->waitlist_position)->toBe(1)
        ->and($waitlisted->confirmed_at)->not->toBeNull()
        ->and($registrations->remainingSeats($event))->toBe(1);
});

test('waitlist promotion selects the first eligible entry that fits the released seats', function (): void {
    $event = ForumEvent::factory()->create(['capacity' => 2]);
    $service = app(ForumEventRegistrationService::class);
    $firstConfirmedUser = User::factory()->create(['email_verified_at' => now()]);
    $secondConfirmedUser = User::factory()->create(['email_verified_at' => now()]);
    $largeParty = User::factory()->create(['email_verified_at' => now()]);
    $singleAttendee = User::factory()->create(['email_verified_at' => now()]);
    $firstConfirmed = $service->register(
        $firstConfirmedUser,
        $event,
        forumEventRegistrationData('event-waitlist-fit-confirmed-0001'),
    );
    $service->register(
        $secondConfirmedUser,
        $event,
        forumEventRegistrationData('event-waitlist-fit-confirmed-0002'),
    );
    $firstWaiting = $service->register(
        $largeParty,
        $event,
        forumEventRegistrationData('event-waitlist-fit-large-party-01', guestCount: 1),
    );
    $secondWaiting = $service->register(
        $singleAttendee,
        $event,
        forumEventRegistrationData('event-waitlist-fit-single-user-01'),
    );

    $service->cancel($firstConfirmedUser, $firstConfirmed);

    expect($firstWaiting->refresh()->status)->toBe(ForumEventRegistrationStatus::Waitlisted)
        ->and($firstWaiting->waitlist_position)->toBe(1)
        ->and($secondWaiting->refresh()->status)->toBe(ForumEventRegistrationStatus::Confirmed)
        ->and($secondWaiting->waitlist_position)->toBe(2);
});

test('waitlist positions are monotonic when an earlier generation leaves the queue', function (): void {
    $event = ForumEvent::factory()->create(['capacity' => 1]);
    $service = app(ForumEventRegistrationService::class);
    $confirmedUser = User::factory()->create(['email_verified_at' => now()]);
    $firstWaitingUser = User::factory()->create(['email_verified_at' => now()]);
    $secondWaitingUser = User::factory()->create(['email_verified_at' => now()]);
    $thirdWaitingUser = User::factory()->create(['email_verified_at' => now()]);
    $service->register(
        $confirmedUser,
        $event,
        forumEventRegistrationData('event-waitlist-sequence-confirmed'),
    );
    $firstWaiting = $service->register(
        $firstWaitingUser,
        $event,
        forumEventRegistrationData('event-waitlist-sequence-first-001'),
    );
    $secondWaiting = $service->register(
        $secondWaitingUser,
        $event,
        forumEventRegistrationData('event-waitlist-sequence-second-01'),
    );

    $service->cancel($secondWaitingUser, $secondWaiting);
    $thirdWaiting = $service->register(
        $thirdWaitingUser,
        $event,
        forumEventRegistrationData('event-waitlist-sequence-third-001'),
    );

    expect($firstWaiting->waitlist_position)->toBe(1)
        ->and($secondWaiting->waitlist_position)->toBe(2)
        ->and($thirdWaiting->waitlist_position)->toBe(3);
});

test('approval registration and check-in remain organizer controlled', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create(['email_verified_at' => now()]);
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()
        ->approvalRequired()
        ->for($organizer, 'organizer')
        ->create([
            'organizer_key' => $organizer->actor_key,
            'organizer_name' => $organizer->name,
        ]);
    $service = app(ForumEventRegistrationService::class);
    $registration = $service->register(
        $attendee,
        $event,
        forumEventRegistrationData('event-register-token-0004'),
    );

    expect($registration->status)->toBe(ForumEventRegistrationStatus::Pending);

    expect(fn () => $service->review($outsider, $registration, true))
        ->toThrow(AuthorizationException::class);

    $service->review($organizer, $registration, true);
    $this->travelTo($event->starts_at->addMinute());
    $service->checkIn($organizer, $registration, 'manual');

    expect($registration->refresh()->status)->toBe(ForumEventRegistrationStatus::CheckedIn)
        ->and($registration->checked_in_at)->not->toBeNull()
        ->and($registration->check_in_method)->toBe('manual')
        ->and(ForumEventHistory::query()
            ->where('forum_event_id', $event->id)
            ->whereIn('event_type', ['registration-reviewed', 'attendee-checked-in'])
            ->count())->toBe(2);
});

test('registration windows are rechecked by the canonical registration service', function () {
    $attendee = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->create([
        'registration_opens_at' => now()->addHour(),
        'registration_closes_at' => now()->addDays(3),
    ]);
    $service = app(ForumEventRegistrationService::class);

    expect(fn () => $service->register(
        $attendee,
        $event,
        forumEventRegistrationData('event-register-window-before-open'),
    ))->toThrow(ValidationException::class);

    $event->forceFill([
        'registration_opens_at' => now()->subDays(2),
        'registration_closes_at' => now()->subMinute(),
    ])->save();

    expect(fn () => $service->register(
        $attendee,
        $event->fresh(),
        forumEventRegistrationData('event-register-window-after-close'),
    ))->toThrow(ValidationException::class);

    $event->forceFill([
        'registration_opens_at' => now()->subHour(),
        'registration_closes_at' => now()->addHour(),
    ])->save();

    expect($service->register(
        $attendee,
        $event->fresh(),
        forumEventRegistrationData('event-register-window-open-now'),
    )->status)->toBe(ForumEventRegistrationStatus::Confirmed);
});

test('completed registration replay survives a later closed admission window', function (): void {
    $attendee = User::factory()->create(['email_verified_at' => now()]);
    $event = ForumEvent::factory()->create([
        'registration_opens_at' => now()->subHour(),
        'registration_closes_at' => now()->addHour(),
    ]);
    $service = app(ForumEventRegistrationService::class);
    $data = forumEventRegistrationData('event-register-completed-replay-01');
    $first = $service->register($attendee, $event, $data);
    $event->forceFill(['registration_closes_at' => now()->subMinute()])->save();

    $replayed = $service->register($attendee, $event->refresh(), $data);

    expect($replayed->id)->toBe($first->id)
        ->and(ForumEventRegistration::query()->where('user_id', $attendee->id)->count())->toBe(1)
        ->and(ForumEventParticipationOperation::query()
            ->where('actor_user_id', $attendee->id)
            ->where('operation_type', 'register')
            ->count())->toBe(1);
});

test('registration idempotency keys are safely scoped to the acting user', function (): void {
    $event = ForumEvent::factory()->create();
    $firstUser = User::factory()->create(['email_verified_at' => now()]);
    $secondUser = User::factory()->create(['email_verified_at' => now()]);
    $service = app(ForumEventRegistrationService::class);
    $sharedKey = 'event-register-shared-client-key-01';

    $first = $service->register(
        $firstUser,
        $event,
        forumEventRegistrationData($sharedKey),
    );
    $second = $service->register(
        $secondUser,
        $event,
        forumEventRegistrationData($sharedKey),
    );

    expect($first->id)->not->toBe($second->id)
        ->and(ForumEventRegistration::query()->where('forum_event_id', $event->id)->count())->toBe(2);
});

test('event creation persists an ordered registration window', function () {
    $opensAt = CarbonImmutable::now()->addDay()->startOfHour();
    $closesAt = CarbonImmutable::now()->addDays(5)->startOfHour();

    $event = app(CreateForumEvent::class)->handle(
        $this->authenticatedUser,
        forumEventCreateData(
            token: 'event-create-registration-window',
            registrationOpensAt: $opensAt,
            registrationClosesAt: $closesAt,
        ),
    );

    expect($event->registration_opens_at?->equalTo($opensAt))->toBeTrue()
        ->and($event->registration_closes_at?->equalTo($closesAt))->toBeTrue();

    expect(fn () => app(CreateForumEvent::class)->handle(
        $this->authenticatedUser,
        forumEventCreateData(
            token: 'event-create-invalid-registration-window',
            registrationOpensAt: $closesAt,
            registrationClosesAt: $opensAt,
        ),
    ))->toThrow(ValidationException::class);
});

test('manual attendance transitions obey the selected occurrence time boundary', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();
    $futureEvent = ForumEvent::factory()->forOrganizer($organizer)->create([
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addHours(2),
    ]);
    $futureRegistration = ForumEventRegistration::factory()
        ->for($futureEvent, 'event')
        ->for($participant, 'user')
        ->confirmed()
        ->create();
    $service = app(ForumEventRegistrationService::class);

    expect(fn () => $service->checkIn($organizer, $futureRegistration, 'manual'))
        ->toThrow(ValidationException::class)
        ->and($futureRegistration->fresh()->status)
        ->toBe(ForumEventRegistrationStatus::Confirmed);

    $endedEvent = ForumEvent::factory()->forOrganizer($organizer)->create([
        'starts_at' => now()->subHours(3),
        'ends_at' => now()->subHour(),
    ]);
    $endedRegistration = ForumEventRegistration::factory()
        ->for($endedEvent, 'event')
        ->for($participant, 'user')
        ->confirmed()
        ->create();

    expect(fn () => $service->checkIn($organizer, $endedRegistration, 'manual'))
        ->toThrow(ValidationException::class);

    $noShow = $service->markNoShow($organizer, $endedRegistration);

    expect($noShow->status)->toBe(ForumEventRegistrationStatus::NoShow)
        ->and($noShow->active_scope_key)->toBeNull();
});

test('access details attendee updates and messages stay inside the authorized audience', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $otherAttendee = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()
        ->online()
        ->for($organizer, 'organizer')
        ->create([
            'organizer_key' => $organizer->actor_key,
            'organizer_name' => $organizer->name,
            'online_url' => 'https://events.example.test/session',
        ]);
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($attendee, 'user')
        ->create();
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($otherAttendee, 'user')
        ->create();
    $update = app(PublishForumEventUpdate::class)->handle(
        $organizer,
        $event,
        ForumEventUpdateType::General,
        ForumEventUpdateAudience::Attendees,
        'Private arrival update',
        'Use the attendee-only video room after checking the event page.',
        'event-update-token-0001',
    );
    $sameUpdate = app(PublishForumEventUpdate::class)->handle(
        $organizer,
        $event,
        ForumEventUpdateType::General,
        ForumEventUpdateAudience::Attendees,
        'Private arrival update',
        'Use the attendee-only video room after checking the event page.',
        'event-update-token-0001',
    );
    $message = app(SendForumEventMessage::class)->handle(
        $attendee,
        $event,
        ForumEventMessageAudience::Organizers,
        'I need the step-free joining instructions.',
        'event-message-token-0001',
    );
    $sameMessage = app(SendForumEventMessage::class)->handle(
        $attendee,
        $event,
        ForumEventMessageAudience::Organizers,
        'I need the step-free joining instructions.',
        'event-message-token-0001',
    );

    expect(Gate::forUser($attendee)->allows('viewAccessDetails', $event))->toBeTrue()
        ->and(Gate::forUser($outsider)->allows('viewAccessDetails', $event))->toBeFalse()
        ->and($sameUpdate->is($update))->toBeTrue()
        ->and($sameMessage->is($message))->toBeTrue()
        ->and($update->audience)->toBe(ForumEventUpdateAudience::Attendees)
        ->and($message->audience)->toBe(ForumEventMessageAudience::Organizers)
        ->and(ForumEventUpdate::query()->where('forum_event_id', $event->id)->count())->toBe(1)
        ->and(ForumNotification::query()
            ->where('type', 'event-organizer-update')
            ->count())->toBe(2)
        ->and(ForumNotification::query()
            ->where('type', 'event-organizer-update')
            ->where('body', 'like', '%attendee-only video room%')
            ->exists())->toBeFalse()
        ->and(ForumEventMessage::query()->where('forum_event_id', $event->id)->count())->toBe(1);

    $this->actingAs($outsider);
    Livewire::test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertDontSee('Private arrival update')
        ->assertDontSee('events.example.test/session');

    $this->actingAs($attendee);
    Livewire::test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertSee('Private arrival update')
        ->assertSee('events.example.test/session')
        ->assertSee('I need the step-free joining instructions.');

    $this->actingAs($otherAttendee);
    Livewire::test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertDontSee('I need the step-free joining instructions.');

    $this->actingAs($organizer);
    Livewire::test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertSee('I need the step-free joining instructions.');
});

test('rescheduling and cancellation preserve an auditable lifecycle', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $event = ForumEvent::factory()
        ->for($organizer, 'organizer')
        ->create([
            'organizer_key' => $organizer->actor_key,
            'organizer_name' => $organizer->name,
        ]);
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($attendee, 'user')
        ->create();
    $newStart = CarbonImmutable::now()->addWeeks(2)->startOfHour();

    $reschedule = app(RescheduleForumEvent::class);
    $reschedule->handle(
        $organizer,
        $event,
        $newStart,
        $newStart->addHours(3),
        'Europe/Vilnius',
        'The venue requested a later date for animal welfare planning.',
        'event-reschedule-token-0001',
    );
    $reschedule->handle(
        $organizer,
        $event,
        $newStart,
        $newStart->addHours(3),
        'Europe/Vilnius',
        'The venue requested a later date for animal welfare planning.',
        'event-reschedule-token-0001',
    );
    expect(fn () => $reschedule->handle(
        $organizer,
        $event,
        $newStart->addDay(),
        $newStart->addDay()->addHours(3),
        'Europe/Vilnius',
        'The venue requested a different date for animal welfare planning.',
        'event-reschedule-token-0001',
    ))->toThrow(ValidationException::class);
    app(CancelForumEvent::class)->handle(
        $organizer,
        $event,
        'venue-unavailable',
        'The accessible venue is no longer available and no safe alternative exists.',
        'event-cancel-token-0001',
    );

    expect($event->refresh()->status)->toBe(ForumEventStatus::Cancelled)
        ->and($event->lock_version)->toBe(2)
        ->and($registration->refresh()->status)->toBe(ForumEventRegistrationStatus::Cancelled)
        ->and(ForumEventUpdate::query()->where('forum_event_id', $event->id)->count())->toBe(2)
        ->and(ForumEventHistory::query()
            ->where('forum_event_id', $event->id)
            ->whereIn('event_type', ['rescheduled', 'cancelled'])
            ->count())->toBe(2)
        ->and(ForumNotification::query()
            ->where('type', 'event-rescheduled')
            ->count())->toBe(1);
});

test('only attendees can submit one idempotent post-event review', function () {
    $attendee = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()->completed()->create();
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($attendee, 'user')
        ->checkedIn()
        ->create();

    $review = app(SubmitForumEventReview::class)->handle(
        $attendee,
        $event,
        5,
        'Accessible and carefully organized',
        'The welfare guidance was followed and the step-free route matched the description.',
        'event-review-token-0001',
    );
    $sameReview = app(SubmitForumEventReview::class)->handle(
        $attendee,
        $event,
        5,
        'Accessible and carefully organized',
        'The welfare guidance was followed and the step-free route matched the description.',
        'event-review-token-0001',
    );

    expect($sameReview->is($review))->toBeTrue()
        ->and(ForumEventReview::query()->count())->toBe(1);

    expect(fn () => app(SubmitForumEventReview::class)->handle(
        $outsider,
        $event,
        5,
        'Not eligible to review',
        'This user did not attend and must not be able to publish a review.',
        'event-review-token-0002',
    ))->toThrow(AuthorizationException::class);
});

test('livewire event mutations reauthorize browser supplied identifiers', function () {
    $organizer = User::factory()->create();
    $attendee = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()
        ->approvalRequired()
        ->for($organizer, 'organizer')
        ->create([
            'organizer_key' => $organizer->actor_key,
            'organizer_name' => $organizer->name,
        ]);
    $registration = ForumEventRegistration::factory()
        ->pending()
        ->for($event, 'event')
        ->for($attendee, 'user')
        ->create();

    Livewire::actingAs($outsider)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->call('reviewRegistration', $registration->id, true)
        ->assertForbidden();

    expect($registration->refresh()->status)->toBe(ForumEventRegistrationStatus::Pending);
});

test('event directory filters are url backed bounded and do not expose private events', function () {
    $public = ForumEvent::factory()->create(['title' => 'Public accessible walk']);
    ForumEvent::factory()->invitationOnly()->create(['title' => 'Private organizer session']);

    Livewire::actingAs($this->authenticatedUser)
        ->withQueryParams(['q' => 'Public accessible'])
        ->test(ForumEventDirectory::class)
        ->assertSet('search', 'Public accessible')
        ->assertSee($public->title)
        ->assertDontSee('Private organizer session');

    $this->get(route('meetups.show', $public))
        ->assertSuccessful()
        ->assertSee($public->title);
});

test('event directory applies canonical species city and availability filters in sql', function () {
    $taxon = Taxon::factory()->create();
    TaxonVersion::factory()->for($taxon)->create([
        'scientific_name' => 'Canis lupus familiaris',
        'canonical_name' => 'Canis lupus familiaris',
        'normalized_scientific_name' => 'canis lupus familiaris',
        'rank' => 'species',
        'is_active_version' => true,
    ]);
    $matching = ForumEvent::factory()->create([
        'title' => 'Vilnius dog meetup filter marker',
        'location_scope' => 'Vilnius city centre',
        'registration_opens_at' => now()->subHour(),
        'registration_closes_at' => now()->addDay(),
    ]);
    $matching->taxa()->attach($taxon->id, ['is_primary' => true]);
    ForumEvent::factory()->create([
        'title' => 'Kaunas unrelated meetup marker',
        'location_scope' => 'Kaunas',
    ]);

    Livewire::actingAs($this->authenticatedUser)
        ->withQueryParams([
            'species' => (string) $taxon->id,
            'city' => 'Vilnius',
            'availability' => 'registration_open',
        ])
        ->test(ForumEventDirectory::class)
        ->assertSet('species', (string) $taxon->id)
        ->assertSet('city', 'Vilnius')
        ->assertSet('availability', 'registration_open')
        ->assertSee($matching->title)
        ->assertDontSee('Kaunas unrelated meetup marker');
});

test('event directory capacity counts every seat consuming status and guest', function () {
    $event = ForumEvent::factory()->create([
        'title' => 'Capacity projection marker',
        'capacity' => 10,
    ]);
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->confirmed()
        ->create(['guest_count' => 2]);
    ForumEventRegistration::factory()
        ->for($event, 'event')
        ->create([
            'status' => ForumEventRegistrationStatus::PartiallyCheckedIn,
            'guest_count' => 1,
        ]);

    Livewire::actingAs($this->authenticatedUser)
        ->test(ForumEventDirectory::class)
        ->assertSee($event->title)
        ->assertSee(__('forum_events.labels.capacity', [
            'confirmed' => 5,
            'capacity' => 10,
        ]));
});

test('event directory query count stays bounded as event volume grows', function () {
    ForumEvent::factory()->create();

    $renderQueryCount = function (): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($this->authenticatedUser)
            ->test(ForumEventDirectory::class)
            ->assertOk();
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $queryCount;
    };
    $singleEventQueries = $renderQueryCount();

    ForumEvent::factory()->count(11)->create();
    $twelveEventQueries = $renderQueryCount();

    expect($singleEventQueries)->toBeLessThanOrEqual(16)
        ->and($twelveEventQueries)->toBeLessThanOrEqual($singleEventQueries + 1);
});

test('legacy event composer redirects to the authoritative livewire flow', function () {
    $event = ForumEvent::factory()->create(['stable_key' => 'legacy-event-route']);

    $this->get(route('compose', ['kind' => 'meetup']))
        ->assertRedirect(route('meetups.create'));
    $this->get(route('compose', [
        'kind' => 'report-event',
        'target' => $event->stable_key,
    ]))->assertRedirect(route('meetups.show', $event));

    $this->post(route('actions.perform'), [
        'action' => 'create-meetup',
        'title' => 'Legacy duplicate event path',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('action');

    expect(ForumEvent::query()->count())->toBe(1);
});

test('event reports reuse unified moderation and preserve reporter privacy', function () {
    $this->seed(ForumModerationDefinitionSeeder::class);
    $event = ForumEvent::factory()->create();
    $reporter = User::factory()->create();

    $report = app(SubmitForumEventReport::class)->handle(
        $reporter,
        $event,
        'spam',
        'The event description contains repeated irrelevant promotion.',
        true,
        false,
    );

    expect($report)
        ->subject_type->toBe(ForumEvent::class)
        ->subject_id->toBe((string) $event->id)
        ->reporter_id->toBe($reporter->id)
        ->status->toBe('received')
        ->and($report->metadata)->toMatchArray(['event_key' => $event->stable_key])
        ->and(ForumReport::query()->whereKey($report)->count())->toBe(1);

    expect(fn () => app(SubmitForumEventReport::class)->handle(
        $reporter,
        $event,
        'spam',
        null,
        false,
        false,
    ))->toThrow(ValidationException::class);
});

test('legacy event and group activity backfill is additive and idempotent', function () {
    $activity = ForumGroupActivity::factory()->create(['forum_event_id' => null]);
    $first = app(BackfillForumEvents::class)->handle();
    $catalogIds = ForumEvent::query()
        ->whereNotNull('legacy_source_key')
        ->orderBy('stable_key')
        ->pluck('id', 'stable_key')
        ->all();
    $activityEventId = $activity->refresh()->forum_event_id;

    $second = app(BackfillForumEvents::class)->handle();

    expect($first->catalogCreated)->toBeGreaterThan(0)
        ->and($first->groupActivitiesCreated)->toBe(1)
        ->and($activityEventId)->not->toBeNull()
        ->and($second->catalogCreated)->toBe(0)
        ->and($second->groupActivitiesCreated)->toBe(0)
        ->and(ForumEvent::query()
            ->whereNotNull('legacy_source_key')
            ->orderBy('stable_key')
            ->pluck('id', 'stable_key')
            ->all())->toBe($catalogIds)
        ->and($activity->refresh()->forum_event_id)->toBe($activityEventId)
        ->and(ForumEvent::query()
            ->where('stable_key', 'group-event-'.$activity->stable_key)
            ->count())->toBe(1);
});

test('every event model factory creates a valid constrained record', function () {
    $event = ForumEvent::factory()->create();
    $user = User::factory()->create();

    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($user, 'user')
        ->create();
    $invitation = ForumEventInvitation::factory()
        ->for($event, 'event')
        ->for($user, 'recipient')
        ->create();
    $update = ForumEventUpdate::factory()->for($event, 'event')->create();
    $message = ForumEventMessage::factory()->for($event, 'event')->create();
    $completed = ForumEvent::factory()->completed()->create();
    $review = ForumEventReview::factory()
        ->for($completed, 'event')
        ->for($user, 'reviewer')
        ->create();
    $history = ForumEventHistory::factory()->for($event, 'event')->create();

    expect($event->exists)->toBeTrue()
        ->and($registration->event->is($event))->toBeTrue()
        ->and($invitation->event->is($event))->toBeTrue()
        ->and($update->event->is($event))->toBeTrue()
        ->and($message->event->is($event))->toBeTrue()
        ->and($review->event->is($completed))->toBeTrue()
        ->and($history->event->is($event))->toBeTrue();
});

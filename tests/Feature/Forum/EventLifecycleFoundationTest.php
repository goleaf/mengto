<?php

declare(strict_types=1);

use App\Actions\BackfillForumEventLifecycle;
use App\Actions\InitializeForumEventLifecycle;
use App\Actions\TransitionForumEventStatus;
use App\Data\RegisterForForumEventData;
use App\Enums\ForumEventAccessibilityStatus;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRecurrenceFrequency;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventTeamMembershipStatus;
use App\Enums\ForumEventTeamRole;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVerificationStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventRegistrationPet;
use App\Models\ForumEventSeries;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumEventVersion;
use App\Models\PetProfile;
use App\Models\User;
use App\Services\ForumEventRegistrationService;
use Database\Seeders\ForumEventDemoSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

test('lifecycle initialization is idempotent and separates owner organizer occurrence and version', function () {
    $organizer = User::factory()->create();
    $owner = User::factory()->create();
    $event = ForumEvent::factory()
        ->forOrganizer($organizer)
        ->create([
            'owner_user_id' => $owner->id,
            'exact_location' => 'Restricted venue entrance',
        ]);

    $first = app(InitializeForumEventLifecycle::class)->handle($event, $organizer);
    $second = app(InitializeForumEventLifecycle::class)->handle($event, $organizer);

    expect($second->occurrence->is($first->occurrence))->toBeTrue()
        ->and($second->version->is($first->version))->toBeTrue()
        ->and($event->occurrences()->count())->toBe(1)
        ->and($event->versions()->count())->toBe(1)
        ->and($event->teamMemberships()->count())->toBe(1)
        ->and($event->teamMemberships()->firstOrFail()->user_id)->toBe($owner->id)
        ->and($event->teamMemberships()->firstOrFail()->role)->toBe(ForumEventTeamRole::Owner)
        ->and($first->occurrence->getRawOriginal('exact_location'))
        ->not->toBe('Restricted venue entrance')
        ->and($first->version->snapshot_checksum)->toHaveLength(64);
});

test('event state transitions are explicit audited and available only to scoped event staff', function () {
    $organizer = User::factory()->create();
    $safetyLead = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($organizer)->withLifecycle()->create();
    ForumEventTeamMembership::factory()->create([
        'forum_event_id' => $event->id,
        'user_id' => $safetyLead->id,
        'invited_by_user_id' => $organizer->id,
        'role' => ForumEventTeamRole::SafetyLead,
        'status' => ForumEventTeamMembershipStatus::Active,
    ]);

    $transition = app(TransitionForumEventStatus::class);
    $suspended = $transition->handle(
        $safetyLead,
        $event,
        ForumEventStatus::SafetySuspended,
        'welfare-review',
        'event-transition-safety-0001',
    );

    expect($suspended->status)->toBe(ForumEventStatus::SafetySuspended)
        ->and($suspended->safety_suspended_at)->not->toBeNull()
        ->and($suspended->occurrences()->firstOrFail()->status)
        ->toBe(ForumEventStatus::SafetySuspended)
        ->and($suspended->history()->where('event_type', 'status-transitioned')->count())
        ->toBe(1);

    $replayed = $transition->handle(
        $safetyLead,
        $suspended,
        ForumEventStatus::SafetySuspended,
        'welfare-review',
        'event-transition-safety-0001',
    );

    expect($replayed->lock_version)->toBe($suspended->lock_version)
        ->and($replayed->history()->where('event_type', 'status-transitioned')->count())
        ->toBe(1);

    expect(fn () => $transition->handle(
        $safetyLead,
        $suspended,
        ForumEventStatus::Live,
        'unauthorized-resume',
        'event-transition-safety-0002',
    ))->toThrow(AuthorizationException::class);

    expect(fn () => $transition->handle(
        $outsider,
        $suspended,
        ForumEventStatus::Live,
        'unauthorized-resume',
        'event-transition-outsider-0001',
    ))->toThrow(AuthorizationException::class);

    expect(fn () => $transition->handle(
        $organizer,
        $suspended,
        ForumEventStatus::Archived,
        'invalid-archive',
        'event-transition-invalid-0001',
    ))->toThrow(ValidationException::class);
});

test('registration snapshots several pets manual eligibility and check out remain separate states', function () {
    $organizer = User::factory()->create();
    $participant = User::factory()->create();
    $pets = PetProfile::factory()->count(2)->for($participant)->create();
    $event = ForumEvent::factory()
        ->forOrganizer($organizer)
        ->withLifecycle()
        ->create([
            'type' => ForumEventType::GroupWalk,
            'pet_participation_mode' => ForumEventPetParticipation::Required,
            'registration_policy' => ForumEventRegistrationPolicy::Approval,
            'vaccination_requirements' => 'Current core vaccination status must be reviewed.',
            'vaccination_jurisdiction' => 'Lithuania',
        ]);
    $data = new RegisterForForumEventData(
        attendanceFormat: ForumEventFormat::Physical,
        guestCount: 0,
        petProfileId: null,
        requirementsNote: 'Please use the quiet entrance.',
        photoConsent: ForumEventPhotoConsent::Declined,
        requirementsAccepted: true,
        idempotencyKey: 'event-registration-multi-pet-0001',
        petProfileIds: $pets->modelKeys(),
        occurrenceId: $event->occurrences()->value('id'),
    );
    $registrations = app(ForumEventRegistrationService::class);

    $registration = $registrations->register($participant, $event, $data);
    $duplicate = $registrations->register($participant, $event, $data);

    expect($duplicate->is($registration))->toBeTrue()
        ->and($registration->status)->toBe(ForumEventRegistrationStatus::Pending)
        ->and($registration->registrationPets()->count())->toBe(2)
        ->and($registration->registrationPets()
            ->where('eligibility_status', ForumEventVerificationStatus::RequiresManualReview->value)
            ->count())->toBe(2)
        ->and($registration->accepted_snapshot['pet_profile_ids'])->toEqualCanonicalizing($pets->modelKeys())
        ->and($registration->accepted_snapshot_checksum)->toHaveLength(64)
        ->and($registration->getRawOriginal('accepted_snapshot'))
        ->not->toContain('Please use the quiet entrance.');

    $approved = $registrations->review($organizer, $registration, true);
    expect($approved->status)->toBe(ForumEventRegistrationStatus::Confirmed)
        ->and($approved->registrationPets()
            ->where('eligibility_status', ForumEventVerificationStatus::Confirmed->value)
            ->count())->toBe(2);

    $checkedIn = $registrations->checkIn($organizer, $approved, 'manual');
    $attended = $registrations->checkOut($organizer, $checkedIn);

    expect($attended->status)->toBe(ForumEventRegistrationStatus::Attended)
        ->and($attended->checked_out_at)->not->toBeNull()
        ->and($attended->registrationPets()->whereNotNull('checked_in_at')->count())->toBe(2)
        ->and($attended->registrationPets()->whereNotNull('checked_out_at')->count())->toBe(2);

    expect(fn () => $registrations->checkIn($organizer, $approved, 'qr'))
        ->toThrow(ValidationException::class);
});

test('unlisted and private events do not leak through the directory or unauthorized detail', function () {
    $viewer = User::factory()->create();
    $organizer = User::factory()->create();
    $unlisted = ForumEvent::factory()
        ->forOrganizer($organizer)
        ->unlisted()
        ->withLifecycle()
        ->create(['title' => 'Unlisted lifecycle review']);
    $private = ForumEvent::factory()
        ->forOrganizer($organizer)
        ->invitationOnly()
        ->withLifecycle()
        ->create([
            'title' => 'Private venue briefing',
            'exact_location' => 'Private foster address',
        ]);

    $this->actingAs($viewer)
        ->get(route('meetups.index'))
        ->assertOk()
        ->assertDontSee($unlisted->title)
        ->assertDontSee($private->title)
        ->assertDontSee('Private foster address');

    $this->actingAs($viewer)
        ->get(route('meetups.show', $unlisted))
        ->assertOk()
        ->assertSee($unlisted->title)
        ->assertDontSee('Private foster address');

    $this->actingAs($viewer)
        ->get(route('meetups.show', $private))
        ->assertForbidden();
});

test('legacy lifecycle backfill is complete and idempotent', function () {
    $participant = User::factory()->create();
    $pet = PetProfile::factory()->for($participant)->create();
    $event = ForumEvent::factory()->create();
    $registration = ForumEventRegistration::factory()
        ->for($event, 'event')
        ->for($participant)
        ->create([
            'pet_profile_id' => $pet->id,
            'forum_event_occurrence_id' => null,
            'forum_event_version_id' => null,
            'accepted_snapshot' => null,
            'accepted_snapshot_checksum' => null,
            'submitted_at' => null,
            'confirmed_at' => null,
            'locale' => null,
            'timezone' => null,
        ]);

    $first = app(BackfillForumEventLifecycle::class)->handle();
    $second = app(BackfillForumEventLifecycle::class)->handle();
    $registration->refresh();

    expect($first->eventsInitialized)->toBe(1)
        ->and($first->registrationsUpdated)->toBe(1)
        ->and($first->petLinksCreated)->toBe(1)
        ->and($registration->forum_event_occurrence_id)->not->toBeNull()
        ->and($registration->forum_event_version_id)->not->toBeNull()
        ->and($registration->accepted_snapshot_checksum)->toHaveLength(64)
        ->and($registration->submitted_at)->not->toBeNull()
        ->and($registration->confirmed_at)->not->toBeNull()
        ->and($registration->locale)->toBe($participant->locale)
        ->and($registration->timezone)->toBe($participant->timezone)
        ->and($registration->registrationPets()->count())->toBe(1)
        ->and($second->eventsInitialized)->toBe(0)
        ->and($second->registrationsUpdated)->toBe(0)
        ->and($second->petLinksCreated)->toBe(0);
});

test('all event registry labels exist in every supported locale and new factories persist', function () {
    $registries = [
        ForumEventType::cases(),
        ForumEventStatus::cases(),
        ForumEventRegistrationStatus::cases(),
        ForumEventPetParticipation::cases(),
        ForumEventAccessibilityStatus::cases(),
        ForumEventVerificationStatus::cases(),
        ForumEventTeamRole::cases(),
        ForumEventTeamMembershipStatus::cases(),
        ForumEventRecurrenceFrequency::cases(),
    ];

    foreach (config('platform.supported_locales', ['en']) as $locale) {
        app()->setLocale($locale);

        foreach ($registries as $cases) {
            foreach ($cases as $case) {
                expect($case->label())->not->toStartWith('forum_events.');
            }
        }
    }

    $series = ForumEventSeries::factory()->create();
    $occurrence = ForumEventOccurrence::factory()->create();
    $version = ForumEventVersion::factory()->create();
    $membership = ForumEventTeamMembership::factory()->create();
    $registrationPet = ForumEventRegistrationPet::factory()->create();

    expect($series->exists)->toBeTrue()
        ->and($occurrence->exists)->toBeTrue()
        ->and($version->exists)->toBeTrue()
        ->and($membership->exists)->toBeTrue()
        ->and($registrationPet->exists)->toBeTrue();
});

test('canonical event demo scenarios are production guarded and idempotent', function () {
    User::factory()->administrator()->create(['actor_key' => 'demo-administrator']);
    User::factory()->lithuanian()->create(['actor_key' => 'demo-lithuanian']);
    User::factory()->unverified()->create(['actor_key' => 'demo-unverified']);
    config(['platform.demo_seed_environments' => ['testing']]);

    $seeder = app(ForumEventDemoSeeder::class);
    $seeder->run();
    $seeder->run();

    expect(ForumEvent::query()->where('stable_key', 'like', 'demo-point13-%')->count())
        ->toBe(16)
        ->and(ForumEvent::query()->select('type')->distinct()->count('type'))->toBe(16)
        ->and(ForumEventSeries::query()->count())->toBe(1)
        ->and(ForumEventOccurrence::query()->count())->toBe(18)
        ->and(ForumEventVersion::query()->count())->toBe(16)
        ->and(ForumEventTeamMembership::query()->count())->toBe(32);

    $walk = ForumEvent::query()
        ->where('stable_key', 'demo-point13-weekly-group-walk')
        ->firstOrFail();
    $occurrences = $walk->occurrences()->orderBy('starts_at')->get();

    expect($walk->ends_at->greaterThan($walk->starts_at))->toBeTrue()
        ->and($walk->starts_at->diffInHours($walk->ends_at))->toBe(3.0)
        ->and($occurrences)->toHaveCount(3)
        ->and($occurrences->pluck('starts_at')->map->toDateString()->unique())->toHaveCount(3)
        ->and($occurrences->every(
            fn (ForumEventOccurrence $occurrence): bool => $occurrence->ends_at->greaterThan($occurrence->starts_at),
        ))->toBeTrue();

    config(['platform.demo_seed_environments' => []]);
    expect(fn () => $seeder->run())->toThrow(LogicException::class);
});

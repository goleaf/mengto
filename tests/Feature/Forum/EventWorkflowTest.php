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

test('private event visibility requires a current accepted invitation', function () {
    $event = ForumEvent::factory()->invitationOnly()->create();
    $invited = User::factory()->create();
    $outsider = User::factory()->create();
    $invitation = ForumEventInvitation::factory()
        ->for($event, 'event')
        ->for($invited, 'recipient')
        ->create();

    expect(Gate::forUser($invited)->allows('view', $event))->toBeFalse()
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
        ->and($waitlisted->waitlist_position)->toBeNull()
        ->and($waitlisted->confirmed_at)->not->toBeNull()
        ->and($registrations->remainingSeats($event))->toBe(1);
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
    $service->checkIn($organizer, $registration, 'manual');

    expect($registration->refresh()->status)->toBe(ForumEventRegistrationStatus::CheckedIn)
        ->and($registration->checked_in_at)->not->toBeNull()
        ->and($registration->check_in_method)->toBe('manual')
        ->and(ForumEventHistory::query()
            ->where('forum_event_id', $event->id)
            ->whereIn('event_type', ['registration-reviewed', 'attendee-checked-in'])
            ->count())->toBe(2);
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

    app(RescheduleForumEvent::class)->handle(
        $organizer,
        $event,
        $newStart,
        $newStart->addHours(3),
        'Europe/Vilnius',
        'The venue requested a later date for animal welfare planning.',
        'event-reschedule-token-0001',
    );
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
            ->count())->toBe(2);
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
        ->assertRedirect(route('meetups.index'));
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

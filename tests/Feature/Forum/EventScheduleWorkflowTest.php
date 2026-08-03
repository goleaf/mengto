<?php

declare(strict_types=1);

use App\Actions\SaveForumEventSession;
use App\Data\SaveForumEventSessionData;
use App\Enums\ForumEventSessionReservationPolicy;
use App\Enums\ForumEventSessionRole;
use App\Enums\ForumEventSessionStatus;
use App\Enums\ForumEventSessionType;
use App\Enums\ForumEventTeamMembershipStatus;
use App\Enums\ForumEventTeamRole;
use App\Livewire\Forum\ForumEventWorkspace;
use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventRoom;
use App\Models\ForumEventSession;
use App\Models\ForumEventSessionStaff;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumEventTrack;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

function forumScheduleData(
    ForumEventOccurrence $occurrence,
    string $token,
    ?ForumEventTrack $track = null,
    ?ForumEventRoom $room = null,
    ?User $staff = null,
    int $offsetMinutes = 0,
    ?string $overrideReason = null,
    ?int $capacity = 20,
): SaveForumEventSessionData {
    return new SaveForumEventSessionData(
        occurrenceId: $occurrence->id,
        trackId: $track?->id,
        roomId: $room?->id,
        title: 'Calm handling workshop',
        summary: 'A structured session with animal rest and accessibility support.',
        type: ForumEventSessionType::Session,
        status: ForumEventSessionStatus::Scheduled,
        startsAt: $occurrence->starts_at->addMinutes($offsetMinutes),
        endsAt: $occurrence->starts_at->addMinutes($offsetMinutes + 45),
        timezone: $occurrence->timezone,
        capacity: $capacity,
        reservationPolicy: ForumEventSessionReservationPolicy::Optional,
        isRequired: false,
        position: 10,
        staff: $staff === null ? [] : [[
            'user_id' => $staff->id,
            'role' => ForumEventSessionRole::Speaker,
            'is_public' => true,
        ]],
        conflictOverrideReason: $overrideReason,
        idempotencyKey: $token,
    );
}

test('event schedule migration is reversible and restores every schedule table', function () {
    $migration = require database_path(
        'migrations/2026_08_03_120100_create_forum_event_schedule_tables.php',
    );

    $migration->down();

    expect(Schema::hasTable('forum_event_tracks'))->toBeFalse()
        ->and(Schema::hasTable('forum_event_rooms'))->toBeFalse()
        ->and(Schema::hasTable('forum_event_sessions'))->toBeFalse()
        ->and(Schema::hasTable('forum_event_session_staff'))->toBeFalse();

    $migration->up();

    expect(Schema::hasTable('forum_event_tracks'))->toBeTrue()
        ->and(Schema::hasTable('forum_event_rooms'))->toBeTrue()
        ->and(Schema::hasTable('forum_event_sessions'))->toBeTrue()
        ->and(Schema::hasTable('forum_event_session_staff'))->toBeTrue();
});

test('schedule action is authorized occurrence scoped encrypted audited and idempotent', function () {
    $owner = User::factory()->create();
    $manager = User::factory()->create();
    $outsider = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($owner)->withLifecycle()->create();
    $occurrence = $event->occurrences()->firstOrFail();
    $track = ForumEventTrack::factory()->for($event, 'event')->create();
    $room = ForumEventRoom::factory()->for($event, 'event')->create([
        'capacity' => 30,
        'exact_directions' => 'Restricted staff entrance',
    ]);
    ForumEventTeamMembership::factory()->create([
        'forum_event_id' => $event->id,
        'user_id' => $manager->id,
        'invited_by_user_id' => $owner->id,
        'role' => ForumEventTeamRole::ScheduleManager,
        'status' => ForumEventTeamMembershipStatus::Active,
    ]);
    $data = forumScheduleData(
        $occurrence,
        'event-session-create-token-0001',
        $track,
        $room,
        $manager,
    );

    $created = app(SaveForumEventSession::class)->handle($manager, $event, $data);
    $replayed = app(SaveForumEventSession::class)->handle($manager, $event, $data);

    expect($replayed->is($created))->toBeTrue()
        ->and(ForumEventSession::query()->count())->toBe(1)
        ->and($created->forum_event_occurrence_id)->toBe($occurrence->id)
        ->and($created->staffAssignments()->count())->toBe(1)
        ->and($event->history()->where('event_type', 'session-created')->count())->toBe(1)
        ->and(DB::table('forum_event_rooms')->where('id', $room->id)->value('exact_directions'))
        ->not->toBe('Restricted staff entrance')
        ->and($room->toArray())->not->toHaveKeys(['exact_directions', 'online_url']);

    expect(fn () => app(SaveForumEventSession::class)->handle(
        $outsider,
        $event,
        forumScheduleData($occurrence, 'event-session-create-token-0002'),
    ))->toThrow(AuthorizationException::class);
});

test('room track and staff conflicts require an audited owner override', function () {
    $owner = User::factory()->create();
    $manager = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($owner)->withLifecycle()->create();
    $occurrence = $event->occurrences()->firstOrFail();
    $track = ForumEventTrack::factory()->for($event, 'event')->create();
    $room = ForumEventRoom::factory()->for($event, 'event')->create(['capacity' => 30]);
    ForumEventTeamMembership::factory()->create([
        'forum_event_id' => $event->id,
        'user_id' => $manager->id,
        'invited_by_user_id' => $owner->id,
        'role' => ForumEventTeamRole::ScheduleManager,
        'status' => ForumEventTeamMembershipStatus::Active,
    ]);
    app(SaveForumEventSession::class)->handle(
        $owner,
        $event,
        forumScheduleData(
            $occurrence,
            'event-session-conflict-token-0001',
            $track,
            $room,
            $owner,
        ),
    );

    expect(fn () => app(SaveForumEventSession::class)->handle(
        $manager,
        $event,
        forumScheduleData(
            $occurrence,
            'event-session-conflict-token-0002',
            $track,
            $room,
            $owner,
        ),
    ))->toThrow(ValidationException::class);

    expect(fn () => app(SaveForumEventSession::class)->handle(
        $manager,
        $event,
        forumScheduleData(
            $occurrence,
            'event-session-conflict-token-0003',
            $track,
            $room,
            $owner,
            overrideReason: 'The parallel setup is intentional and independently supervised.',
        ),
    ))->toThrow(AuthorizationException::class);

    $overridden = app(SaveForumEventSession::class)->handle(
        $owner,
        $event,
        forumScheduleData(
            $occurrence,
            'event-session-conflict-token-0004',
            $track,
            $room,
            $owner,
            overrideReason: 'The parallel setup is intentional and independently supervised.',
        ),
    );

    expect($overridden->conflict_snapshot['conflicts'])->toHaveCount(3)
        ->and($overridden->conflict_override_reason)
        ->toBe('The parallel setup is intentional and independently supervised.')
        ->and(DB::table('forum_event_sessions')->where('id', $overridden->id)->value('conflict_override_reason'))
        ->not->toContain('parallel setup')
        ->and(DB::table('forum_event_sessions')->where('id', $overridden->id)->value('conflict_snapshot'))
        ->not->toContain('room');
});

test('session range timezone capacity and event boundaries fail closed', function () {
    $owner = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($owner)->withLifecycle()->create();
    $occurrence = $event->occurrences()->firstOrFail();
    $room = ForumEventRoom::factory()->for($event, 'event')->create(['capacity' => 10]);
    $invalid = forumScheduleData(
        $occurrence,
        'event-session-validation-token-0001',
        room: $room,
        capacity: 11,
    );

    expect(fn () => app(SaveForumEventSession::class)->handle($owner, $event, $invalid))
        ->toThrow(ValidationException::class);

    $outside = new SaveForumEventSessionData(
        occurrenceId: $occurrence->id,
        trackId: null,
        roomId: null,
        title: 'Outside occurrence',
        summary: null,
        type: ForumEventSessionType::Session,
        status: ForumEventSessionStatus::Scheduled,
        startsAt: $occurrence->starts_at->subMinute(),
        endsAt: $occurrence->starts_at->addMinutes(20),
        timezone: 'UTC',
        capacity: 5,
        reservationPolicy: ForumEventSessionReservationPolicy::Optional,
        isRequired: false,
        position: 0,
        staff: [],
        conflictOverrideReason: null,
        idempotencyKey: 'event-session-validation-token-0002',
    );

    expect(fn () => app(SaveForumEventSession::class)->handle($owner, $event, $outside))
        ->toThrow(ValidationException::class);

    $cancelled = ForumEvent::factory()->forOrganizer($owner)->cancelled()->withLifecycle()->create();
    expect(fn () => app(SaveForumEventSession::class)->handle(
        $owner,
        $cancelled,
        forumScheduleData(
            $cancelled->occurrences()->firstOrFail(),
            'event-session-validation-token-0003',
        ),
    ))->toThrow(ValidationException::class);
});

test('public schedule hides drafts and private staff while scoped managers can edit', function () {
    $owner = User::factory()->create();
    $manager = User::factory()->create();
    $privateSpeaker = User::factory()->create(['name' => 'Private Speaker']);
    $viewer = User::factory()->create();
    $event = ForumEvent::factory()->forOrganizer($owner)->withLifecycle()->create();
    $occurrence = $event->occurrences()->firstOrFail();
    ForumEventTeamMembership::factory()->create([
        'forum_event_id' => $event->id,
        'user_id' => $manager->id,
        'invited_by_user_id' => $owner->id,
        'role' => ForumEventTeamRole::ScheduleManager,
        'status' => ForumEventTeamMembershipStatus::Active,
    ]);
    ForumEventTeamMembership::factory()->create([
        'forum_event_id' => $event->id,
        'user_id' => $privateSpeaker->id,
        'invited_by_user_id' => $owner->id,
        'role' => ForumEventTeamRole::Speaker,
        'status' => ForumEventTeamMembershipStatus::Active,
    ]);
    $published = ForumEventSession::factory()->for($event, 'event')->create([
        'forum_event_occurrence_id' => $occurrence->id,
        'title' => 'Published session',
        'starts_at' => $occurrence->starts_at,
        'ends_at' => $occurrence->starts_at->addMinutes(45),
        'timezone' => $occurrence->timezone,
    ]);
    ForumEventSessionStaff::factory()->private()->create([
        'forum_event_session_id' => $published->id,
        'user_id' => $privateSpeaker->id,
    ]);
    ForumEventSession::factory()->draft()->for($event, 'event')->create([
        'forum_event_occurrence_id' => $occurrence->id,
        'title' => 'Hidden draft session',
        'starts_at' => $occurrence->starts_at->addHour(),
        'ends_at' => $occurrence->starts_at->addMinutes(105),
        'timezone' => $occurrence->timezone,
    ]);

    Livewire::actingAs($viewer)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertSee('Published session')
        ->assertDontSee('Hidden draft session')
        ->assertDontSee('Private Speaker')
        ->assertDontSee(__('forum_events.schedule.manage_heading'));

    $managerWorkspace = Livewire::actingAs($manager)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id])
        ->assertSee('Published session')
        ->assertSee('Hidden draft session')
        ->assertSee('Private Speaker')
        ->assertSee(__('forum_events.schedule.manage_heading'))
        ->call('editSession', $published->id)
        ->assertSet('editingSessionId', $published->id);

    $managerWorkspace
        ->call('resetSessionEditor')
        ->set('sessionForm.title', 'Livewire-created session')
        ->call('saveSession')
        ->assertHasNoErrors()
        ->assertSee(__('forum_events.feedback.session_created'));

    expect($event->sessions()->where('title', 'Livewire-created session')->exists())->toBeTrue();

    expect(Gate::forUser($manager)->allows('manageSchedule', $event))->toBeTrue();
    ForumEventTeamMembership::query()
        ->where('forum_event_id', $event->id)
        ->where('user_id', $manager->id)
        ->update([
            'status' => ForumEventTeamMembershipStatus::Revoked->value,
            'ends_at' => now(),
        ]);
    expect(Gate::forUser($manager)->allows('manageSchedule', $event))->toBeFalse();
});

test('schedule registries are translated and every schedule factory persists', function () {
    $registries = [
        ForumEventSessionType::cases(),
        ForumEventSessionStatus::cases(),
        ForumEventSessionRole::cases(),
        ForumEventSessionReservationPolicy::cases(),
    ];

    foreach (config('platform.supported_locales') as $locale) {
        app()->setLocale($locale);

        foreach ($registries as $registry) {
            foreach ($registry as $entry) {
                expect($entry->label())->not->toContain('forum_events.');
            }
        }
    }

    $event = ForumEvent::factory()->withSchedule()->create();
    $session = $event->sessions()->with(['track', 'room'])->firstOrFail();

    expect($event->tracks()->count())->toBe(1)
        ->and($event->rooms()->count())->toBe(1)
        ->and($session->track)->not->toBeNull()
        ->and($session->room)->not->toBeNull();
});

test('bounded schedule presentation avoids n plus one queries and oversized state', function () {
    $owner = User::factory()->create();
    $speaker = User::factory()->create();
    $eventStartsAt = now()->addDays(3)->startOfHour();
    $event = ForumEvent::factory()->forOrganizer($owner)->withLifecycle()->create([
        'capacity' => 100,
        'starts_at' => $eventStartsAt,
        'ends_at' => $eventStartsAt->copy()->addHours(12),
    ]);
    $occurrence = $event->occurrences()->firstOrFail();
    $track = ForumEventTrack::factory()->for($event, 'event')->create();
    $room = ForumEventRoom::factory()->for($event, 'event')->create(['capacity' => 100]);
    ForumEventTeamMembership::factory()->create([
        'forum_event_id' => $event->id,
        'user_id' => $speaker->id,
        'invited_by_user_id' => $owner->id,
        'role' => ForumEventTeamRole::Speaker,
        'status' => ForumEventTeamMembershipStatus::Active,
    ]);

    foreach (range(0, 29) as $position) {
        $startsAt = $occurrence->starts_at->addMinutes($position * 10);
        $session = ForumEventSession::factory()->for($event, 'event')->create([
            'forum_event_occurrence_id' => $occurrence->id,
            'forum_event_track_id' => $track->id,
            'forum_event_room_id' => $room->id,
            'title' => 'Schedule session '.$position,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes(9),
            'timezone' => $occurrence->timezone,
            'position' => $position,
        ]);
        ForumEventSessionStaff::factory()->create([
            'forum_event_session_id' => $session->id,
            'user_id' => $speaker->id,
        ]);
    }

    $workspace = Livewire::actingAs($owner)
        ->test(ForumEventWorkspace::class, ['eventId' => $event->id]);
    DB::flushQueryLog();
    DB::enableQueryLog();
    $schedule = $workspace->instance()->schedule();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();
    $payloadBytes = strlen((string) json_encode($schedule));

    expect(collect($schedule)->sum(fn (array $day): int => count($day['sessions'])))->toBe(30)
        ->and($queryCount)->toBe(5)
        ->and($payloadBytes)->toBeLessThan(20000);
});

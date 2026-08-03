<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SaveForumEventSessionData;
use App\Enums\ForumEventSessionRole;
use App\Enums\ForumEventSessionStatus;
use App\Enums\ForumEventStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventRoom;
use App\Models\ForumEventSession;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumEventTrack;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class SaveForumEventSession
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        SaveForumEventSessionData $data,
        ?ForumEventSession $session = null,
    ): ForumEventSession {
        $this->gate->forUser($actor)->authorize('manageSchedule', $event);
        $this->validateData($data);

        return DB::transaction(function () use ($actor, $data, $event, $session): ForumEventSession {
            $lockedEvent = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $this->assertEventAcceptsScheduleChanges($lockedEvent);

            if ($session === null) {
                $replayed = ForumEventSession::query()
                    ->where('forum_event_id', $lockedEvent->id)
                    ->where('idempotency_key', $data->idempotencyKey)
                    ->first();

                if ($replayed !== null) {
                    return $replayed;
                }
            } else {
                $session = ForumEventSession::query()->lockForUpdate()->findOrFail($session->id);
                abort_unless($session->forum_event_id === $lockedEvent->id, 404);

                if ($lockedEvent->history()
                    ->where('idempotency_key', 'event:schedule:'.$data->idempotencyKey)
                    ->exists()
                ) {
                    return $session;
                }
            }

            $occurrence = ForumEventOccurrence::query()
                ->lockForUpdate()
                ->where('forum_event_id', $lockedEvent->id)
                ->findOrFail($data->occurrenceId);
            $track = $this->track($lockedEvent, $data->trackId);
            $room = $this->room($lockedEvent, $data->roomId);
            $this->assertRangeAndCapacity($data, $occurrence, $room);
            $this->assertStaffBelongsToEvent($lockedEvent, $data);

            $conflicts = $data->status->blocksResources()
                ? $this->conflicts($lockedEvent, $data, $session)
                : [];
            $overrideReason = $this->authorizeConflictOverride(
                $actor,
                $lockedEvent,
                $data->conflictOverrideReason,
                $conflicts,
            );
            $attributes = [
                'forum_event_occurrence_id' => $occurrence->id,
                'forum_event_track_id' => $track?->id,
                'forum_event_room_id' => $room?->id,
                'updated_by_user_id' => $actor->id,
                'title' => trim($data->title),
                'summary' => $data->summary,
                'type' => $data->type,
                'status' => $data->status,
                'starts_at' => $data->startsAt,
                'ends_at' => $data->endsAt,
                'timezone' => $data->timezone,
                'capacity' => $data->capacity,
                'reservation_policy' => $data->reservationPolicy,
                'is_required' => $data->isRequired,
                'position' => $data->position,
                'conflict_override_reason' => $overrideReason,
                'conflict_snapshot' => $conflicts === [] ? null : ['conflicts' => $conflicts],
            ];

            if ($session === null) {
                $session = ForumEventSession::query()->create([
                    ...$attributes,
                    'forum_event_id' => $lockedEvent->id,
                    'created_by_user_id' => $actor->id,
                    'stable_key' => 'event-session-'.Str::lower((string) Str::ulid()),
                    'idempotency_key' => $data->idempotencyKey,
                ]);
            } else {
                $session->forceFill([
                    ...$attributes,
                    'lock_version' => $session->lock_version + 1,
                ])->save();
            }

            $session->staffAssignments()->delete();
            $session->staffAssignments()->createMany(array_map(
                static fn (array $assignment): array => [
                    'user_id' => $assignment['user_id'],
                    'role' => $assignment['role'],
                    'is_public' => $assignment['is_public'],
                ],
                $data->staff,
            ));
            $lockedEvent->forceFill(['lock_version' => $lockedEvent->lock_version + 1])->save();
            $this->audit->record(
                event: $lockedEvent,
                actor: $actor,
                eventType: $session->wasRecentlyCreated ? 'session-created' : 'session-updated',
                reasonCode: $overrideReason === null ? 'schedule-managed' : 'schedule-conflict-overridden',
                summaryTranslationKey: $session->wasRecentlyCreated
                    ? 'forum_events.history.session_created'
                    : 'forum_events.history.session_updated',
                metadata: [
                    'session_id' => $session->id,
                    'occurrence_id' => $occurrence->id,
                    'track_id' => $track?->id,
                    'room_id' => $room?->id,
                    'starts_at' => $data->startsAt->toAtomString(),
                    'ends_at' => $data->endsAt->toAtomString(),
                    'conflict_count' => count($conflicts),
                ],
                idempotencyKey: 'event:schedule:'.$data->idempotencyKey,
            );

            return $session->load(['track', 'room', 'staffAssignments.user']);
        }, 3);
    }

    private function validateData(SaveForumEventSessionData $data): void
    {
        Validator::make([
            'occurrence_id' => $data->occurrenceId,
            'track_id' => $data->trackId,
            'room_id' => $data->roomId,
            'title' => $data->title,
            'summary' => $data->summary,
            'type' => $data->type->value,
            'status' => $data->status->value,
            'starts_at' => $data->startsAt->toAtomString(),
            'ends_at' => $data->endsAt->toAtomString(),
            'timezone' => $data->timezone,
            'capacity' => $data->capacity,
            'reservation_policy' => $data->reservationPolicy->value,
            'position' => $data->position,
            'staff' => $data->staff,
            'conflict_override_reason' => $data->conflictOverrideReason,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'occurrence_id' => ['required', 'integer'],
            'track_id' => ['nullable', 'integer'],
            'room_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'min:3', 'max:180'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', Rule::enum($data->type::class)],
            'status' => ['required', Rule::enum($data->status::class)],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'timezone' => ['required', 'timezone:all'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'reservation_policy' => ['required', Rule::enum($data->reservationPolicy::class)],
            'position' => ['required', 'integer', 'min:0', 'max:65535'],
            'staff' => ['array', 'max:20'],
            'staff.*.user_id' => ['required', 'integer', 'distinct'],
            'staff.*.role' => ['required', Rule::enum(ForumEventSessionRole::class)],
            'staff.*.is_public' => ['required', 'boolean'],
            'conflict_override_reason' => ['nullable', 'string', 'min:20', 'max:2000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
    }

    private function assertEventAcceptsScheduleChanges(ForumEvent $event): void
    {
        if (in_array($event->status, [
            ForumEventStatus::Cancelled,
            ForumEventStatus::Completed,
            ForumEventStatus::Archived,
            ForumEventStatus::RetentionDeletionPending,
        ], true)) {
            throw ValidationException::withMessages([
                'sessionForm' => __('forum_events.validation.session_event_status'),
            ]);
        }
    }

    private function track(ForumEvent $event, ?int $trackId): ?ForumEventTrack
    {
        return $trackId === null
            ? null
            : ForumEventTrack::query()->where('forum_event_id', $event->id)->findOrFail($trackId);
    }

    private function room(ForumEvent $event, ?int $roomId): ?ForumEventRoom
    {
        return $roomId === null
            ? null
            : ForumEventRoom::query()->where('forum_event_id', $event->id)->findOrFail($roomId);
    }

    private function assertRangeAndCapacity(
        SaveForumEventSessionData $data,
        ForumEventOccurrence $occurrence,
        ?ForumEventRoom $room,
    ): void {
        $messages = [];

        if ($data->startsAt->lt($occurrence->starts_at)
            || $data->endsAt->gt($occurrence->ends_at)
        ) {
            $messages['sessionForm.startsAt'] = __('forum_events.validation.session_occurrence_range');
        }

        if ($data->timezone !== $occurrence->timezone) {
            $messages['sessionForm.timezone'] = __('forum_events.validation.session_timezone');
        }

        if ($data->capacity !== null && $occurrence->capacity !== null
            && $data->capacity > $occurrence->capacity
        ) {
            $messages['sessionForm.capacity'] = __('forum_events.validation.session_occurrence_capacity');
        }

        if ($data->capacity !== null && $room?->capacity !== null
            && $data->capacity > $room->capacity
        ) {
            $messages['sessionForm.capacity'] = __('forum_events.validation.session_room_capacity');
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    private function assertStaffBelongsToEvent(
        ForumEvent $event,
        SaveForumEventSessionData $data,
    ): void {
        $userIds = collect($data->staff)->pluck('user_id')->unique()->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $authorizedIds = ForumEventTeamMembership::query()
            ->active()
            ->where('forum_event_id', $event->id)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->push($event->owner_user_id)
            ->push($event->organizer_user_id)
            ->filter()
            ->unique();

        if ($userIds->diff($authorizedIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'sessionForm.staffUserId' => __('forum_events.validation.session_staff_scope'),
            ]);
        }
    }

    /**
     * @return list<array{resource: string, session_id: int, title: string}>
     */
    private function conflicts(
        ForumEvent $event,
        SaveForumEventSessionData $data,
        ?ForumEventSession $session,
    ): array {
        $conflicts = [];
        $base = ForumEventSession::query()
            ->select(['id', 'title', 'forum_event_room_id', 'forum_event_track_id'])
            ->where('forum_event_id', $event->id)
            ->where('forum_event_occurrence_id', $data->occurrenceId)
            ->where('status', '!=', ForumEventSessionStatus::Cancelled->value)
            ->where('starts_at', '<', $data->endsAt)
            ->where('ends_at', '>', $data->startsAt)
            ->when($session !== null, fn (Builder $query): Builder => $query->whereKeyNot($session->id));

        if ($data->roomId !== null) {
            foreach ((clone $base)->where('forum_event_room_id', $data->roomId)->lockForUpdate()->get() as $overlap) {
                $conflicts[] = ['resource' => 'room', 'session_id' => $overlap->id, 'title' => $overlap->title];
            }
        }

        if ($data->trackId !== null) {
            foreach ((clone $base)->where('forum_event_track_id', $data->trackId)->lockForUpdate()->get() as $overlap) {
                $conflicts[] = ['resource' => 'track', 'session_id' => $overlap->id, 'title' => $overlap->title];
            }
        }

        $staffIds = collect($data->staff)->pluck('user_id')->unique()->values();

        if ($staffIds->isNotEmpty()) {
            foreach ((clone $base)
                ->whereHas('staffAssignments', fn (Builder $staff): Builder => $staff->whereIn('user_id', $staffIds))
                ->lockForUpdate()
                ->get() as $overlap
            ) {
                $conflicts[] = ['resource' => 'staff', 'session_id' => $overlap->id, 'title' => $overlap->title];
            }
        }

        return collect($conflicts)->unique(
            static fn (array $conflict): string => $conflict['resource'].'-'.$conflict['session_id'],
        )->values()->all();
    }

    /**
     * @param  list<array{resource: string, session_id: int, title: string}>  $conflicts
     */
    private function authorizeConflictOverride(
        User $actor,
        ForumEvent $event,
        ?string $reason,
        array $conflicts,
    ): ?string {
        if ($conflicts === []) {
            return null;
        }

        if ($reason === null || mb_strlen(trim($reason)) < 20) {
            throw ValidationException::withMessages([
                'sessionForm.conflictOverrideReason' => __('forum_events.validation.session_conflict'),
            ]);
        }

        $this->gate->forUser($actor)->authorize('overrideScheduleConflict', $event);

        return trim($reason);
    }
}

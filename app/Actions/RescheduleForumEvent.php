<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventStatus;
use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Models\ForumEvent;
use App\Models\ForumEventUpdate;
use App\Models\User;
use App\Services\ForumEventAudit;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class RescheduleForumEvent
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        string $timezone,
        string $explanation,
        string $idempotencyKey,
    ): ForumEvent {
        $this->gate->forUser($actor)->authorize('update', $event);
        Validator::make([
            'starts_at' => $startsAt->toAtomString(),
            'ends_at' => $endsAt->toAtomString(),
            'timezone' => $timezone,
            'explanation' => $explanation,
            'idempotency_key' => $idempotencyKey,
        ], [
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'timezone' => ['required', 'timezone:all'],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $endsAt,
            $event,
            $explanation,
            $idempotencyKey,
            $startsAt,
            $timezone,
        ): ForumEvent {
            $locked = ForumEvent::query()
                ->lockForUpdate()
                ->findOrFail($event->id);

            if ($locked->status !== ForumEventStatus::Scheduled) {
                throw ValidationException::withMessages([
                    'rescheduleForm' => __('forum_events.validation.reschedule_status'),
                ]);
            }

            $oldStartsAt = $locked->starts_at->toAtomString();
            $oldEndsAt = $locked->ends_at->toAtomString();
            $locked->forceFill([
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'timezone' => $timezone,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            ForumEventUpdate::query()->createOrFirst(
                ['idempotency_key' => 'event-reschedule-update:'.$idempotencyKey],
                [
                    'forum_event_id' => $locked->id,
                    'author_user_id' => $actor->id,
                    'stable_key' => 'event-update-'.Str::lower((string) Str::ulid()),
                    'type' => ForumEventUpdateType::Rescheduled,
                    'audience' => ForumEventUpdateAudience::Public,
                    'title' => __('forum_events.updates.rescheduled_title'),
                    'body' => trim($explanation),
                    'published_at' => now(),
                ],
            );
            $this->audit->record(
                event: $locked,
                actor: $actor,
                eventType: 'rescheduled',
                reasonCode: 'organizer-rescheduled',
                summaryTranslationKey: 'forum_events.history.rescheduled',
                metadata: [
                    'old_starts_at' => $oldStartsAt,
                    'old_ends_at' => $oldEndsAt,
                    'new_starts_at' => $startsAt->toAtomString(),
                    'new_ends_at' => $endsAt->toAtomString(),
                    'explanation' => trim($explanation),
                ],
                idempotencyKey: 'event:reschedule:'.$idempotencyKey,
            );

            return $locked;
        }, 3);
    }
}

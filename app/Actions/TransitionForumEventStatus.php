<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventHistory;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class TransitionForumEventStatus
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        ForumEventStatus $next,
        string $reasonCode,
        string $idempotencyKey,
    ): ForumEvent {
        $this->gate->forUser($actor)->authorize('transition', [$event, $next]);
        Validator::make([
            'status' => $next->value,
            'reason_code' => $reasonCode,
            'idempotency_key' => $idempotencyKey,
        ], [
            'status' => ['required', Rule::enum(ForumEventStatus::class)],
            'reason_code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $event,
            $idempotencyKey,
            $next,
            $reasonCode,
        ): ForumEvent {
            $locked = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $this->gate->forUser($actor)->authorize('transition', [$locked, $next]);
            $from = $locked->status;
            $auditKey = 'event:transition:'.$idempotencyKey;
            $existing = ForumEventHistory::query()
                ->where('idempotency_key', $auditKey)
                ->first();

            if ($existing !== null) {
                if ($existing->forum_event_id !== $locked->id
                    || $existing->actor_user_id !== $actor->id
                    || $existing->to_status !== $next->value
                    || $existing->reason_code !== $reasonCode
                ) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('forum_events.validation.idempotency_conflict'),
                    ]);
                }

                return $locked;
            }

            if (! $from->canTransitionTo($next)) {
                throw ValidationException::withMessages([
                    'status' => __('forum_events.validation.invalid_transition', [
                        'from' => $from->label(),
                        'to' => $next->label(),
                    ]),
                ]);
            }

            $locked->forceFill([
                'status' => $next,
                'published_at' => $next === ForumEventStatus::Published
                    ? ($locked->published_at ?? now())
                    : $locked->published_at,
                'safety_suspended_at' => $next === ForumEventStatus::SafetySuspended
                    ? now()
                    : null,
                'archived_at' => $next === ForumEventStatus::Archived
                    ? now()
                    : $locked->archived_at,
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $locked->occurrences()
                ->where('status', $from->value)
                ->update(['status' => $next->value, 'updated_at' => now()]);
            $this->audit->record(
                event: $locked,
                actor: $actor,
                eventType: 'status-transitioned',
                reasonCode: $reasonCode,
                summaryTranslationKey: 'forum_events.history.status_transitioned',
                fromStatus: $from->value,
                toStatus: $next->value,
                idempotencyKey: $auditKey,
            );

            return $locked;
        }, 3);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventUpdate;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class CancelForumEvent
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumEvent $event,
        string $reasonCode,
        string $explanation,
        string $idempotencyKey,
    ): ForumEvent {
        $this->gate->forUser($actor)->authorize('update', $event);
        Validator::make([
            'reason_code' => $reasonCode,
            'explanation' => $explanation,
            'idempotency_key' => $idempotencyKey,
        ], [
            'reason_code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $event,
            $explanation,
            $idempotencyKey,
            $reasonCode,
        ): ForumEvent {
            $locked = ForumEvent::query()
                ->lockForUpdate()
                ->findOrFail($event->id);
            $this->gate->forUser($actor)->authorize('update', $locked);

            if ($locked->status === ForumEventStatus::Cancelled) {
                return $locked;
            }

            if ($locked->status !== ForumEventStatus::Scheduled) {
                throw ValidationException::withMessages([
                    'cancellationForm' => __('forum_events.validation.cancellation_status'),
                ]);
            }

            $from = $locked->status;
            $locked->forceFill([
                'status' => ForumEventStatus::Cancelled,
                'cancelled_by_user_id' => $actor->id,
                'cancelled_at' => now(),
                'cancellation_reason_code' => $reasonCode,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            ForumEventRegistration::query()
                ->where('forum_event_id', $locked->id)
                ->whereIn('status', [
                    ForumEventRegistrationStatus::Pending->value,
                    ForumEventRegistrationStatus::Confirmed->value,
                    ForumEventRegistrationStatus::Waitlisted->value,
                ])
                ->update([
                    'status' => ForumEventRegistrationStatus::Cancelled->value,
                    'waitlist_position' => null,
                    'cancelled_at' => now(),
                    'cancellation_reason_code' => 'event-cancelled',
                    'updated_at' => now(),
                ]);

            ForumEventUpdate::query()->createOrFirst(
                ['idempotency_key' => 'event-cancellation-update:'.$idempotencyKey],
                [
                    'forum_event_id' => $locked->id,
                    'author_user_id' => $actor->id,
                    'stable_key' => 'event-update-'.Str::lower((string) Str::ulid()),
                    'type' => ForumEventUpdateType::Cancelled,
                    'audience' => ForumEventUpdateAudience::Public,
                    'title' => __('forum_events.updates.cancelled_title'),
                    'body' => trim($explanation),
                    'published_at' => now(),
                ],
            );
            $this->audit->record(
                event: $locked,
                actor: $actor,
                eventType: 'cancelled',
                reasonCode: $reasonCode,
                summaryTranslationKey: 'forum_events.history.cancelled',
                fromStatus: $from->value,
                toStatus: ForumEventStatus::Cancelled->value,
                metadata: ['explanation' => trim($explanation)],
                idempotencyKey: 'event:cancel:'.$idempotencyKey,
            );

            return $locked;
        }, 3);
    }
}

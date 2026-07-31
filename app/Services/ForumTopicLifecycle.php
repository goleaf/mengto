<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicStatus;
use App\Models\ForumTopic;
use App\Models\ForumTopicLifecycleEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ForumTopicLifecycle
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function transition(
        ForumTopic $topic,
        ForumTopicStatus $target,
        ?User $actor,
        string $reasonCode,
        ?int $expectedLockVersion = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
        ForumTopicLifecycleEventType $eventType = ForumTopicLifecycleEventType::StateChanged,
        bool $administrativeOverride = false,
    ): ForumTopic {
        return DB::transaction(function () use (
            $topic,
            $target,
            $actor,
            $reasonCode,
            $expectedLockVersion,
            $metadata,
            $idempotencyKey,
            $eventType,
            $administrativeOverride,
        ): ForumTopic {
            if ($idempotencyKey !== null) {
                $existing = ForumTopicLifecycleEvent::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->first();

                if ($existing instanceof ForumTopicLifecycleEvent) {
                    return ForumTopic::query()->findOrFail($existing->forum_topic_id);
                }
            }

            $locked = ForumTopic::query()
                ->lockForUpdate()
                ->findOrFail($topic->id);
            $from = $locked->status->canonical();
            $to = $target->canonical();

            if (
                $expectedLockVersion !== null
                && $locked->lock_version !== $expectedLockVersion
            ) {
                throw ValidationException::withMessages([
                    'lock_version' => __('forum_topic_lifecycle.validation.stale_state'),
                ]);
            }

            if ($from === $to) {
                throw ValidationException::withMessages([
                    'status' => __('forum_topic_lifecycle.validation.same_state'),
                ]);
            }

            if (
                $locked->hasActiveLegalHold()
                && in_array($to, $this->holdProtectedStates(), true)
            ) {
                throw ValidationException::withMessages([
                    'status' => __('forum_topic_lifecycle.validation.legal_hold'),
                ]);
            }

            if (
                ! $administrativeOverride
                && ! $this->canTransition($from, $to)
            ) {
                throw ValidationException::withMessages([
                    'status' => __('forum_topic_lifecycle.validation.transition', [
                        'from' => $from->label(),
                        'to' => $to->label(),
                    ]),
                ]);
            }

            $now = now();
            $nextVersion = $locked->lock_version + 1;
            $attributes = [
                'status' => $to,
                'state_entered_at' => $now,
                'lock_version' => $nextVersion,
                'last_activity_at' => $now,
                'is_locked' => in_array($to, [
                    ForumTopicStatus::Locked,
                    ForumTopicStatus::Archived,
                    ForumTopicStatus::Merged,
                    ForumTopicStatus::Redirected,
                    ForumTopicStatus::Removed,
                ], true),
            ];

            if ($to->isPubliclyVisible() && $locked->published_at === null) {
                $attributes['published_at'] = $now;
            }

            if ($to === ForumTopicStatus::Archived) {
                $attributes['archived_at'] = $now;
            }

            if ($to === ForumTopicStatus::Removed) {
                $attributes['removed_at'] = $now;
            }

            if ($to === ForumTopicStatus::Outdated) {
                $attributes['outdated_at'] = $now;
            }

            if ($to === ForumTopicStatus::Locked) {
                $attributes['locked_at'] = $now;
            }

            if ($to->redirectsToAnotherTopic()) {
                $attributes['redirected_at'] = $now;
            }

            if ($to === ForumTopicStatus::Restored) {
                $attributes = [
                    ...$attributes,
                    'archived_at' => null,
                    'removed_at' => null,
                    'locked_at' => null,
                    'redirected_at' => null,
                    'redirect_path' => null,
                    'merged_into_topic_id' => null,
                    'restored_at' => $now,
                    'is_locked' => false,
                ];
            }

            if ($from === ForumTopicStatus::Outdated && $to !== ForumTopicStatus::Outdated) {
                $attributes['outdated_at'] = null;
            }

            if ($from === ForumTopicStatus::Locked && $to !== ForumTopicStatus::Locked) {
                $attributes['locked_at'] = null;
            }

            $locked->forceFill($attributes)->save();
            $this->record(
                topic: $locked,
                type: $eventType,
                actor: $actor,
                reasonCode: $reasonCode,
                fromStatus: $from,
                toStatus: $to,
                metadata: $metadata,
                idempotencyKey: $idempotencyKey,
            );

            return $locked->refresh();
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ForumTopic $topic,
        ForumTopicLifecycleEventType $type,
        ?User $actor,
        string $reasonCode,
        ?ForumTopicStatus $fromStatus = null,
        ?ForumTopicStatus $toStatus = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): ForumTopicLifecycleEvent {
        if ($idempotencyKey !== null) {
            $existing = ForumTopicLifecycleEvent::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing instanceof ForumTopicLifecycleEvent) {
                return $existing;
            }
        }

        return ForumTopicLifecycleEvent::query()->create([
            'forum_topic_id' => $topic->id,
            'actor_user_id' => $actor?->id,
            'event_type' => $type,
            'from_status' => $fromStatus?->value,
            'to_status' => $toStatus?->value,
            'reason_code' => $reasonCode,
            'reason_translation_key' => "forum_topic_lifecycle.reasons.{$reasonCode}",
            'lock_version' => $topic->lock_version,
            'idempotency_key' => $idempotencyKey,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    public function canTransition(
        ForumTopicStatus $from,
        ForumTopicStatus $to,
    ): bool {
        $from = $from->canonical();
        $to = $to->canonical();

        return in_array($to, match ($from) {
            ForumTopicStatus::Draft => [
                ForumTopicStatus::Published,
                ForumTopicStatus::PendingModeration,
                ForumTopicStatus::Removed,
            ],
            ForumTopicStatus::PendingModeration => [
                ForumTopicStatus::NeedsClarification,
                ForumTopicStatus::Published,
                ForumTopicStatus::Open,
                ForumTopicStatus::Removed,
            ],
            ForumTopicStatus::NeedsClarification => [
                ForumTopicStatus::Draft,
                ForumTopicStatus::PendingModeration,
                ForumTopicStatus::Published,
                ForumTopicStatus::Open,
                ForumTopicStatus::Removed,
            ],
            ForumTopicStatus::Published,
            ForumTopicStatus::Open,
            ForumTopicStatus::Answered,
            ForumTopicStatus::PartiallySolved,
            ForumTopicStatus::Solved,
            ForumTopicStatus::Disputed,
            ForumTopicStatus::Outdated,
            ForumTopicStatus::Restored => [
                ForumTopicStatus::Open,
                ForumTopicStatus::Answered,
                ForumTopicStatus::PartiallySolved,
                ForumTopicStatus::Solved,
                ForumTopicStatus::Disputed,
                ForumTopicStatus::Outdated,
                ForumTopicStatus::Locked,
                ForumTopicStatus::Archived,
                ForumTopicStatus::Merged,
                ForumTopicStatus::Redirected,
                ForumTopicStatus::Removed,
            ],
            ForumTopicStatus::Locked => [
                ForumTopicStatus::Open,
                ForumTopicStatus::Answered,
                ForumTopicStatus::Solved,
                ForumTopicStatus::Archived,
                ForumTopicStatus::Removed,
                ForumTopicStatus::Restored,
            ],
            ForumTopicStatus::Archived,
            ForumTopicStatus::Merged,
            ForumTopicStatus::Redirected,
            ForumTopicStatus::Removed => [
                ForumTopicStatus::Restored,
            ],
            default => [],
        }, true);
    }

    /** @return array<int, ForumTopicStatus> */
    private function holdProtectedStates(): array
    {
        return [
            ForumTopicStatus::Archived,
            ForumTopicStatus::Merged,
            ForumTopicStatus::Redirected,
            ForumTopicStatus::Removed,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicLifecycleEventType;
use App\Models\ForumTopic;
use App\Models\ForumTopicLegalHold;
use App\Models\User;
use App\Services\ForumTopicLifecycle;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class SetForumTopicLegalHold
{
    public function __construct(
        private Gate $gate,
        private ForumTopicLifecycle $lifecycle,
    ) {}

    public function apply(
        User $actor,
        ForumTopic $topic,
        string $reasonCode,
        string $privateReason,
        ?string $reviewAt = null,
    ): ForumTopicLegalHold {
        $this->gate->forUser($actor)->authorize('manageLegalHold', $topic);
        $validated = Validator::make([
            'reason_code' => trim($reasonCode),
            'private_reason' => trim($privateReason),
            'review_at' => $reviewAt,
        ], [
            'reason_code' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'private_reason' => ['required', 'string', 'min:20', 'max:5000'],
            'review_at' => ['nullable', 'date', 'after:now'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $topic,
            $validated,
        ): ForumTopicLegalHold {
            $locked = ForumTopic::query()->lockForUpdate()->findOrFail($topic->id);
            $active = ForumTopicLegalHold::query()
                ->where('forum_topic_id', $locked->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if ($active instanceof ForumTopicLegalHold) {
                return $active;
            }

            $hold = ForumTopicLegalHold::query()->create([
                'forum_topic_id' => $locked->id,
                'applied_by_user_id' => $actor->id,
                'reason_code' => $validated['reason_code'],
                'private_reason' => $validated['private_reason'],
                'starts_at' => now(),
                'review_at' => $validated['review_at'],
                'active_key' => "forum-topic:{$locked->id}",
                'metadata' => [],
            ]);
            $locked->forceFill([
                'legal_hold_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $this->lifecycle->record(
                topic: $locked,
                type: ForumTopicLifecycleEventType::LegalHoldApplied,
                actor: $actor,
                reasonCode: 'legal-hold-applied',
                metadata: [
                    'legal_hold_id' => $hold->id,
                    'reason_code' => $validated['reason_code'],
                ],
                idempotencyKey: "topic-legal-hold-applied:{$hold->id}",
            );

            return $hold;
        }, 3);
    }

    public function release(
        User $actor,
        ForumTopic $topic,
        string $releaseReason,
    ): ForumTopicLegalHold {
        $this->gate->forUser($actor)->authorize('manageLegalHold', $topic);
        $releaseReason = trim($releaseReason);
        Validator::make(['release_reason' => $releaseReason], [
            'release_reason' => ['required', 'string', 'min:20', 'max:5000'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $topic,
            $releaseReason,
        ): ForumTopicLegalHold {
            $locked = ForumTopic::query()->lockForUpdate()->findOrFail($topic->id);
            $hold = ForumTopicLegalHold::query()
                ->where('forum_topic_id', $locked->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if (! $hold instanceof ForumTopicLegalHold) {
                throw ValidationException::withMessages([
                    'release_reason' => __('forum_topic_lifecycle.validation.no_active_hold'),
                ]);
            }

            $hold->forceFill([
                'released_at' => now(),
                'released_by_user_id' => $actor->id,
                'release_reason' => $releaseReason,
                'active_key' => null,
            ])->save();
            $locked->forceFill([
                'legal_hold_at' => null,
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $this->lifecycle->record(
                topic: $locked,
                type: ForumTopicLifecycleEventType::LegalHoldReleased,
                actor: $actor,
                reasonCode: 'legal-hold-released',
                metadata: ['legal_hold_id' => $hold->id],
                idempotencyKey: "topic-legal-hold-released:{$hold->id}",
            );

            return $hold->refresh();
        }, 3);
    }
}

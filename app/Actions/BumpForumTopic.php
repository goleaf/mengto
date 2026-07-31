<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicLifecycleEventType;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumTopicLifecycle;
use App\Services\ForumTopicLifecycleProjection;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class BumpForumTopic
{
    public function __construct(
        private Gate $gate,
        private ForumTopicLifecycle $lifecycle,
        private ForumTopicLifecycleProjection $projection,
    ) {}

    public function handle(
        User $actor,
        ForumTopic $topic,
        int $expectedLockVersion,
    ): ForumTopic {
        $this->gate->forUser($actor)->authorize('bump', $topic);

        return DB::transaction(function () use (
            $actor,
            $topic,
            $expectedLockVersion,
        ): ForumTopic {
            $locked = ForumTopic::query()->lockForUpdate()->findOrFail($topic->id);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'lock_version' => __('forum_topic_lifecycle.validation.stale_state'),
                ]);
            }

            if (! $this->projection->snapshot($locked)->canBump) {
                throw ValidationException::withMessages([
                    'status' => __('forum_topic_lifecycle.validation.bump_cooldown'),
                ]);
            }

            $locked->forceFill([
                'last_bumped_at' => now(),
                'last_activity_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $this->lifecycle->record(
                topic: $locked,
                type: ForumTopicLifecycleEventType::Bumped,
                actor: $actor,
                reasonCode: 'controlled-bump',
                idempotencyKey: "topic-bump:{$locked->id}:{$locked->lock_version}",
            );

            return $locked->refresh();
        }, 3);
    }
}

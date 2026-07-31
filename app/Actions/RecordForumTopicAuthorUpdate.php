<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicUpdateRequestStatus;
use App\Models\ForumTopic;
use App\Models\ForumTopicUpdateRequest;
use App\Models\User;
use App\Services\ForumTopicLifecycle;
use Illuminate\Support\Facades\DB;

final readonly class RecordForumTopicAuthorUpdate
{
    public function __construct(private ForumTopicLifecycle $lifecycle) {}

    public function handle(
        ForumTopic $topic,
        User $actor,
        string $reasonCode = 'author-content-updated',
    ): ForumTopic {
        return DB::transaction(function () use (
            $topic,
            $actor,
            $reasonCode,
        ): ForumTopic {
            $locked = ForumTopic::query()->lockForUpdate()->findOrFail($topic->id);

            if ($locked->status->canonical() === ForumTopicStatus::Outdated) {
                $locked = $this->lifecycle->transition(
                    topic: $locked,
                    target: ForumTopicStatus::Open,
                    actor: $actor,
                    reasonCode: 'author-reopened-after-update',
                    expectedLockVersion: $locked->lock_version,
                );
                $locked = ForumTopic::query()->lockForUpdate()->findOrFail($locked->id);
            }

            $locked->forceFill([
                'last_author_update_at' => now(),
                'last_activity_at' => now(),
                'stale_review_requested_at' => null,
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            ForumTopicUpdateRequest::query()
                ->where('forum_topic_id', $locked->id)
                ->where('status', ForumTopicUpdateRequestStatus::Pending->value)
                ->increment('lock_version', 1, [
                    'status' => ForumTopicUpdateRequestStatus::Superseded->value,
                    'reviewed_by_user_id' => $actor->id,
                    'reviewed_at' => now(),
                    'resolution_reason' => __('forum_topic_lifecycle.reasons.author-content-updated'),
                    'updated_at' => now(),
                ]);
            $this->lifecycle->record(
                topic: $locked,
                type: ForumTopicLifecycleEventType::AuthorUpdated,
                actor: $actor,
                reasonCode: $reasonCode,
                idempotencyKey: "topic-author-update:{$locked->id}:{$locked->lock_version}",
            );

            return $locked->refresh();
        }, 3);
    }
}

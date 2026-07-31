<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicStatus;
use App\Models\ForumCategory;
use App\Models\ForumCategoryLifecycleRule;
use App\Models\ForumTopic;
use App\Models\ForumTopicLifecycleEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

final class ForumTopicLifecycleBackfillSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        ForumCategory::query()
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function (Collection $categories) use ($now): void {
                $rows = $categories
                    ->map(static fn (ForumCategory $category): array => [
                        'forum_category_id' => $category->id,
                        'stale_after_days' => (int) config('forum.lifecycle.stale_after_days'),
                        'necropost_after_days' => (int) config('forum.lifecycle.necropost_after_days'),
                        'archive_review_after_days' => config('forum.lifecycle.archive_review_after_days'),
                        'retention_review_after_days' => config('forum.lifecycle.retention_review_after_days'),
                        'bump_cooldown_hours' => (int) config('forum.lifecycle.bump_cooldown_hours'),
                        'allow_author_reopen' => (bool) config('forum.lifecycle.allow_author_reopen'),
                        'allow_author_archive' => (bool) config('forum.lifecycle.allow_author_archive'),
                        'allow_author_remove' => (bool) config('forum.lifecycle.allow_author_remove'),
                        'allow_bumping' => (bool) config('forum.lifecycle.allow_bumping'),
                        'auto_archive_enabled' => (bool) config('forum.lifecycle.auto_archive_enabled'),
                        'rules_version' => 1,
                        'is_system_managed' => true,
                        'metadata' => json_encode([], JSON_THROW_ON_ERROR),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->all();

                ForumCategoryLifecycleRule::query()->insertOrIgnore($rows);
            });

        ForumTopic::query()
            ->whereNull('state_entered_at')
            ->update(['state_entered_at' => $now]);
        ForumTopic::query()
            ->whereNull('last_author_update_at')
            ->update(['last_author_update_at' => $now]);

        $retentionDays = config('forum.lifecycle.retention_review_after_days');

        if (is_int($retentionDays) && $retentionDays > 0) {
            ForumTopic::query()
                ->whereNull('retention_until')
                ->update(['retention_until' => $now->clone()->addDays($retentionDays)]);
        }

        ForumTopic::query()
            ->select(['id', 'status', 'lock_version'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $topics) use ($now): void {
                $rows = $topics
                    ->map(static function (ForumTopic $topic) use ($now): array {
                        $status = $topic->status instanceof ForumTopicStatus
                            ? $topic->status->canonical()
                            : ForumTopicStatus::from((string) $topic->status)->canonical();

                        return [
                            'forum_topic_id' => $topic->id,
                            'actor_user_id' => null,
                            'event_type' => ForumTopicLifecycleEventType::StateChanged->value,
                            'from_status' => null,
                            'to_status' => $status->value,
                            'reason_code' => 'legacy-lifecycle-baseline',
                            'reason_translation_key' => 'forum_topic_lifecycle.reasons.legacy-lifecycle-baseline',
                            'lock_version' => $topic->lock_version,
                            'idempotency_key' => "topic-lifecycle-backfill:{$topic->id}",
                            'metadata' => json_encode(
                                ['source' => 'production-safe-backfill'],
                                JSON_THROW_ON_ERROR,
                            ),
                            'occurred_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    })
                    ->all();

                ForumTopicLifecycleEvent::query()->insertOrIgnore($rows);
            });
    }
}

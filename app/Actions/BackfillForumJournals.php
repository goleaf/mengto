<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumJournalStatus;
use App\Enums\ForumJournalType;
use App\Enums\ForumTopicType;
use App\Models\ForumJournal;
use App\Models\ForumTopic;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class BackfillForumJournals
{
    /**
     * @return array{created: int, unchanged: int, review_required: int}
     */
    public function handle(): array
    {
        $stats = [
            'created' => 0,
            'unchanged' => 0,
            'review_required' => 0,
        ];

        ForumTopic::query()
            ->select([
                'id',
                'author_id',
                'author_key',
                'type',
                'structured_data',
                'published_at',
                'created_at',
            ])
            ->where('type', ForumTopicType::Journal->value)
            ->orderBy('id')
            ->chunkById(200, function ($topics) use (&$stats): void {
                foreach ($topics as $topic) {
                    DB::transaction(function () use ($topic, &$stats): void {
                        $existing = ForumJournal::query()
                            ->where('forum_topic_id', $topic->id)
                            ->first();

                        if ($existing !== null) {
                            $stats['unchanged']++;

                            return;
                        }

                        $requestedType = $topic->structured_data['journal_type'] ?? null;
                        $type = is_string($requestedType)
                            ? ForumJournalType::tryFrom($requestedType)
                            : null;
                        $requiresReview = $type === null;
                        $ownerTimezone = $topic->author_id === null
                            ? null
                            : User::query()
                                ->whereKey($topic->author_id)
                                ->value('timezone');

                        ForumJournal::query()->create([
                            'forum_topic_id' => $topic->id,
                            'owner_user_id' => $topic->author_id,
                            'owner_key' => $topic->author_key,
                            'stable_key' => "journal-legacy-topic-{$topic->id}",
                            'creation_idempotency_key' => "journal:legacy-topic:{$topic->id}",
                            'type' => $type ?? ForumJournalType::General,
                            'status' => ForumJournalStatus::Active,
                            'started_on' => ($topic->published_at ?? $topic->created_at ?? now())
                                ->toDateString(),
                            'timezone' => is_string($ownerTimezone) && $ownerTimezone !== ''
                                ? $ownerTimezone
                                : config('app.timezone', 'UTC'),
                            'lock_version' => 0,
                            'metadata' => [
                                'legacy_topic_backfill' => true,
                                'requires_type_review' => $requiresReview,
                            ],
                        ]);

                        $stats['created']++;

                        if ($requiresReview) {
                            $stats['review_required']++;
                        }
                    }, 3);
                }
            });

        return $stats;
    }
}

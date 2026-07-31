<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicStatus;
use App\Models\ForumTopic;
use App\Services\ForumActor;
use App\Services\ForumTopicLifecycle;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateTopic
{
    public function __construct(
        private PrepareTopicData $prepareTopicData,
        private ForumActor $actor,
        private ForumTopicLifecycle $lifecycle,
        private RecordForumTopicAuthorUpdate $recordAuthorUpdate,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(ForumTopic $topic, array $data): ForumTopic
    {
        $preparedTopic = $this->prepareTopicData->handle($data, $topic->media ?? []);

        try {
            return DB::transaction(function () use ($data, $topic, $preparedTopic): ForumTopic {
                $prepared = $preparedTopic->attributes;
                $targetStatus = $prepared['status'];
                unset(
                    $prepared['status'],
                    $prepared['published_at'],
                    $prepared['state_entered_at'],
                    $prepared['last_author_update_at'],
                );
                $prepared['structured_data'] = [
                    ...($topic->structured_data ?? []),
                    'animal_context' => $data['animal_context'] ?? 'taxa',
                ];
                $topic->update($prepared);
                $topic->taxa()->sync(collect($data['taxon_ids'] ?? [])
                    ->mapWithKeys(static fn (int|string $taxonId): array => [
                        (int) $taxonId => [
                            'context_type' => 'subject',
                            'topic_time_snapshot' => json_encode(
                                ['selected_at' => now()->toIso8601String()],
                                JSON_THROW_ON_ERROR,
                            ),
                        ],
                    ])
                    ->all());

                $topic = $topic->refresh();
                $actor = $this->actor->requireUser();

                if (
                    $topic->status->canonical() === ForumTopicStatus::Draft
                    && $targetStatus === ForumTopicStatus::Published
                ) {
                    $topic = $this->lifecycle->transition(
                        topic: $topic,
                        target: ForumTopicStatus::Published,
                        actor: $actor,
                        reasonCode: 'author-published',
                        expectedLockVersion: $topic->lock_version,
                    );
                }

                return $this->recordAuthorUpdate->handle($topic, $actor);
            }, 3);
        } catch (Throwable $exception) {
            $this->prepareTopicData->discardNewMedia($preparedTopic);

            throw $exception;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumTopic;
use Illuminate\Support\Facades\DB;

class UpdateTopic
{
    public function __construct(private readonly PrepareTopicData $prepareTopicData) {}

    /** @param array<string, mixed> $data */
    public function handle(ForumTopic $topic, array $data): ForumTopic
    {
        return DB::transaction(function () use ($data, $topic): ForumTopic {
            $prepared = $this->prepareTopicData->handle($data, $topic->media ?? []);
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

            return $topic->refresh();
        }, 3);
    }
}

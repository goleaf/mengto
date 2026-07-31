<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumTopic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTopic
{
    public function __construct(private readonly PrepareTopicData $prepareTopicData) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): ForumTopic
    {
        return DB::transaction(function () use ($data): ForumTopic {
            $topic = ForumTopic::query()->create([
                ...$this->prepareTopicData->handle($data),
                'slug' => Str::slug((string) $data['title']).'-'.Str::lower(Str::random(6)),
                'structured_data' => [
                    'animal_context' => $data['animal_context'] ?? 'taxa',
                ],
            ]);
            $topic->taxa()->sync($this->taxonPivot($data));

            return $topic;
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{context_type: string, topic_time_snapshot: string}>
     */
    private function taxonPivot(array $data): array
    {
        return collect($data['taxon_ids'] ?? [])
            ->mapWithKeys(static fn (int|string $taxonId): array => [
                (int) $taxonId => [
                    'context_type' => 'subject',
                    'topic_time_snapshot' => json_encode(
                        ['selected_at' => now()->toIso8601String()],
                        JSON_THROW_ON_ERROR,
                    ),
                ],
            ])
            ->all();
    }
}

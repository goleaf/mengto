<?php

namespace App\Actions;

use App\Models\ForumTopic;
use Illuminate\Support\Str;

class CreateTopic
{
    public function __construct(private readonly PrepareTopicData $prepareTopicData) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): ForumTopic
    {
        return ForumTopic::query()->create([
            ...$this->prepareTopicData->handle($data),
            'slug' => Str::slug((string) $data['title']).'-'.Str::lower(Str::random(6)),
        ]);
    }
}

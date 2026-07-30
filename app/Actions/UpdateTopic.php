<?php

namespace App\Actions;

use App\Models\ForumTopic;

class UpdateTopic
{
    public function __construct(private readonly PrepareTopicData $prepareTopicData) {}

    /** @param array<string, mixed> $data */
    public function handle(ForumTopic $topic, array $data): ForumTopic
    {
        $topic->update($this->prepareTopicData->handle($data, $topic->media ?? []));

        return $topic->refresh();
    }
}

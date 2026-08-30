<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumAnswer;
use App\Models\ForumTopicAcceptance;

/**
 * @extends ApplicationFactory<ForumTopicAcceptance>
 */
final class ForumTopicAcceptanceFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_topic_id' => null,
            'forum_answer_id' => ForumAnswer::factory(),
            'acceptance_type' => 'author',
            'is_active' => true,
            'accepted_at' => now(),
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumTopicAcceptance $acceptance): void {
            $answer = ForumAnswer::query()
                ->select(['id', 'topic_id'])
                ->findOrFail($acceptance->forum_answer_id);

            $acceptance->forum_topic_id = $answer->topic_id;
        });
    }

    public function forAnswer(ForumAnswer $answer): static
    {
        return $this->state([
            'forum_topic_id' => $answer->topic_id,
            'forum_answer_id' => $answer->id,
        ]);
    }
}

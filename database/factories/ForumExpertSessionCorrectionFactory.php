<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionCorrection;

/** @extends ApplicationFactory<ForumExpertSessionCorrection> */
final class ForumExpertSessionCorrectionFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_expert_session_id' => null,
            'forum_expert_session_answer_id' => ForumExpertSessionAnswer::factory(),
            'actor_user_id' => null,
            'version' => fn (array $attributes): int => (int) ForumExpertSessionCorrection::query()
                ->where('forum_expert_session_answer_id', $attributes['forum_expert_session_answer_id'])
                ->max('version') + 1,
            'previous_body' => null,
            'previous_source_links' => null,
            'corrected_body' => fake()->paragraphs(2, true),
            'corrected_source_links' => null,
            'reason' => 'Clarified the scope and updated the public reference.',
            'created_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumExpertSessionCorrection $correction): void {
            $answer = ForumExpertSessionAnswer::query()
                ->findOrFail($correction->forum_expert_session_answer_id);

            $correction->forum_expert_session_id = $answer->forum_expert_session_id;
            $correction->actor_user_id = $answer->author_user_id;
            $correction->previous_body = $answer->body;
            $correction->previous_source_links = $answer->source_links;
            $correction->corrected_source_links = $answer->source_links;
        });
    }
}

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
        $answer = ForumExpertSessionAnswer::factory()->create();

        return [
            'forum_expert_session_id' => $answer->forum_expert_session_id,
            'forum_expert_session_answer_id' => $answer->id,
            'actor_user_id' => $answer->author_user_id,
            'version' => 2,
            'previous_body' => $answer->body,
            'previous_source_links' => $answer->source_links,
            'corrected_body' => fake()->paragraphs(2, true),
            'corrected_source_links' => $answer->source_links,
            'reason' => 'Clarified the scope and updated the public reference.',
            'created_at' => now(),
        ];
    }
}

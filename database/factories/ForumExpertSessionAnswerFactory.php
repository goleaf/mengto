<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumExpertAnswerStatus;
use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionQuestion;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumExpertSessionAnswer> */
final class ForumExpertSessionAnswerFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_expert_session_id' => null,
            'forum_expert_session_question_id' => ForumExpertSessionQuestion::factory()->approved()->state([
                'status' => ForumExpertQuestionStatus::Answered,
                'moderation_status' => ForumExpertQuestionModerationStatus::Approved,
                'answered_at' => now(),
            ]),
            'author_user_id' => null,
            'stable_key' => 'answer-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'body' => fake()->paragraphs(2, true),
            'source_links' => [[
                'label' => 'Public reference',
                'url' => 'https://example.test/reference',
            ]],
            'status' => ForumExpertAnswerStatus::Published,
            'current_version' => 1,
            'answered_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumExpertSessionAnswer $answer): void {
            $question = ForumExpertSessionQuestion::query()
                ->with('session')
                ->findOrFail($answer->forum_expert_session_question_id);

            $answer->forum_expert_session_id = $question->forum_expert_session_id;
            $answer->author_user_id = $question->session->created_by_user_id;
        });
    }

    public function corrected(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumExpertAnswerStatus::Corrected,
            'current_version' => 2,
        ]);
    }
}

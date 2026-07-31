<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionQuestion;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumExpertSessionQuestion> */
final class ForumExpertSessionQuestionFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_expert_session_id' => ForumExpertSession::factory(),
            'author_user_id' => User::factory(),
            'stable_key' => 'question-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'body' => fake()->paragraph(),
            'status' => ForumExpertQuestionStatus::Queued,
            'moderation_status' => ForumExpertQuestionModerationStatus::Pending,
            'queue_position' => fn (array $attributes): int => (int) ForumExpertSessionQuestion::query()
                ->where('forum_expert_session_id', $attributes['forum_expert_session_id'])
                ->max('queue_position') + 1,
            'moderation_reason_code' => null,
            'moderation_reason' => null,
            'selected_at' => null,
            'answered_at' => null,
            'declined_at' => null,
            'withdrawn_at' => null,
            'removed_at' => null,
            'lock_version' => 0,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (): array => [
            'moderation_status' => ForumExpertQuestionModerationStatus::Approved,
        ]);
    }

    public function selected(): static
    {
        return $this->approved()->state(fn (): array => [
            'status' => ForumExpertQuestionStatus::Selected,
            'selected_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->approved()->state(fn (): array => [
            'status' => ForumExpertQuestionStatus::Declined,
            'declined_at' => now(),
            'moderation_reason_code' => 'host-declined',
            'moderation_reason' => 'The session cannot cover this question safely.',
        ]);
    }
}

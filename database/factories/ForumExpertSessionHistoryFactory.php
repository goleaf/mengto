<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionHistory;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumExpertSessionHistory> */
final class ForumExpertSessionHistoryFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_expert_session_id' => ForumExpertSession::factory(),
            'forum_expert_session_question_id' => null,
            'forum_expert_session_answer_id' => null,
            'actor_user_id' => null,
            'event_type' => 'created',
            'from_status' => null,
            'to_status' => 'published',
            'reason_code' => 'session-created',
            'summary_translation_key' => 'forum_expert_sessions.history.created',
            'metadata' => null,
            'idempotency_key' => (string) Str::uuid(),
            'created_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumExpertSessionHistory $history): void {
            $session = ForumExpertSession::query()
                ->findOrFail($history->forum_expert_session_id);

            $history->actor_user_id = $session->created_by_user_id;
        });
    }
}

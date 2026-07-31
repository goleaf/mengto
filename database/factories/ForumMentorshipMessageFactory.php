<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumMentorship;
use App\Models\ForumMentorshipMessage;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumMentorshipMessage>
 */
final class ForumMentorshipMessageFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_mentorship_id' => ForumMentorship::factory()->active(),
            'sender_user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'idempotency_key' => 'factory:message:'.Str::uuid()->toString(),
            'created_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumMentorshipMessage $message): void {
            if ($message->forum_mentorship_id !== null) {
                $message->sender_user_id = ForumMentorship::query()
                    ->whereKey($message->forum_mentorship_id)
                    ->value('mentor_user_id');
            }
        });
    }
}

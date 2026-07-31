<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumPoll;
use App\Models\ForumPollVote;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumPollVote>
 */
final class ForumPollVoteFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_poll_id' => ForumPoll::factory(),
            'user_id' => User::factory(),
            'choices' => [],
            'idempotency_key' => 'factory:poll-vote:'.Str::lower((string) Str::ulid()),
            'lock_version' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(static function (ForumPollVote $vote): void {
            if ($vote->choices !== []) {
                return;
            }

            $choice = $vote->poll()->firstOrFail()->options()->value('id');
            $vote->forceFill(['choices' => [(int) $choice]])->save();
        });
    }
}

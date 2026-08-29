<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumPoll;
use App\Models\ForumPollVote;
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
            'user_id' => null,
            'choices' => [],
            'idempotency_key' => 'factory:poll-vote:'.Str::lower((string) Str::ulid()),
            'lock_version' => 0,
        ];
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(static function (ForumPollVote $vote): void {
                if ($vote->forum_poll_id !== null) {
                    $vote->user_id = ForumPoll::query()
                        ->whereKey($vote->forum_poll_id)
                        ->with('group:id,owner_user_id')
                        ->firstOrFail()
                        ->group
                        ->owner_user_id;
                }
            })
            ->afterCreating(static function (ForumPollVote $vote): void {
                if ($vote->choices !== []) {
                    return;
                }

                $choice = $vote->poll()->firstOrFail()->options()->value('id');
                $vote->forceFill(['choices' => [(int) $choice]])->save();

                $poll = $vote->poll()->with(['votes:id,forum_poll_id,choices', 'options'])->firstOrFail();
                $votes = $poll->votes;

                foreach ($poll->options as $option) {
                    $option->forceFill([
                        'selection_count' => $votes->filter(
                            static fn (ForumPollVote $candidate): bool => in_array(
                                $option->id,
                                $candidate->choices,
                                true,
                            ),
                        )->count(),
                        'first_choice_count' => $votes->filter(
                            static fn (ForumPollVote $candidate): bool => ($candidate->choices[0] ?? null) === $option->id,
                        )->count(),
                    ])->save();
                }

                $poll->forceFill(['total_vote_count' => $votes->count()])->save();
            });
    }
}

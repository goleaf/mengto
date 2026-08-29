<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumVoteValue;
use App\Models\ForumAnswer;
use App\Models\ForumVote;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumVote>
 */
class ForumVoteFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'answer_id' => ForumAnswer::factory(),
            'user_id' => User::factory(),
            'effect_revision' => 0,
            'user_key' => fn (array $attributes): string => User::query()
                ->findOrFail($attributes['user_id'])
                ->actor_key,
            'value' => ForumVoteValue::Helpful,
            'reason' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(static function (ForumVote $vote): void {
            $vote->answer()->update([
                'helpful_count' => ForumVote::query()
                    ->where('answer_id', $vote->answer_id)
                    ->where('value', ForumVoteValue::Helpful->value)
                    ->count(),
            ]);
        });
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumPoll;
use App\Models\ForumPollOption;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumPollOption>
 */
final class ForumPollOptionFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_poll_id' => ForumPoll::factory(),
            'stable_key' => 'option-'.Str::lower((string) Str::ulid()),
            'label' => fake()->unique()->sentence(3),
            'position' => fake()->unique()->numberBetween(100, 60000),
            'selection_count' => 0,
            'first_choice_count' => 0,
        ];
    }
}

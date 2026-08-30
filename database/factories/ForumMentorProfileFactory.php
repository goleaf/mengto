<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumMentorProfileState;
use App\Models\ForumMentorProfile;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumMentorProfile>
 */
final class ForumMentorProfileFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'state' => ForumMentorProfileState::Active,
            'headline' => fake()->sentence(6),
            'summary' => fake()->paragraph(),
            'languages' => ['en'],
            'location_scope' => 'lt-vilnius',
            'timezone' => 'Europe/Vilnius',
            'communication_preferences' => ['platform'],
            'availability' => ['weekdays' => ['evening']],
            'capacity' => 2,
            'is_public' => true,
            'safety_acknowledged_at' => now(),
            'lock_version' => 0,
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (): array => ['state' => ForumMentorProfileState::Paused]);
    }

    public function withdrawn(): static
    {
        return $this->state(fn (): array => ['state' => ForumMentorProfileState::Withdrawn]);
    }

    public function private(): static
    {
        return $this->state(fn (): array => ['is_public' => false]);
    }
}

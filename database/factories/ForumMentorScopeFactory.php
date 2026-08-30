<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumMentorshipType;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorScope;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumMentorScope>
 */
final class ForumMentorScopeFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_mentor_profile_id' => ForumMentorProfile::factory(),
            'mentorship_type' => ForumMentorshipType::FirstTimeOwner,
            'experience_summary' => fake()->paragraph(),
            'requires_verified_expertise' => false,
            'is_active' => true,
            'scope_key' => 'factory:'.Str::uuid()->toString(),
            'metadata' => [],
        ];
    }

    public function forType(ForumMentorshipType $type): static
    {
        return $this->state(fn (): array => ['mentorship_type' => $type]);
    }

    public function requiresVerifiedExpertise(): static
    {
        return $this->state(fn (): array => ['requires_verified_expertise' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}

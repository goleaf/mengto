<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumMentorshipState;
use App\Models\ForumMentorScope;
use App\Models\ForumMentorship;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumMentorship>
 */
final class ForumMentorshipFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_mentor_scope_id' => ForumMentorScope::factory(),
            'mentor_user_id' => static fn (array $attributes): mixed => ForumMentorScope::query()
                ->whereKey($attributes['forum_mentor_scope_id'])
                ->join('forum_mentor_profiles', 'forum_mentor_profiles.id', '=', 'forum_mentor_scopes.forum_mentor_profile_id')
                ->value('forum_mentor_profiles.user_id'),
            'mentee_user_id' => User::factory(),
            'mentorship_type' => static fn (array $attributes): mixed => ForumMentorScope::query()
                ->whereKey($attributes['forum_mentor_scope_id'])
                ->value('mentorship_type'),
            'state' => ForumMentorshipState::Requested,
            'language' => 'en',
            'location_scope' => 'lt-vilnius',
            'communication_preference' => 'platform',
            'request_message' => fake()->paragraph(),
            'mentee_safety_acknowledged_at' => now(),
            'requested_at' => now(),
            'lock_version' => 0,
            'open_key' => 'factory:open:'.Str::uuid()->toString(),
            'idempotency_key' => 'factory:request:'.Str::uuid()->toString(),
            'metadata' => [],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'state' => ForumMentorshipState::Active,
            'accepted_at' => now(),
            'mentor_safety_acknowledged_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->active()->state(fn (): array => [
            'state' => ForumMentorshipState::Completed,
            'completed_at' => now(),
            'ended_at' => now(),
            'open_key' => null,
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (): array => [
            'state' => ForumMentorshipState::Declined,
            'declined_at' => now(),
            'ended_at' => now(),
            'open_key' => null,
        ]);
    }

    public function ended(): static
    {
        return $this->active()->state(fn (): array => [
            'state' => ForumMentorshipState::Ended,
            'ended_at' => now(),
            'open_key' => null,
        ]);
    }
}

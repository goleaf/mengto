<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventReviewStatus;
use App\Models\ForumEvent;
use App\Models\ForumEventReview;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumEventReview>
 */
final class ForumEventReviewFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory()->completed(),
            'reviewer_user_id' => User::factory(),
            'stable_key' => 'event-review-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(5),
            'body' => fake()->paragraph(),
            'status' => ForumEventReviewStatus::Published,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumEventReviewStatus::Hidden,
        ]);
    }
}

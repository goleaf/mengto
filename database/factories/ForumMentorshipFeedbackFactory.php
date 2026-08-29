<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumMentorship;
use App\Models\ForumMentorshipFeedback;

/**
 * @extends ApplicationFactory<ForumMentorshipFeedback>
 */
final class ForumMentorshipFeedbackFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_mentorship_id' => ForumMentorship::factory()->completed(),
            'author_user_id' => null,
            'recipient_user_id' => null,
            'rating' => 5,
            'summary' => fake()->sentence(),
            'would_recommend' => true,
            'created_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumMentorshipFeedback $feedback): void {
            if ($feedback->forum_mentorship_id === null) {
                return;
            }

            $mentorship = ForumMentorship::query()->findOrFail($feedback->forum_mentorship_id);
            $feedback->author_user_id = $mentorship->mentee_user_id;
            $feedback->recipient_user_id = $mentorship->mentor_user_id;
        });
    }
}

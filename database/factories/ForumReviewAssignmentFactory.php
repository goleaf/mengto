<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumReviewAssignmentState;
use App\Models\ForumReviewAssignment;
use App\Models\ForumReviewPanel;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumReviewAssignment>
 */
final class ForumReviewAssignmentFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_review_panel_id' => ForumReviewPanel::factory(),
            'reviewer_user_id' => User::factory(),
            'state' => ForumReviewAssignmentState::Assigned,
            'anonymous_reviewer_key' => hash('sha256', fake()->uuid()),
            'assigned_at' => now(),
            'review_deadline_at' => now()->addWeek(),
        ];
    }
}

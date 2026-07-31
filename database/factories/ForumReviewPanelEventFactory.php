<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumReviewPanel;
use App\Models\ForumReviewPanelEvent;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumReviewPanelEvent>
 */
final class ForumReviewPanelEventFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_review_panel_id' => ForumReviewPanel::factory(),
            'actor_user_id' => User::factory(),
            'event_type' => 'created',
            'reason_code' => 'factory',
            'summary_translation_key' => 'forum_review.events.created',
            'metadata' => [],
            'created_at' => now(),
        ];
    }
}

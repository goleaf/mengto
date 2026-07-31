<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumReviewPanelState;
use App\Enums\ForumReviewPanelType;
use App\Models\ForumReviewPanel;
use App\Models\ForumTopic;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumReviewPanel>
 */
final class ForumReviewPanelFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'subject_type' => 'forum-topic',
            'subject_id' => null,
            'panel_type' => ForumReviewPanelType::ContentQuality,
            'risk_class' => 'low',
            'requested_by_user_id' => User::factory(),
            'state' => ForumReviewPanelState::AwaitingAssignment,
            'required_reviewers' => 3,
            'review_deadline_at' => now()->addWeek(),
            'public_context' => [
                'summary' => fake()->sentence(),
            ],
            'metadata' => [],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumReviewPanel $panel): void {
            if ($panel->subject_id === null) {
                $panel->subject_id = ForumTopic::factory()->create()->id;
            }

            if ($panel->state->isOpen() && $panel->active_key === null) {
                $panel->active_key = implode(':', [
                    $panel->subject_type,
                    $panel->subject_id,
                    $panel->panel_type->value,
                ]);
            }
        });
    }
}

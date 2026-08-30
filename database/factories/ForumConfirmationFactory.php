<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConfirmationState;
use App\Models\ForumConfirmation;
use App\Models\ForumTopic;
use App\Models\User;

/**
 * @extends ApplicationFactory<ForumConfirmation>
 */
final class ForumConfirmationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'subject_type' => 'forum-topic',
            'subject_id' => null,
            'requester_user_id' => User::factory(),
            'state' => ConfirmationState::AwaitingConfirmation,
            'claim_text' => fake()->sentence(),
            'structured_claim' => [
                'claim_type' => 'community-observation',
                'value' => true,
            ],
            'scope' => [
                'locale' => 'en',
                'audience' => 'community',
            ],
            'risk_class' => 'low',
            'required_quorum' => 3,
            'required_diversity' => 2,
            'confidence' => 0,
            'supporting_votes' => 0,
            'opposing_votes' => 0,
            'abstentions' => 0,
            'review_deadline_at' => now()->addWeek(),
            'expires_at' => now()->addMonth(),
            'metadata' => ['source' => 'factory'],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (ForumConfirmation $confirmation): void {
            if ($confirmation->subject_id === null) {
                $confirmation->subject_id = (string) ForumTopic::factory()->create()->id;
            }
        });
    }

    public function forSubject(string $type, string|int $id): static
    {
        return $this->state([
            'subject_type' => $type,
            'subject_id' => (string) $id,
        ]);
    }
}

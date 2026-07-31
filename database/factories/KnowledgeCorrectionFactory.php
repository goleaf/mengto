<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KnowledgeCorrectionStatus;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCorrection;

/**
 * @extends ApplicationFactory<KnowledgeCorrection>
 */
class KnowledgeCorrectionFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => KnowledgeArticle::factory(),
            'reporter_key' => fake()->unique()->userName(),
            'reporter_user_id' => null,
            'field' => 'body',
            'suggestion' => fake()->sentence(),
            'source_url' => fake()->url(),
            'status' => KnowledgeCorrectionStatus::Submitted,
            'base_version_number' => 1,
            'reviewed_by_user_id' => null,
            'reviewed_at' => null,
            'decision_reason' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeCorrectionStatus::Accepted,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeCorrectionStatus::Rejected,
            'reviewed_at' => now(),
            'decision_reason' => fake()->sentence(),
        ]);
    }

    public function applied(): static
    {
        return $this->state(fn (): array => [
            'status' => KnowledgeCorrectionStatus::Applied,
            'reviewed_at' => now(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

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
            'field' => 'body',
            'suggestion' => fake()->sentence(),
            'source_url' => fake()->url(),
            'status' => 'submitted',
        ];
    }
}

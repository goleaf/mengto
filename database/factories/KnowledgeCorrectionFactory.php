<?php

namespace Database\Factories;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCorrection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeCorrection>
 */
class KnowledgeCorrectionFactory extends Factory
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

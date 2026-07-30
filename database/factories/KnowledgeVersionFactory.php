<?php

namespace Database\Factories;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KnowledgeVersion>
 */
class KnowledgeVersionFactory extends Factory
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
            'version_number' => 1,
            'title' => fake()->sentence(7),
            'body' => fake()->paragraphs(4, true),
            'edited_by' => fake()->name(),
            'change_summary' => 'Initial editorial review',
        ];
    }
}

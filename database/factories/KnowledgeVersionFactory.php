<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KnowledgeStatus;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeVersion;

/**
 * @extends ApplicationFactory<KnowledgeVersion>
 */
class KnowledgeVersionFactory extends ApplicationFactory
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
            'editor_user_id' => null,
            'change_summary' => 'Initial editorial review',
            'status' => KnowledgeStatus::Draft,
            'summary' => fake()->paragraph(),
            'sources' => [],
            'language' => 'en',
            'jurisdiction' => null,
            'taxon_id' => null,
            'protected_sections' => [],
        ];
    }
}

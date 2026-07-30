<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KnowledgeStatus;
use App\Models\KnowledgeArticle;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<KnowledgeArticle>
 */
class KnowledgeArticleFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(7);

        return [
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'title' => $title,
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'category' => fake()->randomElement(['behavior', 'travel', 'health', 'care']),
            'type' => 'guide',
            'difficulty' => 'beginner',
            'audience' => 'Pet owners',
            'status' => KnowledgeStatus::Published,
            'language' => 'en',
            'tags' => [fake()->word(), fake()->word()],
            'sources' => [],
            'contributors' => ['PawCircle editorial team'],
            'current_version' => 1,
            'last_reviewed_at' => now(),
            'next_review_at' => now()->addMonths(6),
            'published_at' => now(),
        ];
    }
}

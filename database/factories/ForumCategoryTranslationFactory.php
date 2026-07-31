<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;

/**
 * @extends ApplicationFactory<ForumCategoryTranslation>
 */
final class ForumCategoryTranslationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'forum_category_id' => ForumCategory::factory(),
            'locale' => 'en',
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'notice' => null,
            'rules_summary' => null,
            'is_reviewed' => true,
        ];
    }
}

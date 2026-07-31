<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumCategory;
use App\Models\ForumCategoryRedirect;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumCategoryRedirect>
 */
final class ForumCategoryRedirectFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'source_slug' => Str::slug(fake()->unique()->words(3, true)),
            'target_forum_category_id' => ForumCategory::factory(),
            'reason_code' => 'category-renamed',
            'is_permanent' => true,
        ];
    }
}

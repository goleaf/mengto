<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumCategory;
use App\Models\ForumCategoryAlias;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumCategoryAlias>
 */
final class ForumCategoryAliasFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $alias = fake()->unique()->words(2, true);

        return [
            'forum_category_id' => ForumCategory::factory(),
            'locale' => 'en',
            'alias' => $alias,
            'normalized_alias' => Str::lower($alias),
            'is_active' => true,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumReputationDimension;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumReputationDimension>
 */
final class ForumReputationDimensionFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::slug(fake()->unique()->words(2, true));

        return [
            'stable_key' => $key,
            'name_translation_key' => "forum.reputation.dimensions.{$key}.name",
            'description_translation_key' => "forum.reputation.dimensions.{$key}.description",
            'daily_actor_recipient_cap' => 10,
            'relationship_cap' => 50,
            'is_public_by_default' => true,
            'is_active' => true,
            'metadata' => [],
        ];
    }
}

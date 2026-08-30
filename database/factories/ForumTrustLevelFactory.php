<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumTrustLevel;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumTrustLevel>
 */
final class ForumTrustLevelFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::slug(fake()->unique()->words(2, true));

        return [
            'stable_key' => $key,
            'name_translation_key' => "forum.trust.levels.{$key}.name",
            'description_translation_key' => "forum.trust.levels.{$key}.description",
            'position' => fake()->unique()->numberBetween(1, 60_000),
            'is_professional' => false,
            'is_moderation_role' => false,
            'is_active' => true,
            'criteria' => [['key' => 'helpful_contributions', 'operator' => '>=', 'value' => 5]],
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }
}

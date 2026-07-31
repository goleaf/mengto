<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumCategory;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumCategory>
 */
final class ForumCategoryFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $slug = Str::slug($name);

        return [
            'stable_key' => 'forum.'.str_replace('-', '.', $slug),
            'slug' => $slug,
            'position' => fake()->numberBetween(1, 500),
            'visibility' => 'public',
            'moderation_level' => 'standard',
            'schema_version' => 1,
            'is_system_managed' => false,
            'is_active' => true,
            'rules' => [],
            'permissions' => [],
            'topic_type_keys' => ['question', 'discussion'],
            'metadata' => [],
        ];
    }

    public function systemManaged(): static
    {
        return $this->state(fn (): array => ['is_system_managed' => true]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
            'archived_at' => now(),
        ]);
    }
}

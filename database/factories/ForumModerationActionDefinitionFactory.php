<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumModerationActionDefinition;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumModerationActionDefinition> */
final class ForumModerationActionDefinitionFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = 'action-'.Str::lower((string) Str::ulid());

        return [
            'stable_key' => $key,
            'translation_key' => "forum_moderation.actions.{$key}",
            'is_restrictive' => false,
            'is_appealable' => true,
            'requires_end_at' => false,
            'requires_senior_review' => false,
            'is_active' => true,
            'position' => fake()->numberBetween(1, 100),
            'metadata' => [],
        ];
    }
}

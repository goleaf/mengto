<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumBadge;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumBadge>
 */
final class ForumBadgeFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::slug(fake()->unique()->words(2, true));

        return [
            'stable_key' => $key,
            'name_translation_key' => "forum.badges.{$key}.name",
            'description_translation_key' => "forum.badges.{$key}.description",
            'criteria_version' => 1,
            'criteria' => ['event_count' => 1],
            'revocation_rules' => ['confirmed-abuse'],
            'requires_moderation_review' => false,
            'expires' => false,
            'is_active' => true,
        ];
    }
}

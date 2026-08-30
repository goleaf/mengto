<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumCategory;
use App\Models\ForumCategoryLifecycleRule;

/**
 * @extends ApplicationFactory<ForumCategoryLifecycleRule>
 */
final class ForumCategoryLifecycleRuleFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_category_id' => ForumCategory::factory(),
            'stale_after_days' => 180,
            'necropost_after_days' => 90,
            'archive_review_after_days' => 365,
            'retention_review_after_days' => 2555,
            'bump_cooldown_hours' => 168,
            'allow_author_reopen' => true,
            'allow_author_archive' => true,
            'allow_author_remove' => true,
            'allow_bumping' => true,
            'auto_archive_enabled' => false,
            'rules_version' => 1,
            'is_system_managed' => false,
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }

    public function restrictive(): static
    {
        return $this->state(fn (): array => [
            'allow_author_reopen' => false,
            'allow_author_archive' => false,
            'allow_author_remove' => false,
            'allow_bumping' => false,
        ]);
    }
}

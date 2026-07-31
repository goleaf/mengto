<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumReportReason;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumReportReason> */
final class ForumReportReasonFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = 'reason-'.Str::lower((string) Str::ulid());

        return [
            'stable_key' => $key,
            'translation_key' => "forum_moderation.reasons.{$key}",
            'default_priority' => 'standard',
            'allows_immediate_safety' => false,
            'requires_specialist_review' => false,
            'is_active' => true,
            'position' => fake()->numberBetween(1, 500),
            'metadata' => [],
        ];
    }
}

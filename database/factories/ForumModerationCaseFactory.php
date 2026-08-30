<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumModerationCase;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumModerationCase> */
final class ForumModerationCaseFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'case_number' => 'MOD-'.Str::upper(Str::random(12)),
            'status' => 'awaiting-review',
            'priority' => 'standard',
            'opened_by_user_id' => User::factory()->administrator(),
            'summary_translation_key' => 'forum_moderation.messages.case_opened',
            'review_due_at' => now()->addDays(3),
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }
}

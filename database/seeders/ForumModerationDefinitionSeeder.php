<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ForumModerationActionDefinition;
use App\Models\ForumReportReason;
use App\Services\ForumModerationActionCatalog;
use App\Services\ForumReportReasonCatalog;
use Illuminate\Database\Seeder;

final class ForumModerationDefinitionSeeder extends Seeder
{
    public function __construct(
        private readonly ForumReportReasonCatalog $reasons,
        private readonly ForumModerationActionCatalog $actions,
    ) {}

    public function run(): void
    {
        $now = now();
        $reasonRows = [];

        foreach (ForumReportReasonCatalog::KEYS as $position => $key) {
            $priority = $this->reasons->defaultPriority($key);
            $reasonRows[] = [
                'stable_key' => $key,
                'translation_key' => "forum_moderation.reasons.{$key}",
                'default_priority' => $priority,
                'allows_immediate_safety' => $priority === 'critical',
                'requires_specialist_review' => $this->reasons->requiresSpecialistReview($key),
                'is_active' => true,
                'position' => $position + 1,
                'metadata' => json_encode([], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ForumReportReason::query()->upsert(
            $reasonRows,
            ['stable_key'],
            [
                'translation_key',
                'default_priority',
                'allows_immediate_safety',
                'requires_specialist_review',
                'is_active',
                'position',
                'metadata',
                'updated_at',
            ],
        );
        $actionRows = [];

        foreach (ForumModerationActionCatalog::KEYS as $position => $key) {
            $actionRows[] = [
                'stable_key' => $key,
                'translation_key' => "forum_moderation.actions.{$key}",
                'is_restrictive' => $this->actions->isRestrictive($key),
                'is_appealable' => $key !== 'no-action',
                'requires_end_at' => $this->actions->requiresEnd($key),
                'requires_senior_review' => $this->actions->requiresSeniorReview($key),
                'is_active' => true,
                'position' => $position + 1,
                'metadata' => json_encode([
                    'automatic_permanent_enforcement' => false,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ForumModerationActionDefinition::query()->upsert(
            $actionRows,
            ['stable_key'],
            [
                'translation_key',
                'is_restrictive',
                'is_appealable',
                'requires_end_at',
                'requires_senior_review',
                'is_active',
                'position',
                'metadata',
                'updated_at',
            ],
        );
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeWorkflowEvent;
use App\Models\User;

/**
 * @extends ApplicationFactory<KnowledgeWorkflowEvent>
 */
final class KnowledgeWorkflowEventFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'article_id' => KnowledgeArticle::factory(),
            'actor_user_id' => User::factory(),
            'event_type' => KnowledgeWorkflowEventType::StatusChanged,
            'from_status' => KnowledgeStatus::Draft,
            'to_status' => KnowledgeStatus::SubmittedForReview,
            'version_number' => 1,
            'reason_code' => 'workflow-transition',
            'summary_translation_key' => 'knowledge.events.status_changed',
            'metadata' => [],
            'idempotency_key' => fake()->uuid(),
            'created_at' => now(),
        ];
    }
}

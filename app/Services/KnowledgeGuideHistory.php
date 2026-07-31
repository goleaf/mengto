<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeVersion;
use App\Models\KnowledgeWorkflowEvent;
use App\Models\User;
use Illuminate\Support\Str;

final class KnowledgeGuideHistory
{
    public function snapshot(
        KnowledgeArticle $article,
        User $actor,
        string $changeSummary,
    ): KnowledgeVersion {
        return $article->versions()->create([
            'version_number' => $article->current_version,
            'title' => $article->title,
            'summary' => $article->summary,
            'body' => $article->body,
            'sources' => $article->sources,
            'language' => $article->language,
            'jurisdiction' => $article->jurisdiction,
            'taxon_id' => $article->taxon_id,
            'protected_sections' => $article->protected_sections,
            'status' => $article->status,
            'edited_by' => $actor->name,
            'editor_user_id' => $actor->id,
            'change_summary' => $changeSummary,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        KnowledgeArticle $article,
        ?User $actor,
        KnowledgeWorkflowEventType $eventType,
        string $reasonCode,
        string $summaryTranslationKey,
        array $metadata = [],
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?int $versionNumber = null,
    ): KnowledgeWorkflowEvent {
        return $article->workflowEvents()->create([
            'actor_user_id' => $actor?->id,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'version_number' => $versionNumber,
            'reason_code' => $reasonCode,
            'summary_translation_key' => $summaryTranslationKey,
            'metadata' => $metadata,
            'idempotency_key' => (string) Str::uuid(),
            'created_at' => now(),
        ]);
    }
}

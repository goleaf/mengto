<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeVersion;
use App\Models\User;
use App\Services\KnowledgeGuideHistory;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RollbackKnowledgeGuideVersion
{
    public function __construct(
        private readonly Gate $gate,
        private readonly KnowledgeGuideHistory $history,
    ) {}

    public function handle(
        User $actor,
        KnowledgeArticle $article,
        KnowledgeVersion $version,
        string $reason,
        int $expectedLockVersion,
    ): KnowledgeArticle {
        $this->gate->forUser($actor)->authorize('rollback', $article);

        if ($version->article_id !== $article->id) {
            throw ValidationException::withMessages([
                'rollbackVersionId' => __('knowledge.validation.version_mismatch'),
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $article,
            $version,
            $reason,
            $expectedLockVersion,
        ): KnowledgeArticle {
            $locked = KnowledgeArticle::query()
                ->lockForUpdate()
                ->findOrFail($article->id);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'rollbackExpectedLockVersion' => __('knowledge.validation.edit_conflict'),
                ]);
            }

            $locked->update([
                'title' => $version->title,
                'summary' => $version->summary ?? $locked->summary,
                'body' => $version->body,
                'sources' => $version->sources ?? $locked->sources,
                'language' => $version->language ?? $locked->language,
                'jurisdiction' => $version->jurisdiction ?? $locked->jurisdiction,
                'taxon_id' => $version->taxon_id ?? $locked->taxon_id,
                'protected_sections' => $version->protected_sections ?? $locked->protected_sections,
                'current_version' => $locked->current_version + 1,
                'lock_version' => $locked->lock_version + 1,
            ]);

            $this->history->snapshot($locked, $actor, trim($reason));
            $this->history->record(
                $locked,
                $actor,
                KnowledgeWorkflowEventType::RolledBack,
                'version-rollback',
                'knowledge.events.rolled_back',
                ['restored_version_number' => $version->version_number],
                versionNumber: $locked->current_version,
            );

            return $locked->refresh();
        });
    }
}

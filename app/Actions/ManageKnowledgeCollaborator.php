<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\User;
use App\Services\KnowledgeGuideHistory;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManageKnowledgeCollaborator
{
    public function __construct(
        private readonly Gate $gate,
        private readonly KnowledgeGuideHistory $history,
    ) {}

    public function grant(
        User $actor,
        KnowledgeArticle $article,
        User $collaborator,
        KnowledgeCollaboratorRole $role,
        ?string $attributionName = null,
    ): KnowledgeArticleCollaborator {
        $this->gate->forUser($actor)->authorize('manageCollaborators', $article);

        if (! $collaborator->isActive()) {
            throw ValidationException::withMessages([
                'collaboratorEmail' => __('knowledge.validation.collaborator_inactive'),
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $article,
            $collaborator,
            $role,
            $attributionName,
        ): KnowledgeArticleCollaborator {
            $record = KnowledgeArticleCollaborator::query()
                ->where('article_id', $article->id)
                ->where('user_id', $collaborator->id)
                ->where('role', $role->value)
                ->lockForUpdate()
                ->first();

            if ($record !== null && $record->revoked_at === null) {
                return $record;
            }

            if ($record === null) {
                $record = KnowledgeArticleCollaborator::query()->create([
                    'article_id' => $article->id,
                    'user_id' => $collaborator->id,
                    'role' => $role,
                    'added_by_user_id' => $actor->id,
                    'attribution_name' => $attributionName,
                ]);
            } else {
                $record->update([
                    'added_by_user_id' => $actor->id,
                    'attribution_name' => $attributionName,
                    'revoked_at' => null,
                    'revoked_by_user_id' => null,
                ]);
            }

            $this->history->record(
                $article,
                $actor,
                KnowledgeWorkflowEventType::CollaboratorAdded,
                'collaborator-granted',
                'knowledge.events.collaborator_added',
                [
                    'collaborator_user_id' => $collaborator->id,
                    'role' => $role->value,
                ],
            );

            return $record;
        });
    }

    public function revoke(
        User $actor,
        KnowledgeArticleCollaborator $collaborator,
    ): KnowledgeArticleCollaborator {
        $collaborator->loadMissing('article');
        $article = $collaborator->article;

        abort_if($article === null, 404);
        $this->gate->forUser($actor)->authorize('manageCollaborators', $article);

        return DB::transaction(function () use (
            $actor,
            $article,
            $collaborator,
        ): KnowledgeArticleCollaborator {
            $locked = KnowledgeArticleCollaborator::query()
                ->lockForUpdate()
                ->findOrFail($collaborator->id);

            if (
                $locked->role === KnowledgeCollaboratorRole::Maintainer
                && KnowledgeArticleCollaborator::query()
                    ->where('article_id', $article->id)
                    ->where('role', KnowledgeCollaboratorRole::Maintainer->value)
                    ->whereNull('revoked_at')
                    ->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'collaboratorId' => __('knowledge.validation.last_maintainer'),
                ]);
            }

            $locked->update([
                'revoked_at' => now(),
                'revoked_by_user_id' => $actor->id,
            ]);

            $this->history->record(
                $article,
                $actor,
                KnowledgeWorkflowEventType::CollaboratorRemoved,
                'collaborator-revoked',
                'knowledge.events.collaborator_removed',
                [
                    'collaborator_user_id' => $locked->user_id,
                    'role' => $locked->role->value,
                ],
            );

            return $locked->refresh();
        });
    }
}

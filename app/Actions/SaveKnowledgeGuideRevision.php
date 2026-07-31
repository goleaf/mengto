<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\KnowledgeGuideData;
use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\KnowledgeGuideHistory;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SaveKnowledgeGuideRevision
{
    public function __construct(
        private readonly Gate $gate,
        private readonly KnowledgeGuideHistory $history,
    ) {}

    public function handle(
        User $actor,
        KnowledgeArticle $article,
        KnowledgeGuideData $data,
    ): KnowledgeArticle {
        $this->gate->forUser($actor)->authorize('update', $article);

        return DB::transaction(function () use ($actor, $article, $data): KnowledgeArticle {
            $locked = KnowledgeArticle::query()
                ->lockForUpdate()
                ->findOrFail($article->id);

            if ($locked->lock_version !== $data->expectedLockVersion) {
                throw ValidationException::withMessages([
                    'form.expectedLockVersion' => __('knowledge.validation.edit_conflict'),
                ]);
            }

            if (
                $locked->editorial_locked_at !== null
                && $locked->editorial_locked_by_user_id !== $actor->id
                && ! $actor->isAdministrator()
            ) {
                throw ValidationException::withMessages([
                    'form.body' => __('knowledge.validation.editorial_locked'),
                ]);
            }

            $locked->update([
                'title' => trim($data->title),
                'summary' => trim($data->summary),
                'body' => trim($data->body),
                'category' => $data->category,
                'type' => $data->type,
                'difficulty' => $data->difficulty,
                'audience' => $data->audience,
                'language' => $data->language,
                'jurisdiction' => $data->jurisdiction,
                'taxon_id' => $data->taxonId,
                'discussion_topic_id' => $data->discussionTopicId,
                'sources' => $data->sources,
                'protected_sections' => $data->protectedSections,
                'current_version' => $locked->current_version + 1,
                'lock_version' => $locked->lock_version + 1,
            ]);

            if (! $locked->activeCollaborators()->where('user_id', $actor->id)->exists()) {
                $locked->collaborators()->create([
                    'user_id' => $actor->id,
                    'role' => KnowledgeCollaboratorRole::Contributor,
                    'added_by_user_id' => $actor->id,
                    'attribution_name' => $actor->name,
                ]);
            }

            $this->history->snapshot($locked, $actor, $data->changeSummary);
            $this->history->record(
                $locked,
                $actor,
                KnowledgeWorkflowEventType::ContentRevised,
                'content-revised',
                'knowledge.events.content_revised',
                versionNumber: $locked->current_version,
            );

            return $locked->refresh();
        });
    }
}

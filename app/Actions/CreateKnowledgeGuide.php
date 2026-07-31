<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\KnowledgeGuideData;
use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Services\KnowledgeGuideHistory;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateKnowledgeGuide
{
    public function __construct(
        private readonly Gate $gate,
        private readonly KnowledgeGuideHistory $history,
    ) {}

    public function handle(User $actor, KnowledgeGuideData $data): KnowledgeArticle
    {
        $this->gate->forUser($actor)->authorize('create', KnowledgeArticle::class);

        return DB::transaction(function () use ($actor, $data): KnowledgeArticle {
            $article = KnowledgeArticle::query()->create([
                'created_by_user_id' => $actor->id,
                'slug' => Str::slug($data->title).'-'.Str::lower(Str::random(8)),
                'translation_group_key' => 'guide-'.Str::lower((string) Str::uuid()),
                'title' => trim($data->title),
                'summary' => trim($data->summary),
                'body' => trim($data->body),
                'category' => $data->category,
                'type' => $data->type,
                'difficulty' => $data->difficulty,
                'audience' => $data->audience,
                'status' => KnowledgeStatus::Draft,
                'language' => $data->language,
                'jurisdiction' => $data->jurisdiction,
                'taxon_id' => $data->taxonId,
                'discussion_topic_id' => $data->discussionTopicId,
                'sources' => $data->sources,
                'protected_sections' => $data->protectedSections,
                'tags' => [],
                'contributors' => [],
                'current_version' => 1,
                'lock_version' => 0,
            ]);

            $article->collaborators()->create([
                'user_id' => $actor->id,
                'role' => KnowledgeCollaboratorRole::Maintainer,
                'added_by_user_id' => $actor->id,
                'attribution_name' => $actor->name,
            ]);
            $this->history->snapshot($article, $actor, $data->changeSummary);
            $this->history->record(
                $article,
                $actor,
                KnowledgeWorkflowEventType::Created,
                'guide-created',
                'knowledge.events.created',
                versionNumber: 1,
            );

            return $article->refresh();
        });
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\KnowledgeCollaboratorRole;
use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Database\Seeder;

final class CollaborativeGuideDemoSeeder extends Seeder
{
    public function run(): void
    {
        $administrator = User::query()
            ->where('actor_key', 'demo-administrator')
            ->firstOrFail();
        $member = User::query()
            ->where('actor_key', 'mia-carter')
            ->firstOrFail();

        $this->synchronize(
            'dog-travel-documents-lithuania-to-poland',
            'guide-dog-travel-documents-lithuania-poland',
            $administrator,
        );
        $this->synchronize(
            'help-a-cat-feel-safe-in-a-carrier',
            'guide-help-cat-feel-safe-carrier',
            $member,
        );
    }

    private function synchronize(
        string $slug,
        string $translationGroupKey,
        User $maintainer,
    ): void {
        $article = KnowledgeArticle::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $article->forceFill([
            'created_by_user_id' => $maintainer->id,
            'discussion_topic_id' => $article->source_topic_id,
            'translation_group_key' => $translationGroupKey,
        ])->save();

        $article->collaborators()->firstOrCreate(
            [
                'user_id' => $maintainer->id,
                'role' => KnowledgeCollaboratorRole::Maintainer->value,
            ],
            [
                'added_by_user_id' => $maintainer->id,
                'attribution_name' => $maintainer->name,
            ],
        );

        if (! $article->versions()->exists()) {
            $article->versions()->create([
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
                'edited_by' => $maintainer->name,
                'editor_user_id' => $maintainer->id,
                'change_summary' => 'Initial demo guide snapshot.',
            ]);
        }

        $article->workflowEvents()->firstOrCreate(
            ['idempotency_key' => "demo-guide:{$slug}:created:v1"],
            [
                'actor_user_id' => $maintainer->id,
                'event_type' => KnowledgeWorkflowEventType::Created,
                'to_status' => $article->status,
                'version_number' => $article->current_version,
                'reason_code' => 'demo-guide-created',
                'summary_translation_key' => 'knowledge.events.created',
                'metadata' => ['source_topic_id' => $article->source_topic_id],
                'created_at' => now(),
            ],
        );
    }
}

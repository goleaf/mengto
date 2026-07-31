<?php

declare(strict_types=1);

use App\Enums\KnowledgeStatus;
use App\Models\KnowledgeArticle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table): void {
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('translation_group_key', 160)->nullable();
            $table->string('jurisdiction', 120)->nullable();
            $table->foreignId('taxon_id')->nullable()->constrained('taxa')->nullOnDelete();
            $table->foreignId('discussion_topic_id')->nullable()->constrained('forum_topics')->nullOnDelete();
            $table->foreignId('replaced_by_article_id')->nullable()->constrained('knowledge_articles')->nullOnDelete();
            $table->json('protected_sections')->nullable();
            $table->timestamp('editorial_locked_at')->nullable();
            $table->foreignId('editorial_locked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('editorial_lock_reason', 500)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
        });

        KnowledgeArticle::query()
            ->where('status', 'review')
            ->update(['status' => KnowledgeStatus::SubmittedForReview->value]);

        KnowledgeArticle::query()
            ->select(['id'])
            ->whereNull('translation_group_key')
            ->chunkById(250, function ($articles): void {
                $rows = $articles
                    ->map(fn (KnowledgeArticle $article): array => [
                        'id' => $article->id,
                        'translation_group_key' => "guide-{$article->id}",
                    ])
                    ->all();

                KnowledgeArticle::query()->upsert(
                    $rows,
                    ['id'],
                    ['translation_group_key'],
                );
            });

        Schema::table('knowledge_articles', function (Blueprint $table): void {
            $table->unique(
                ['translation_group_key', 'language'],
                'knowledge_articles_translation_locale_unique',
            );
            $table->index(
                ['status', 'language', 'last_reviewed_at', 'id'],
                'knowledge_articles_status_language_review_idx',
            );
            $table->index(
                ['taxon_id', 'status', 'last_reviewed_at'],
                'knowledge_articles_taxon_status_review_idx',
            );
            $table->index(
                ['created_by_user_id', 'status', 'updated_at'],
                'knowledge_articles_creator_status_updated_idx',
            );
        });

        Schema::table('knowledge_versions', function (Blueprint $table): void {
            $table->foreignId('editor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->nullable();
            $table->text('summary')->nullable();
            $table->json('sources')->nullable();
            $table->string('language', 12)->nullable();
            $table->string('jurisdiction', 120)->nullable();
            $table->foreignId('taxon_id')->nullable()->constrained('taxa')->nullOnDelete();
            $table->json('protected_sections')->nullable();

        });

        Schema::table('knowledge_corrections', function (Blueprint $table): void {
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('base_version_number')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('decision_reason', 500)->nullable();

            $table->index(
                ['reviewed_by_user_id', 'status', 'reviewed_at'],
                'knowledge_corrections_reviewer_status_idx',
            );
        });

        Schema::create('knowledge_article_collaborators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 40);
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('attribution_name', 120)->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['article_id', 'user_id', 'role'],
                'knowledge_collaborators_article_user_role_unique',
            );
            $table->index(
                ['user_id', 'role', 'revoked_at'],
                'knowledge_collaborators_user_role_active_idx',
            );
            $table->index(
                ['article_id', 'revoked_at', 'role'],
                'knowledge_collaborators_article_active_idx',
            );
        });

        Schema::create('knowledge_workflow_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->unsignedInteger('version_number')->nullable();
            $table->string('reason_code', 120);
            $table->string('summary_translation_key', 180);
            $table->json('metadata')->nullable();
            $table->string('idempotency_key', 180)->nullable()->unique();
            $table->timestamp('created_at');

            $table->index(
                ['article_id', 'created_at', 'id'],
                'knowledge_workflow_events_article_created_idx',
            );
            $table->index(
                ['actor_user_id', 'event_type', 'created_at'],
                'knowledge_workflow_events_actor_type_idx',
            );
        });
    }

    public function down(): void
    {
        KnowledgeArticle::query()
            ->whereIn('status', [
                KnowledgeStatus::SubmittedForReview->value,
                KnowledgeStatus::ChangesRequested->value,
                KnowledgeStatus::CommunityReviewed->value,
                KnowledgeStatus::ExpertReviewed->value,
                KnowledgeStatus::CorrectionRequested->value,
            ])
            ->update(['status' => 'review']);

        KnowledgeArticle::query()
            ->where('status', KnowledgeStatus::Replaced->value)
            ->update(['status' => KnowledgeStatus::Archived->value]);

        Schema::dropIfExists('knowledge_workflow_events');
        Schema::dropIfExists('knowledge_article_collaborators');

        Schema::table('knowledge_corrections', function (Blueprint $table): void {
            $table->dropIndex('knowledge_corrections_reviewer_status_idx');
            $table->dropConstrainedForeignId('reporter_user_id');
            $table->dropConstrainedForeignId('reviewed_by_user_id');
            $table->dropColumn([
                'base_version_number',
                'reviewed_at',
                'decision_reason',
            ]);
        });

        Schema::table('knowledge_versions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('editor_user_id');
            $table->dropConstrainedForeignId('taxon_id');
            $table->dropColumn([
                'status',
                'summary',
                'sources',
                'language',
                'jurisdiction',
                'protected_sections',
            ]);
        });

        Schema::table('knowledge_articles', function (Blueprint $table): void {
            $table->dropUnique('knowledge_articles_translation_locale_unique');
            $table->dropIndex('knowledge_articles_status_language_review_idx');
            $table->dropIndex('knowledge_articles_taxon_status_review_idx');
            $table->dropIndex('knowledge_articles_creator_status_updated_idx');
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('taxon_id');
            $table->dropConstrainedForeignId('discussion_topic_id');
            $table->dropConstrainedForeignId('replaced_by_article_id');
            $table->dropConstrainedForeignId('editorial_locked_by_user_id');
            $table->dropColumn([
                'translation_group_key',
                'jurisdiction',
                'protected_sections',
                'editorial_locked_at',
                'editorial_lock_reason',
                'lock_version',
            ]);
        });
    }
};

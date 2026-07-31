<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table): void {
            $table->index(
                'discussion_topic_id',
                'knowledge_articles_discussion_topic_fk_idx',
            );
            $table->index(
                'replaced_by_article_id',
                'knowledge_articles_replacement_fk_idx',
            );
            $table->index(
                'editorial_locked_by_user_id',
                'knowledge_articles_editorial_locker_fk_idx',
            );
        });

        Schema::table('knowledge_versions', function (Blueprint $table): void {
            $table->index(
                'editor_user_id',
                'knowledge_versions_editor_fk_idx',
            );
            $table->index(
                'taxon_id',
                'knowledge_versions_taxon_fk_idx',
            );
        });

        Schema::table('knowledge_corrections', function (Blueprint $table): void {
            $table->index(
                'reporter_user_id',
                'knowledge_corrections_reporter_fk_idx',
            );
        });

        Schema::table('knowledge_article_collaborators', function (Blueprint $table): void {
            $table->index(
                'added_by_user_id',
                'knowledge_collaborators_added_by_fk_idx',
            );
            $table->index(
                'revoked_by_user_id',
                'knowledge_collaborators_revoked_by_fk_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_article_collaborators', function (Blueprint $table): void {
            $table->dropIndex('knowledge_collaborators_revoked_by_fk_idx');
            $table->dropIndex('knowledge_collaborators_added_by_fk_idx');
        });

        Schema::table('knowledge_corrections', function (Blueprint $table): void {
            $table->dropIndex('knowledge_corrections_reporter_fk_idx');
        });

        Schema::table('knowledge_versions', function (Blueprint $table): void {
            $table->dropIndex('knowledge_versions_taxon_fk_idx');
            $table->dropIndex('knowledge_versions_editor_fk_idx');
        });

        Schema::table('knowledge_articles', function (Blueprint $table): void {
            $table->dropIndex('knowledge_articles_editorial_locker_fk_idx');
            $table->dropIndex('knowledge_articles_replacement_fk_idx');
            $table->dropIndex('knowledge_articles_discussion_topic_fk_idx');
        });
    }
};

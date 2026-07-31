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
            $table->foreignId('translated_from_article_id')
                ->nullable()
                ->constrained('knowledge_articles')
                ->nullOnDelete();
            $table->foreignId('translated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('translation_source', 40)->nullable();

            $table->index(
                ['translated_from_article_id', 'language', 'status'],
                'knowledge_articles_translation_source_locale_status_idx',
            );
            $table->index(
                ['translated_by_user_id', 'translation_source', 'created_at'],
                'knowledge_articles_translator_source_created_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_articles', function (Blueprint $table): void {
            $table->dropIndex('knowledge_articles_translation_source_locale_status_idx');
            $table->dropIndex('knowledge_articles_translator_source_created_idx');
            $table->dropConstrainedForeignId('translated_from_article_id');
            $table->dropConstrainedForeignId('translated_by_user_id');
            $table->dropColumn('translation_source');
        });
    }
};

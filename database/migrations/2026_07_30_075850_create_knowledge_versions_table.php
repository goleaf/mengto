<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title', 180);
            $table->text('body');
            $table->string('edited_by', 120);
            $table->string('change_summary', 240);
            $table->timestamps();

            $table->unique(['article_id', 'version_number'], 'knowledge_versions_article_version_unique');
            $table->index(['article_id', 'created_at', 'id'], 'knowledge_versions_article_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_versions');
    }
};

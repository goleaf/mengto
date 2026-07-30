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
        Schema::create('knowledge_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->cascadeOnDelete();
            $table->string('reporter_key', 80);
            $table->string('field', 80);
            $table->text('suggestion');
            $table->string('source_url', 500)->nullable();
            $table->string('status', 40)->default('submitted');
            $table->timestamps();

            $table->index(['article_id', 'status', 'created_at'], 'knowledge_corrections_article_status_created_idx');
            $table->index(['reporter_key', 'status'], 'knowledge_corrections_reporter_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_corrections');
    }
};

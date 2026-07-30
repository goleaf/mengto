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
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_topic_id')->nullable()->constrained('forum_topics')->nullOnDelete();
            $table->string('slug', 180)->unique();
            $table->string('title', 180);
            $table->text('summary');
            $table->text('body');
            $table->string('category', 80);
            $table->string('type', 60);
            $table->string('difficulty', 60)->default('beginner');
            $table->string('audience', 120)->nullable();
            $table->string('status', 40);
            $table->string('language', 12)->default('en');
            $table->json('tags')->nullable();
            $table->json('sources')->nullable();
            $table->json('contributors')->nullable();
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'category', 'last_reviewed_at', 'id'], 'knowledge_articles_status_category_reviewed_idx');
            $table->index(['type', 'status', 'updated_at'], 'knowledge_articles_type_status_updated_idx');
            $table->index(['source_topic_id', 'status'], 'knowledge_articles_topic_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};

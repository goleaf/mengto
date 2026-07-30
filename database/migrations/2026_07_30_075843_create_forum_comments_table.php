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
        Schema::create('forum_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('forum_answers')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('forum_comments')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_key', 80);
            $table->string('author_name', 120);
            $table->string('author_initials', 8);
            $table->text('body');
            $table->string('status', 40)->default('published');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['topic_id', 'status', 'created_at', 'id'], 'forum_comments_topic_status_created_idx');
            $table->index(['answer_id', 'status', 'created_at', 'id'], 'forum_comments_answer_status_created_idx');
            $table->index(['parent_id', 'status'], 'forum_comments_parent_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_comments');
    }
};

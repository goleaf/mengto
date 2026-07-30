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
        Schema::create('forum_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_key', 80);
            $table->string('author_name', 120);
            $table->string('author_initials', 8);
            $table->string('author_role', 120)->nullable();
            $table->text('body');
            $table->string('experience_type', 60)->default('personal-experience');
            $table->boolean('is_verified_expert')->default(false);
            $table->string('expertise', 100)->nullable();
            $table->string('qualification_region', 120)->nullable();
            $table->json('sources')->nullable();
            $table->json('media')->nullable();
            $table->string('status', 40)->default('published');
            $table->boolean('is_accepted')->default(false);
            $table->boolean('is_highlighted')->default(false);
            $table->boolean('needs_source')->default(false);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();

            $table->index(['topic_id', 'status', 'is_accepted', 'created_at', 'id'], 'forum_answers_topic_status_accepted_idx');
            $table->index(['is_verified_expert', 'status', 'created_at'], 'forum_answers_expert_status_created_idx');
            $table->index(['author_key', 'status'], 'forum_answers_author_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_answers');
    }
};

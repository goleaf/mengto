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
        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_key', 80);
            $table->string('author_name', 120);
            $table->string('author_initials', 8);
            $table->string('author_role', 120)->nullable();
            $table->string('slug', 180)->unique();
            $table->string('type', 40);
            $table->string('title', 180);
            $table->text('body');
            $table->string('category', 80);
            $table->string('subcategory', 80)->nullable();
            $table->json('tags')->nullable();
            $table->string('pet_key', 80)->nullable();
            $table->string('pet_name', 80)->nullable();
            $table->string('pet_species', 80)->nullable();
            $table->string('pet_age_label', 80)->nullable();
            $table->string('location', 120)->nullable();
            $table->string('status', 40);
            $table->string('visibility', 40);
            $table->string('desired_answer', 80)->nullable();
            $table->string('comment_policy', 40)->default('registered');
            $table->string('language', 12)->default('en');
            $table->json('media')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_medical')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('has_expert_answer')->default(false);
            $table->unsignedBigInteger('accepted_answer_id')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['visibility', 'status', 'last_activity_at', 'id'], 'forum_topics_visibility_status_activity_idx');
            $table->index(['category', 'status', 'last_activity_at', 'id'], 'forum_topics_category_status_activity_idx');
            $table->index(['type', 'status', 'created_at', 'id'], 'forum_topics_type_status_created_idx');
            $table->index(['author_key', 'status'], 'forum_topics_author_status_idx');
            $table->index(['pet_key', 'status'], 'forum_topics_pet_status_idx');
            $table->index(['is_medical', 'status'], 'forum_topics_medical_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_topics');
    }
};

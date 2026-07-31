<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_topic_types', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 120)->unique();
            $table->string('name_translation_key', 190);
            $table->string('description_translation_key', 190);
            $table->unsignedSmallInteger('schema_version')->default(1);
            $table->json('field_schema')->nullable();
            $table->json('configuration')->nullable();
            $table->string('moderation_level', 40)->default('standard');
            $table->boolean('allows_accepted_answers')->default(false);
            $table->boolean('allows_confirmation')->default(false);
            $table->boolean('expires')->default(false);
            $table->boolean('is_system_managed')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('forum_topics', function (Blueprint $table): void {
            $table->foreignId('forum_category_id')
                ->nullable()
                ->after('subcategory')
                ->constrained('forum_categories')
                ->restrictOnDelete();
            $table->foreignId('forum_topic_type_id')
                ->nullable()
                ->after('forum_category_id')
                ->constrained('forum_topic_types')
                ->restrictOnDelete();
            $table->json('structured_data')->nullable();
            $table->unsignedSmallInteger('structured_data_version')
                ->default(1)
                ->after('structured_data');
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('merged_into_topic_id')
                ->nullable()
                ->constrained('forum_topics')
                ->restrictOnDelete();

            $table->index(
                ['forum_category_id', 'status', 'last_activity_at'],
                'forum_topics_normalized_category_status_idx',
            );
            $table->index(
                ['forum_topic_type_id', 'status', 'created_at'],
                'forum_topics_normalized_type_status_idx',
            );
        });

        Schema::create('forum_topic_moves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_topic_id')
                ->constrained('forum_topics')
                ->cascadeOnDelete();
            $table->foreignId('from_forum_category_id')
                ->nullable()
                ->constrained('forum_categories')
                ->nullOnDelete();
            $table->foreignId('to_forum_category_id')
                ->constrained('forum_categories')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('reason_code', 80);
            $table->string('old_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['forum_topic_id', 'created_at'],
                'forum_topic_moves_topic_created_idx',
            );
        });

        Schema::create('forum_topic_acceptances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_topic_id')
                ->constrained('forum_topics')
                ->cascadeOnDelete();
            $table->foreignId('forum_answer_id')
                ->constrained('forum_answers')
                ->cascadeOnDelete();
            $table->foreignId('accepted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('acceptance_type', 40)->default('author');
            $table->boolean('is_active')->default(true);
            $table->timestamp('accepted_at');
            $table->timestamp('invalidated_at')->nullable();
            $table->string('invalidation_reason_code', 80)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['forum_topic_id', 'forum_answer_id', 'acceptance_type'],
                'forum_topic_acceptances_subject_type_unique',
            );
            $table->index(
                ['forum_topic_id', 'is_active', 'accepted_at'],
                'forum_topic_acceptances_active_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_topic_acceptances');
        Schema::dropIfExists('forum_topic_moves');

        Schema::table('forum_topics', function (Blueprint $table): void {
            $table->dropForeign(['merged_into_topic_id']);
            $table->dropForeign(['forum_topic_type_id']);
            $table->dropForeign(['forum_category_id']);
            $table->dropIndex('forum_topics_normalized_type_status_idx');
            $table->dropIndex('forum_topics_normalized_category_status_idx');
            $table->dropColumn([
                'forum_category_id',
                'forum_topic_type_id',
                'structured_data',
                'structured_data_version',
                'lock_version',
                'archived_at',
                'merged_into_topic_id',
            ]);
        });

        Schema::dropIfExists('forum_topic_types');
    }
};

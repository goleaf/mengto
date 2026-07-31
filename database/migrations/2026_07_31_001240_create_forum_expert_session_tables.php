<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_expert_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('expert_profile_id')
                ->constrained('expert_profiles')
                ->restrictOnDelete();
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('creation_idempotency_key', 190)->unique();
            $table->string('host_name_snapshot', 160);
            $table->string('professional_scope', 120);
            $table->string('jurisdiction', 120);
            $table->string('title', 180);
            $table->text('summary');
            $table->string('locale', 12);
            $table->string('timezone', 64)->default('UTC');
            $table->string('status', 30)->default('published');
            $table->string('disclaimer_version', 40)->default('2026-07');
            $table->timestamp('question_opens_at');
            $table->timestamp('question_closes_at');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->foreignId('archived_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->string('archive_reason_code', 120)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(
                ['status', 'starts_at', 'id'],
                'forum_expert_sessions_status_start_idx',
            );
            $table->index(
                ['professional_scope', 'status', 'starts_at', 'id'],
                'forum_expert_sessions_scope_status_start_idx',
            );
            $table->index(
                ['jurisdiction', 'status', 'starts_at', 'id'],
                'forum_expert_sessions_jurisdiction_status_start_idx',
            );
            $table->index(
                ['expert_profile_id', 'status', 'starts_at', 'id'],
                'forum_expert_sessions_profile_status_start_idx',
            );
            $table->index(
                ['created_by_user_id'],
                'forum_expert_sessions_creator_idx',
            );
            $table->index(
                ['archived_by_user_id'],
                'forum_expert_sessions_archiver_idx',
            );
        });

        Schema::create('forum_expert_session_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_expert_session_id')
                ->constrained('forum_expert_sessions')
                ->restrictOnDelete();
            $table->foreignId('author_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->text('body');
            $table->string('status', 30)->default('queued');
            $table->string('moderation_status', 30)->default('pending');
            $table->unsignedInteger('queue_position');
            $table->string('moderation_reason_code', 120)->nullable();
            $table->text('moderation_reason')->nullable();
            $table->timestamp('selected_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(
                ['forum_expert_session_id', 'queue_position'],
                'forum_expert_questions_session_position_unique',
            );
            $table->index(
                ['forum_expert_session_id', 'moderation_status', 'status', 'queue_position'],
                'forum_expert_questions_session_queue_idx',
            );
            $table->index(
                ['author_user_id', 'created_at', 'id'],
                'forum_expert_questions_author_created_idx',
            );
        });

        Schema::create('forum_expert_session_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_expert_session_id')
                ->constrained('forum_expert_sessions')
                ->restrictOnDelete();
            $table->foreignId('forum_expert_session_question_id')
                ->unique()
                ->constrained('forum_expert_session_questions')
                ->restrictOnDelete();
            $table->foreignId('author_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->text('body');
            $table->json('source_links');
            $table->string('status', 30)->default('published');
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamp('answered_at');
            $table->timestamps();

            $table->index(
                ['forum_expert_session_id', 'status', 'answered_at', 'id'],
                'forum_expert_answers_session_status_time_idx',
            );
            $table->index(
                ['author_user_id', 'answered_at', 'id'],
                'forum_expert_answers_author_time_idx',
            );
        });

        Schema::create('forum_expert_session_corrections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_expert_session_id')
                ->constrained('forum_expert_sessions')
                ->restrictOnDelete();
            $table->foreignId('forum_expert_session_answer_id')
                ->constrained('forum_expert_session_answers')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->text('previous_body');
            $table->json('previous_source_links');
            $table->text('corrected_body');
            $table->json('corrected_source_links');
            $table->text('reason');
            $table->timestamp('created_at');

            $table->unique(
                ['forum_expert_session_answer_id', 'version'],
                'forum_expert_corrections_answer_version_unique',
            );
            $table->index(
                ['forum_expert_session_id', 'created_at', 'id'],
                'forum_expert_corrections_session_created_idx',
            );
            $table->index(
                ['actor_user_id'],
                'forum_expert_corrections_actor_idx',
            );
        });

        Schema::create('forum_expert_session_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_expert_session_id')
                ->constrained('forum_expert_sessions')
                ->restrictOnDelete();
            $table->foreignId('forum_expert_session_question_id')
                ->nullable()
                ->constrained('forum_expert_session_questions')
                ->restrictOnDelete();
            $table->foreignId('forum_expert_session_answer_id')
                ->nullable()
                ->constrained('forum_expert_session_answers')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->string('reason_code', 120);
            $table->string('summary_translation_key', 190);
            $table->json('metadata')->nullable();
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->timestamp('created_at');

            $table->index(
                ['forum_expert_session_id', 'created_at', 'id'],
                'forum_expert_history_session_created_idx',
            );
            $table->index(
                ['forum_expert_session_question_id', 'created_at', 'id'],
                'forum_expert_history_question_created_idx',
            );
            $table->index(
                ['forum_expert_session_answer_id', 'created_at', 'id'],
                'forum_expert_history_answer_created_idx',
            );
            $table->index(
                ['actor_user_id'],
                'forum_expert_history_actor_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_expert_session_history');
        Schema::dropIfExists('forum_expert_session_corrections');
        Schema::dropIfExists('forum_expert_session_answers');
        Schema::dropIfExists('forum_expert_session_questions');
        Schema::dropIfExists('forum_expert_sessions');
    }
};

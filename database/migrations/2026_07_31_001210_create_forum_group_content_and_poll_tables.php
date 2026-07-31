<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_topics', function (Blueprint $table): void {
            $table->foreignId('forum_group_id')
                ->nullable()
                ->after('forum_category_id')
                ->constrained('forum_groups')
                ->nullOnDelete();
            $table->index(
                ['forum_group_id', 'status', 'last_activity_at', 'id'],
                'forum_topics_group_status_activity_idx',
            );
        });

        Schema::table('knowledge_articles', function (Blueprint $table): void {
            $table->foreignId('forum_group_id')
                ->nullable()
                ->after('created_by_user_id')
                ->constrained('forum_groups')
                ->nullOnDelete();
            $table->index(
                ['forum_group_id', 'status', 'updated_at', 'id'],
                'knowledge_articles_group_status_updated_idx',
            );
        });

        Schema::create('forum_group_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_group_id')
                ->constrained('forum_groups')
                ->restrictOnDelete();
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('creation_idempotency_key', 190)->unique();
            $table->string('title', 180);
            $table->text('summary');
            $table->string('format', 30);
            $table->string('status', 30)->default('scheduled');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('timezone', 64);
            $table->string('location_scope', 160)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->text('participation_notes')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['forum_group_id', 'status', 'starts_at', 'id'],
                'forum_group_activities_group_schedule_idx',
            );
            $table->index(
                ['status', 'starts_at', 'id'],
                'forum_group_activities_schedule_idx',
            );
            $table->index(
                ['created_by_user_id', 'starts_at', 'id'],
                'forum_group_activities_creator_schedule_idx',
            );
        });

        Schema::create('forum_group_announcements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_group_id')
                ->constrained('forum_groups')
                ->restrictOnDelete();
            $table->foreignId('author_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('publication_idempotency_key', 190)->unique();
            $table->string('title', 180);
            $table->text('body');
            $table->timestamp('published_at');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['forum_group_id', 'archived_at', 'published_at', 'id'],
                'forum_group_announcements_publication_idx',
            );
            $table->index(
                ['expires_at', 'archived_at', 'id'],
                'forum_group_announcements_expiry_idx',
            );
            $table->index(
                ['author_user_id', 'published_at', 'id'],
                'forum_group_announcements_author_publication_idx',
            );
        });

        Schema::create('forum_group_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_group_id')
                ->constrained('forum_groups')
                ->restrictOnDelete();
            $table->foreignId('uploaded_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('upload_idempotency_key', 190)->unique();
            $table->string('disk', 40)->default('local');
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('byte_size');
            $table->string('checksum', 64);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['disk', 'path'], 'forum_group_files_disk_path_unique');
            $table->index(
                ['forum_group_id', 'status', 'created_at', 'id'],
                'forum_group_files_group_status_created_idx',
            );
            $table->index(
                ['checksum', 'forum_group_id'],
                'forum_group_files_checksum_group_idx',
            );
            $table->index(
                ['uploaded_by_user_id', 'created_at', 'id'],
                'forum_group_files_uploader_created_idx',
            );
        });

        Schema::create('forum_polls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_group_id')
                ->constrained('forum_groups')
                ->restrictOnDelete();
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('creation_idempotency_key', 190)->unique();
            $table->string('question', 240);
            $table->text('description')->nullable();
            $table->string('type', 30);
            $table->string('voter_visibility', 30);
            $table->string('result_visibility', 30);
            $table->boolean('is_vote_editable')->default(false);
            $table->string('eligibility', 40);
            $table->string('location_scope', 160)->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamp('closes_at')->nullable();
            $table->unsignedInteger('total_vote_count')->default(0);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['forum_group_id', 'status', 'closes_at', 'id'],
                'forum_polls_group_status_closure_idx',
            );
            $table->index(
                ['status', 'closes_at', 'id'],
                'forum_polls_status_closure_idx',
            );
            $table->index(
                ['created_by_user_id', 'created_at', 'id'],
                'forum_polls_creator_created_idx',
            );
        });

        Schema::create('forum_poll_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_poll_id')
                ->constrained('forum_polls')
                ->cascadeOnDelete();
            $table->string('stable_key', 100);
            $table->string('label', 180);
            $table->unsignedSmallInteger('position');
            $table->unsignedInteger('selection_count')->default(0);
            $table->unsignedInteger('first_choice_count')->default(0);
            $table->timestamps();

            $table->unique(
                ['forum_poll_id', 'stable_key'],
                'forum_poll_options_poll_key_unique',
            );
            $table->unique(
                ['forum_poll_id', 'position'],
                'forum_poll_options_poll_position_unique',
            );
        });

        Schema::create('forum_poll_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_poll_id')
                ->constrained('forum_polls')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->json('choices');
            $table->string('idempotency_key', 190)->unique();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(
                ['forum_poll_id', 'user_id'],
                'forum_poll_votes_poll_user_unique',
            );
            $table->index(
                ['forum_poll_id', 'updated_at', 'id'],
                'forum_poll_votes_poll_updated_idx',
            );
            $table->index(
                ['user_id', 'updated_at', 'id'],
                'forum_poll_votes_user_updated_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_poll_votes');
        Schema::dropIfExists('forum_poll_options');
        Schema::dropIfExists('forum_polls');
        Schema::dropIfExists('forum_group_files');
        Schema::dropIfExists('forum_group_announcements');
        Schema::dropIfExists('forum_group_activities');

        Schema::table('knowledge_articles', function (Blueprint $table): void {
            $table->dropIndex('knowledge_articles_group_status_updated_idx');
            $table->dropConstrainedForeignId('forum_group_id');
        });

        Schema::table('forum_topics', function (Blueprint $table): void {
            $table->dropIndex('forum_topics_group_status_activity_idx');
            $table->dropConstrainedForeignId('forum_group_id');
        });
    }
};

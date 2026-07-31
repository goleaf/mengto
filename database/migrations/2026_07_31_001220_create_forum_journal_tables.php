<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_journals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_topic_id')
                ->unique()
                ->constrained('forum_topics')
                ->restrictOnDelete();
            $table->foreignId('owner_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('owner_key', 80);
            $table->string('stable_key', 190)->unique();
            $table->string('creation_idempotency_key', 190)->unique();
            $table->string('type', 60);
            $table->string('status', 30)->default('active');
            $table->date('started_on');
            $table->string('timezone', 64);
            $table->unsignedInteger('lock_version')->default(0);
            $table->foreignId('archived_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->string('archive_reason_code', 80)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['owner_user_id', 'status', 'updated_at', 'id'],
                'forum_journals_owner_status_updated_idx',
            );
            $table->index(
                ['type', 'status', 'updated_at', 'id'],
                'forum_journals_type_status_updated_idx',
            );
            $table->index(
                ['status', 'updated_at', 'id'],
                'forum_journals_status_updated_idx',
            );
            $table->index(
                ['archived_by_user_id', 'archived_at', 'id'],
                'forum_journals_archiver_archived_idx',
            );
        });

        Schema::create('forum_journal_collaborators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_journal_id')
                ->constrained('forum_journals')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('granted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('role', 30);
            $table->string('state', 30)->default('active');
            $table->timestamp('granted_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['forum_journal_id', 'user_id'],
                'forum_journal_collaborators_journal_user_unique',
            );
            $table->index(
                ['forum_journal_id', 'state', 'role', 'id'],
                'forum_journal_collaborators_journal_state_role_idx',
            );
            $table->index(
                ['user_id', 'state', 'updated_at', 'id'],
                'forum_journal_collaborators_user_state_updated_idx',
            );
            $table->index(
                ['granted_by_user_id', 'created_at', 'id'],
                'forum_journal_collaborators_grantor_created_idx',
            );
        });

        Schema::create('forum_journal_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_journal_id')
                ->constrained('forum_journals')
                ->restrictOnDelete();
            $table->foreignId('author_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('author_key', 80);
            $table->string('author_name', 120);
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->string('kind', 30)->default('entry');
            $table->timestamp('occurred_at');
            $table->string('timezone', 64);
            $table->string('title', 180);
            $table->text('body');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(
                ['forum_journal_id', 'occurred_at', 'id'],
                'forum_journal_entries_journal_occurred_idx',
            );
            $table->index(
                ['author_user_id', 'occurred_at', 'id'],
                'forum_journal_entries_author_occurred_idx',
            );
            $table->index(
                ['kind', 'occurred_at', 'id'],
                'forum_journal_entries_kind_occurred_idx',
            );
        });

        Schema::create('forum_journal_measurements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_journal_entry_id')
                ->constrained('forum_journal_entries')
                ->cascadeOnDelete();
            $table->string('metric_key', 80);
            $table->decimal('numeric_value', 16, 4);
            $table->string('unit', 40);
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();

            $table->unique(
                ['forum_journal_entry_id', 'metric_key'],
                'forum_journal_measurements_entry_metric_unique',
            );
            $table->index(
                ['metric_key', 'created_at', 'id'],
                'forum_journal_measurements_metric_created_idx',
            );
        });

        Schema::create('forum_journal_entry_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_journal_entry_id')
                ->constrained('forum_journal_entries')
                ->restrictOnDelete();
            $table->foreignId('edited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot');
            $table->string('reason_code', 80);
            $table->timestamp('created_at');

            $table->unique(
                ['forum_journal_entry_id', 'version'],
                'forum_journal_entry_versions_entry_version_unique',
            );
            $table->index(
                ['forum_journal_entry_id', 'created_at', 'id'],
                'forum_journal_entry_versions_entry_created_idx',
            );
            $table->index(
                ['edited_by_user_id', 'created_at', 'id'],
                'forum_journal_entry_versions_editor_created_idx',
            );
        });

        Schema::create('forum_journal_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_journal_entry_id')
                ->constrained('forum_journal_entries')
                ->restrictOnDelete();
            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('upload_idempotency_key', 190)->unique();
            $table->string('disk', 40)->default('local');
            $table->string('path', 500);
            $table->text('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('byte_size');
            $table->string('checksum', 64);
            $table->text('alt_text');
            $table->text('caption')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(
                ['disk', 'path'],
                'forum_journal_media_disk_path_unique',
            );
            $table->index(
                ['forum_journal_entry_id', 'status', 'created_at', 'id'],
                'forum_journal_media_entry_status_created_idx',
            );
            $table->index(
                ['uploaded_by_user_id', 'created_at', 'id'],
                'forum_journal_media_uploader_created_idx',
            );
            $table->index(
                ['checksum', 'forum_journal_entry_id'],
                'forum_journal_media_checksum_entry_idx',
            );
        });

        Schema::table('forum_comments', function (Blueprint $table): void {
            $table->foreignId('forum_journal_entry_id')
                ->nullable()
                ->after('answer_id')
                ->constrained('forum_journal_entries')
                ->restrictOnDelete();
            $table->string('idempotency_key', 190)->nullable();
            $table->unique(
                'idempotency_key',
                'forum_comments_idempotency_unique',
            );
            $table->index(
                ['forum_journal_entry_id', 'status', 'created_at', 'id'],
                'forum_comments_journal_entry_status_created_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('forum_comments', function (Blueprint $table): void {
            $table->dropIndex('forum_comments_journal_entry_status_created_idx');
            $table->dropUnique('forum_comments_idempotency_unique');
            $table->dropConstrainedForeignId('forum_journal_entry_id');
            $table->dropColumn('idempotency_key');
        });

        Schema::dropIfExists('forum_journal_media');
        Schema::dropIfExists('forum_journal_entry_versions');
        Schema::dropIfExists('forum_journal_measurements');
        Schema::dropIfExists('forum_journal_entries');
        Schema::dropIfExists('forum_journal_collaborators');
        Schema::dropIfExists('forum_journals');
    }
};

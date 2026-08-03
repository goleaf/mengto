<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_event_registrations', function (Blueprint $table): void {
            $table->dropUnique('forum_event_registrations_event_user_unique');
            $table->foreignId('forum_event_occurrence_id')
                ->nullable()
                ->after('forum_event_id')
                ->constrained('forum_event_occurrences')
                ->restrictOnDelete();
            $table->foreignId('forum_event_version_id')
                ->nullable()
                ->after('forum_event_occurrence_id')
                ->constrained('forum_event_versions')
                ->restrictOnDelete();
            $table->text('accepted_snapshot')->nullable();
            $table->char('accepted_snapshot_checksum', 64)->nullable();
            $table->string('locale', 12)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();

            $table->unique(
                ['forum_event_id', 'forum_event_occurrence_id', 'user_id'],
                'forum_event_registrations_occurrence_user_unique',
            );
            $table->index(
                ['forum_event_occurrence_id', 'status', 'created_at', 'id'],
                'forum_event_registrations_occurrence_state_idx',
            );
            $table->index(
                ['forum_event_version_id', 'created_at', 'id'],
                'forum_event_registrations_version_created_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('forum_event_registrations', function (Blueprint $table): void {
            $table->dropUnique('forum_event_registrations_occurrence_user_unique');
            $table->dropIndex('forum_event_registrations_occurrence_state_idx');
            $table->dropIndex('forum_event_registrations_version_created_idx');
            $table->dropConstrainedForeignId('forum_event_occurrence_id');
            $table->dropConstrainedForeignId('forum_event_version_id');
            $table->dropColumn([
                'accepted_snapshot',
                'accepted_snapshot_checksum',
                'locale',
                'timezone',
                'submitted_at',
                'confirmed_at',
                'checked_out_at',
            ]);
            $table->unique(
                ['forum_event_id', 'user_id'],
                'forum_event_registrations_event_user_unique',
            );
        });
    }
};

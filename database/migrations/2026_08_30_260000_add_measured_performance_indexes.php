<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_entries', function (Blueprint $table): void {
            $table->index(
                ['care_journal_id', 'started_at', 'id'],
                'care_entries_journal_started_id_idx',
            );
        });

        Schema::table('device_readings', function (Blueprint $table): void {
            $table->index(
                ['smart_device_id', 'recorded_at', 'id'],
                'device_readings_device_recorded_id_idx',
            );
        });

        Schema::table('device_events', function (Blueprint $table): void {
            $table->index(
                ['smart_device_id', 'occurred_at', 'id'],
                'device_events_device_occurred_id_idx',
            );
        });

        Schema::table('search_cases', function (Blueprint $table): void {
            $table->index(
                [
                    'moderation_status',
                    'visibility',
                    'archived_at',
                    'last_sighting_at',
                    'last_seen_at',
                    'id',
                ],
                'search_cases_public_recency_id_idx',
            );
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(
                ['target_type', 'target_id', 'created_at', 'id'],
                'audit_logs_target_created_id_idx',
            );
        });

        Schema::table('forum_event_messages', function (Blueprint $table): void {
            $table->index(
                ['forum_event_id', 'id'],
                'forum_event_messages_event_id_idx',
            );
        });

        Schema::table('forum_groups', function (Blueprint $table): void {
            $table->index(
                ['status', 'updated_at', 'id'],
                'forum_groups_status_updated_id_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('forum_groups', function (Blueprint $table): void {
            $table->dropIndex('forum_groups_status_updated_id_idx');
        });

        Schema::table('forum_event_messages', function (Blueprint $table): void {
            $table->dropIndex('forum_event_messages_event_id_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_target_created_id_idx');
        });

        Schema::table('search_cases', function (Blueprint $table): void {
            $table->dropIndex('search_cases_public_recency_id_idx');
        });

        Schema::table('device_events', function (Blueprint $table): void {
            $table->dropIndex('device_events_device_occurred_id_idx');
        });

        Schema::table('device_readings', function (Blueprint $table): void {
            $table->dropIndex('device_readings_device_recorded_id_idx');
        });

        Schema::table('care_entries', function (Blueprint $table): void {
            $table->dropIndex('care_entries_journal_started_id_idx');
        });
    }
};

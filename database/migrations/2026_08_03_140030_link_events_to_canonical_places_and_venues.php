<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_events', function (Blueprint $table): void {
            $table->foreignId('place_id')->nullable()->after('responsible_organization_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('venue_id')->nullable()->after('place_id')->constrained('venues')->restrictOnDelete();
            $table->index(['place_id', 'status', 'starts_at', 'id'], 'forum_events_place_status_starts_idx');
            $table->index(['venue_id', 'status', 'starts_at', 'id'], 'forum_events_venue_status_starts_idx');
        });

        Schema::table('forum_event_occurrences', function (Blueprint $table): void {
            $table->foreignId('place_id')->nullable()->after('forum_event_series_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('venue_id')->nullable()->after('place_id')->constrained('venues')->restrictOnDelete();
            $table->index(['place_id', 'starts_at', 'id'], 'forum_event_occurrences_place_starts_idx');
            $table->index(['venue_id', 'starts_at', 'id'], 'forum_event_occurrences_venue_starts_idx');
        });

        Schema::table('forum_event_rooms', function (Blueprint $table): void {
            $table->foreignId('venue_area_id')->nullable()->after('forum_event_occurrence_id')->constrained('venue_areas')->restrictOnDelete();
            $table->index(['venue_area_id', 'id'], 'forum_event_rooms_venue_area_idx');
        });
    }

    public function down(): void
    {
        Schema::table('forum_event_rooms', function (Blueprint $table): void {
            $table->dropIndex('forum_event_rooms_venue_area_idx');
            $table->dropConstrainedForeignId('venue_area_id');
        });

        Schema::table('forum_event_occurrences', function (Blueprint $table): void {
            $table->dropIndex('forum_event_occurrences_place_starts_idx');
            $table->dropIndex('forum_event_occurrences_venue_starts_idx');
            $table->dropConstrainedForeignId('venue_id');
            $table->dropConstrainedForeignId('place_id');
        });

        Schema::table('forum_events', function (Blueprint $table): void {
            $table->dropIndex('forum_events_place_status_starts_idx');
            $table->dropIndex('forum_events_venue_status_starts_idx');
            $table->dropConstrainedForeignId('venue_id');
            $table->dropConstrainedForeignId('place_id');
        });
    }
};

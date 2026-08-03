<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_event_tracks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->unique(
                ['forum_event_id', 'name'],
                'forum_event_tracks_event_name_unique',
            );
            $table->index(
                ['forum_event_id', 'is_public', 'position', 'id'],
                'forum_event_tracks_event_public_position_idx',
            );
        });

        Schema::create('forum_event_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('name', 120);
            $table->text('public_directions')->nullable();
            $table->text('exact_directions')->nullable();
            $table->text('online_url')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->text('accessibility_information')->nullable();
            $table->boolean('is_online')->default(false);
            $table->boolean('is_private')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(
                ['forum_event_id', 'name'],
                'forum_event_rooms_event_name_unique',
            );
            $table->index(
                ['forum_event_id', 'is_online', 'position', 'id'],
                'forum_event_rooms_event_online_position_idx',
            );
        });

        Schema::create('forum_event_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->foreignId('forum_event_occurrence_id')
                ->constrained('forum_event_occurrences')
                ->restrictOnDelete();
            $table->foreignId('forum_event_track_id')
                ->nullable()
                ->constrained('forum_event_tracks')
                ->restrictOnDelete();
            $table->foreignId('forum_event_room_id')
                ->nullable()
                ->constrained('forum_event_rooms')
                ->restrictOnDelete();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->string('title', 180);
            $table->text('summary')->nullable();
            $table->string('type', 40);
            $table->string('status', 30)->default('scheduled');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('timezone', 64);
            $table->unsignedInteger('capacity')->nullable();
            $table->string('reservation_policy', 30)->default('optional');
            $table->boolean('is_required')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->text('conflict_override_reason')->nullable();
            $table->text('conflict_snapshot')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(
                ['forum_event_id', 'status', 'starts_at', 'id'],
                'forum_event_sessions_event_state_start_idx',
            );
            $table->index(
                ['forum_event_occurrence_id', 'status', 'starts_at', 'ends_at', 'id'],
                'forum_event_sessions_occurrence_state_range_idx',
            );
            $table->index(
                ['forum_event_room_id', 'status', 'starts_at', 'ends_at', 'id'],
                'forum_event_sessions_room_state_range_idx',
            );
            $table->index(
                ['forum_event_track_id', 'starts_at', 'position', 'id'],
                'forum_event_sessions_track_start_position_idx',
            );
            $table->index(
                ['created_by_user_id', 'id'],
                'forum_event_sessions_created_by_idx',
            );
            $table->index(
                ['updated_by_user_id', 'id'],
                'forum_event_sessions_updated_by_idx',
            );
        });

        Schema::create('forum_event_session_staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_session_id')
                ->constrained('forum_event_sessions')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('role', 40);
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->unique(
                ['forum_event_session_id', 'user_id', 'role'],
                'forum_event_session_staff_session_user_role_unique',
            );
            $table->index(
                ['user_id', 'role', 'forum_event_session_id'],
                'forum_event_session_staff_user_role_session_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_event_session_staff');
        Schema::dropIfExists('forum_event_sessions');
        Schema::dropIfExists('forum_event_rooms');
        Schema::dropIfExists('forum_event_tracks');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_event_series', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('name', 180);
            $table->string('frequency', 40);
            $table->unsignedSmallInteger('interval')->default(1);
            $table->json('weekdays')->nullable();
            $table->string('timezone', 64);
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->unsignedInteger('maximum_occurrences')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(
                ['owner_user_id', 'is_active', 'starts_on', 'id'],
                'forum_event_series_owner_active_start_idx',
            );
            $table->index(
                ['is_active', 'starts_on', 'ends_on', 'id'],
                'forum_event_series_active_range_idx',
            );
        });

        Schema::create('forum_event_occurrences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->foreignId('forum_event_series_id')
                ->nullable()
                ->constrained('forum_event_series')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('status', 40)->default('scheduled');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('timezone', 64);
            $table->string('format', 30);
            $table->unsignedInteger('capacity')->nullable();
            $table->string('location_scope', 190)->nullable();
            $table->text('exact_location')->nullable();
            $table->text('online_url')->nullable();
            $table->boolean('is_override')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason_code', 100)->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['forum_event_id', 'status', 'starts_at', 'id'],
                'forum_event_occurrences_event_state_start_idx',
            );
            $table->index(
                ['forum_event_series_id', 'status', 'starts_at', 'id'],
                'forum_event_occurrences_series_state_start_idx',
            );
            $table->index(
                ['status', 'starts_at', 'ends_at', 'id'],
                'forum_event_occurrences_state_range_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_event_occurrences');
        Schema::dropIfExists('forum_event_series');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_event_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('kind', 40)->default('draft');
            $table->string('reason_code', 100);
            $table->text('snapshot');
            $table->char('snapshot_checksum', 64);
            $table->json('material_fields')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at');

            $table->unique(
                ['forum_event_id', 'version_number'],
                'forum_event_versions_event_number_unique',
            );
            $table->index(
                ['forum_event_id', 'published_at', 'id'],
                'forum_event_versions_event_published_idx',
            );
            $table->index(
                'created_by_user_id',
                'forum_event_versions_creator_idx',
            );
        });

        Schema::create('forum_event_team_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('role', 40);
            $table->string('status', 30)->default('invited');
            $table->json('permission_overrides')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['forum_event_id', 'user_id', 'role'],
                'forum_event_team_event_user_role_unique',
            );
            $table->index(
                ['user_id', 'status', 'starts_at', 'ends_at', 'id'],
                'forum_event_team_user_state_range_idx',
            );
            $table->index(
                ['forum_event_id', 'status', 'role', 'id'],
                'forum_event_team_event_state_role_idx',
            );
            $table->index(
                'invited_by_user_id',
                'forum_event_team_inviter_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_event_team_memberships');
        Schema::dropIfExists('forum_event_versions');
    }
};

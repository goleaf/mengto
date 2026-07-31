<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('creation_idempotency_key', 190)->unique();
            $table->boolean('is_system_managed')->default(false);
            $table->string('name', 160);
            $table->string('name_translation_key', 190)->nullable();
            $table->text('description');
            $table->string('description_translation_key', 190)->nullable();
            $table->json('rules');
            $table->string('visibility', 30);
            $table->string('status', 30)->default('active');
            $table->string('default_locale', 20);
            $table->string('location_scope', 160)->nullable();
            $table->json('membership_questions')->nullable();
            $table->unsignedInteger('active_member_count')->default(1);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['status', 'visibility', 'updated_at', 'id'],
                'forum_groups_discovery_idx',
            );
            $table->index(
                ['owner_user_id', 'status', 'id'],
                'forum_groups_owner_status_idx',
            );
            $table->index(
                ['location_scope', 'status', 'visibility', 'id'],
                'forum_groups_location_discovery_idx',
            );
        });

        Schema::create('forum_group_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_group_id')
                ->constrained('forum_groups')
                ->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('role', 40)->default('member');
            $table->string('state', 30);
            $table->string('notification_level', 30)->default('important');
            $table->json('answers')->nullable();
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('review_reason')->nullable();
            $table->text('restriction_reason')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->string('last_idempotency_key', 190)->nullable()->unique();
            $table->timestamps();

            $table->unique(
                ['forum_group_id', 'user_id'],
                'forum_group_memberships_group_user_unique',
            );
            $table->index(
                ['forum_group_id', 'state', 'role', 'id'],
                'forum_group_memberships_group_state_role_idx',
            );
            $table->index(
                ['user_id', 'state', 'updated_at', 'id'],
                'forum_group_memberships_user_state_idx',
            );
            $table->index(
                'reviewed_by_user_id',
                'forum_group_memberships_reviewer_fk_idx',
            );
        });

        Schema::create('forum_group_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_group_id')
                ->constrained('forum_groups')
                ->restrictOnDelete();
            $table->foreignId('invited_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('role', 40)->default('member');
            $table->string('state', 30)->default('pending');
            $table->text('message')->nullable();
            $table->string('open_key', 190)->nullable()->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(
                ['forum_group_id', 'state', 'expires_at', 'id'],
                'forum_group_invitations_group_state_idx',
            );
            $table->index(
                ['invited_user_id', 'state', 'expires_at', 'id'],
                'forum_group_invitations_recipient_state_idx',
            );
            $table->index(
                'invited_by_user_id',
                'forum_group_invitations_inviter_fk_idx',
            );
        });

        Schema::create('forum_group_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_group_id')
                ->constrained('forum_groups')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('subject_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('reason_code', 120);
            $table->string('summary_translation_key', 190);
            $table->json('metadata')->nullable();
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->timestamp('created_at');

            $table->index(
                ['forum_group_id', 'created_at', 'id'],
                'forum_group_events_history_idx',
            );
            $table->index(
                ['actor_user_id', 'event_type', 'created_at'],
                'forum_group_events_actor_type_idx',
            );
            $table->index('subject_user_id', 'forum_group_events_subject_fk_idx');
        });

        Schema::create('forum_group_taxon', function (Blueprint $table): void {
            $table->foreignId('forum_group_id')
                ->constrained('forum_groups')
                ->cascadeOnDelete();
            $table->foreignId('taxon_id')->constrained('taxa')->restrictOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(
                ['forum_group_id', 'taxon_id'],
                'forum_group_taxon_primary',
            );
            $table->index(
                ['taxon_id', 'forum_group_id'],
                'forum_group_taxon_taxon_group_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_group_taxon');
        Schema::dropIfExists('forum_group_events');
        Schema::dropIfExists('forum_group_invitations');
        Schema::dropIfExists('forum_group_memberships');
        Schema::dropIfExists('forum_groups');
    }
};

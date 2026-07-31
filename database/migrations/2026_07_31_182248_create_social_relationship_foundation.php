<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_actors', function (Blueprint $table): void {
            $table->id();
            $table->uuid('actor_key')->unique();
            $table->string('actor_type', 32);
            $table->string('status', 24)->default('active');
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('pet_profile_id')
                ->nullable()
                ->unique()
                ->constrained('pet_profiles')
                ->nullOnDelete();
            $table->foreignId('expert_profile_id')
                ->nullable()
                ->unique()
                ->constrained('expert_profiles')
                ->nullOnDelete();
            $table->foreignId('forum_group_id')
                ->nullable()
                ->unique()
                ->constrained('forum_groups')
                ->nullOnDelete();
            $table->boolean('is_discoverable')->default(true);
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('detached_at')->nullable();
            $table->timestamps();

            $table->index(
                ['actor_type', 'status', 'is_discoverable', 'id'],
                'social_actors_directory_idx',
            );
        });

        Schema::create('social_actor_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('social_actor_id')
                ->unique()
                ->constrained('social_actors')
                ->cascadeOnDelete();
            $table->string('friend_request_policy', 32)->default('everyone');
            $table->string('follow_policy', 24)->default('public');
            $table->string('friend_list_visibility', 24)->default('friends');
            $table->string('follower_list_visibility', 24)->default('count-only');
            $table->boolean('is_recommendable')->default(true);
            $table->boolean('allow_message_requests')->default(true);
            $table->unsignedInteger('lock_version')->default(1);
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(
                ['is_recommendable', 'follow_policy', 'social_actor_id'],
                'social_actor_settings_recommendation_idx',
            );
            $table->index('updated_by_user_id', 'social_actor_settings_updater_idx');
        });

        Schema::create('social_relationship_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('request_key')->unique();
            $table->foreignId('source_actor_id')->constrained('social_actors')->restrictOnDelete();
            $table->foreignId('target_actor_id')->constrained('social_actors')->restrictOnDelete();
            $table->string('relationship_type', 40);
            $table->string('direction', 16);
            $table->string('status', 24)->default('pending');
            $table->string('active_key', 190)->nullable()->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('context_type', 48)->nullable();
            $table->string('context_key', 190)->nullable();
            $table->text('message')->nullable();
            $table->string('reason_code', 100)->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('repeat_after')->nullable();
            $table->timestamps();

            $table->index(
                ['target_actor_id', 'status', 'expires_at', 'id'],
                'social_requests_inbox_idx',
            );
            $table->index(
                ['source_actor_id', 'status', 'id'],
                'social_requests_outbox_idx',
            );
            $table->index(
                ['relationship_type', 'status', 'created_at', 'id'],
                'social_requests_type_status_idx',
            );
            $table->index('created_by_user_id', 'social_requests_creator_idx');
            $table->index('decided_by_user_id', 'social_requests_decider_idx');
        });

        Schema::create('social_relationships', function (Blueprint $table): void {
            $table->id();
            $table->uuid('relationship_key')->unique();
            $table->foreignId('source_actor_id')->constrained('social_actors')->restrictOnDelete();
            $table->foreignId('target_actor_id')->constrained('social_actors')->restrictOnDelete();
            $table->foreignId('request_id')
                ->nullable()
                ->constrained('social_relationship_requests')
                ->nullOnDelete();
            $table->string('relationship_type', 40);
            $table->string('direction', 16);
            $table->string('status', 24)->default('active');
            $table->string('active_key', 190)->nullable()->unique();
            $table->string('visibility', 24)->default('private');
            $table->json('rights')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('context_type', 48)->nullable();
            $table->string('context_key', 190)->nullable();
            $table->string('reason_code', 100)->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamp('started_at');
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(
                ['source_actor_id', 'relationship_type', 'status', 'id'],
                'social_relationships_source_list_idx',
            );
            $table->index(
                ['target_actor_id', 'relationship_type', 'status', 'id'],
                'social_relationships_target_list_idx',
            );
            $table->index(
                ['status', 'ends_at', 'id'],
                'social_relationships_expiry_idx',
            );
            $table->index('created_by_user_id', 'social_relationships_creator_idx');
            $table->index('accepted_by_user_id', 'social_relationships_acceptor_idx');
            $table->index('request_id', 'social_relationships_request_idx');
        });

        Schema::create('social_relationship_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('social_relationship_id')
                ->nullable()
                ->constrained('social_relationships')
                ->nullOnDelete();
            $table->foreignId('social_relationship_request_id')
                ->nullable()
                ->constrained('social_relationship_requests')
                ->nullOnDelete();
            $table->foreignId('source_actor_id')->constrained('social_actors')->restrictOnDelete();
            $table->foreignId('target_actor_id')->constrained('social_actors')->restrictOnDelete();
            $table->foreignId('represented_actor_id')
                ->nullable()
                ->constrained('social_actors')
                ->nullOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_key_snapshot', 120);
            $table->string('event_type', 60);
            $table->string('relationship_type', 40);
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24)->nullable();
            $table->string('reason_code', 100)->nullable();
            $table->string('idempotency_key', 190)->unique();
            $table->json('public_metadata')->nullable();
            $table->text('private_metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(
                ['source_actor_id', 'occurred_at', 'id'],
                'social_events_source_time_idx',
            );
            $table->index(
                ['target_actor_id', 'occurred_at', 'id'],
                'social_events_target_time_idx',
            );
            $table->index('social_relationship_id', 'social_events_relationship_idx');
            $table->index(
                'social_relationship_request_id',
                'social_events_request_idx',
            );
            $table->index('represented_actor_id', 'social_events_represented_actor_idx');
            $table->index(
                ['event_type', 'occurred_at', 'id'],
                'social_events_type_time_idx',
            );
            $table->index('actor_user_id', 'social_events_actor_user_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_relationship_events');
        Schema::dropIfExists('social_relationships');
        Schema::dropIfExists('social_relationship_requests');
        Schema::dropIfExists('social_actor_settings');
        Schema::dropIfExists('social_actors');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_mentor_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('state', 30)->default('paused');
            $table->string('headline', 160);
            $table->text('summary');
            $table->json('languages');
            $table->string('location_scope', 160)->nullable();
            $table->string('timezone', 80);
            $table->json('communication_preferences');
            $table->json('availability')->nullable();
            $table->unsignedSmallInteger('capacity')->default(2);
            $table->boolean('is_public')->default(true);
            $table->timestamp('safety_acknowledged_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(
                ['state', 'is_public', 'capacity', 'id'],
                'forum_mentor_profiles_discovery_idx',
            );
            $table->index(
                ['location_scope', 'state', 'id'],
                'forum_mentor_profiles_location_idx',
            );
        });

        Schema::create('forum_mentor_scopes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_mentor_profile_id')
                ->constrained('forum_mentor_profiles')
                ->cascadeOnDelete();
            $table->string('mentorship_type', 80);
            $table->foreignId('forum_category_id')
                ->nullable()
                ->constrained('forum_categories')
                ->nullOnDelete();
            $table->foreignId('taxon_id')
                ->nullable()
                ->constrained('taxa')
                ->nullOnDelete();
            $table->text('experience_summary');
            $table->boolean('requires_verified_expertise')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('scope_key', 190)->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['mentorship_type', 'is_active', 'id'],
                'forum_mentor_scopes_type_active_idx',
            );
            $table->index(
                ['forum_mentor_profile_id', 'is_active', 'id'],
                'forum_mentor_scopes_profile_active_idx',
            );
            $table->index('forum_category_id', 'forum_mentor_scopes_category_fk_idx');
            $table->index('taxon_id', 'forum_mentor_scopes_taxon_fk_idx');
        });

        Schema::create('forum_mentorships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_mentor_scope_id')
                ->constrained('forum_mentor_scopes')
                ->restrictOnDelete();
            $table->foreignId('mentor_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('mentee_user_id')->constrained('users')->restrictOnDelete();
            $table->string('mentorship_type', 80);
            $table->string('state', 30)->default('requested');
            $table->string('language', 20);
            $table->string('location_scope', 160)->nullable();
            $table->string('communication_preference', 50);
            $table->text('request_message');
            $table->text('mentor_response')->nullable();
            $table->timestamp('mentee_safety_acknowledged_at');
            $table->timestamp('mentor_safety_acknowledged_at')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('completion_validated_at')->nullable();
            $table->foreignId('validated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ended_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('end_reason')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->string('open_key', 190)->nullable()->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['mentor_user_id', 'state', 'updated_at', 'id'],
                'forum_mentorships_mentor_state_idx',
            );
            $table->index(
                ['mentee_user_id', 'state', 'updated_at', 'id'],
                'forum_mentorships_mentee_state_idx',
            );
            $table->index(
                ['mentorship_type', 'state', 'id'],
                'forum_mentorships_type_state_idx',
            );
            $table->index(
                'forum_mentor_scope_id',
                'forum_mentorships_scope_fk_idx',
            );
            $table->index('validated_by_user_id', 'forum_mentorships_validator_fk_idx');
            $table->index('ended_by_user_id', 'forum_mentorships_ended_by_fk_idx');
        });

        Schema::create('forum_mentorship_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_mentorship_id')
                ->constrained('forum_mentorships')
                ->restrictOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->restrictOnDelete();
            $table->text('body');
            $table->string('idempotency_key', 190)->unique();
            $table->timestamp('created_at');

            $table->index(
                ['forum_mentorship_id', 'created_at', 'id'],
                'forum_mentorship_messages_thread_idx',
            );
            $table->index('sender_user_id', 'forum_mentorship_messages_sender_fk_idx');
        });

        Schema::create('forum_mentorship_feedback', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_mentorship_id')
                ->constrained('forum_mentorships')
                ->restrictOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('summary');
            $table->boolean('would_recommend')->nullable();
            $table->text('private_note')->nullable();
            $table->timestamp('created_at');

            $table->unique(
                ['forum_mentorship_id', 'author_user_id'],
                'forum_mentorship_feedback_author_unique',
            );
            $table->index('author_user_id', 'forum_mentorship_feedback_author_fk_idx');
            $table->index('recipient_user_id', 'forum_mentorship_feedback_recipient_fk_idx');
        });

        Schema::create('forum_mentorship_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_mentorship_id')
                ->constrained('forum_mentorships')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('from_state', 30)->nullable();
            $table->string('to_state', 30)->nullable();
            $table->string('reason_code', 120);
            $table->string('summary_translation_key', 190);
            $table->json('metadata')->nullable();
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->timestamp('created_at');

            $table->index(
                ['forum_mentorship_id', 'created_at', 'id'],
                'forum_mentorship_events_history_idx',
            );
            $table->index(
                ['actor_user_id', 'event_type', 'created_at'],
                'forum_mentorship_events_actor_type_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_mentorship_events');
        Schema::dropIfExists('forum_mentorship_feedback');
        Schema::dropIfExists('forum_mentorship_messages');
        Schema::dropIfExists('forum_mentorships');
        Schema::dropIfExists('forum_mentor_scopes');
        Schema::dropIfExists('forum_mentor_profiles');
    }
};

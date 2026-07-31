<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organizer_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('organizer_key', 80);
            $table->string('organizer_name', 120);
            $table->foreignId('forum_group_id')
                ->nullable()
                ->constrained('forum_groups')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('creation_idempotency_key', 190)->unique();
            $table->boolean('is_system_managed')->default(false);
            $table->string('legacy_source_key', 190)->nullable()->unique();
            $table->string('title', 180);
            $table->text('summary');
            $table->string('type', 60);
            $table->string('visibility', 30)->default('public');
            $table->string('format', 30);
            $table->string('status', 30)->default('scheduled');
            $table->string('locale', 12);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('timezone', 64);
            $table->unsignedInteger('capacity')->nullable();
            $table->string('registration_policy', 30)->default('open');
            $table->boolean('waitlist_enabled')->default(true);
            $table->string('location_scope', 190)->nullable();
            $table->text('exact_location')->nullable();
            $table->text('online_url')->nullable();
            $table->text('attendance_requirements')->nullable();
            $table->text('vaccination_requirements')->nullable();
            $table->string('vaccination_jurisdiction', 120)->nullable();
            $table->unsignedSmallInteger('minimum_animal_age_months')->nullable();
            $table->unsignedSmallInteger('maximum_animal_age_months')->nullable();
            $table->text('accessibility_information')->nullable();
            $table->unsignedBigInteger('cost_minor')->default(0);
            $table->char('currency', 3)->default('EUR');
            $table->text('refund_policy')->nullable();
            $table->string('photo_consent_mode', 30)->default('ask_first');
            $table->text('animal_welfare_rules');
            $table->text('emergency_contact_plan');
            $table->unsignedInteger('lock_version')->default(0);
            $table->foreignId('cancelled_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason_code', 100)->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['visibility', 'status', 'starts_at', 'id'],
                'forum_events_visibility_status_start_idx',
            );
            $table->index(
                ['organizer_user_id', 'status', 'starts_at', 'id'],
                'forum_events_organizer_status_start_idx',
            );
            $table->index(
                ['forum_group_id', 'status', 'starts_at', 'id'],
                'forum_events_group_status_start_idx',
            );
            $table->index(
                ['type', 'status', 'starts_at', 'id'],
                'forum_events_type_status_start_idx',
            );
            $table->index(
                ['format', 'status', 'starts_at', 'id'],
                'forum_events_format_status_start_idx',
            );
            $table->index(
                ['location_scope', 'status', 'starts_at', 'id'],
                'forum_events_location_status_start_idx',
            );
            $table->index(
                ['cancelled_by_user_id', 'cancelled_at', 'id'],
                'forum_events_canceller_time_idx',
            );
        });

        Schema::create('forum_event_taxon', function (Blueprint $table): void {
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->cascadeOnDelete();
            $table->foreignId('taxon_id')
                ->constrained('taxa')
                ->restrictOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->primary(
                ['forum_event_id', 'taxon_id'],
                'forum_event_taxon_primary',
            );
            $table->index(
                ['taxon_id', 'forum_event_id'],
                'forum_event_taxon_taxon_event_idx',
            );
        });

        Schema::create('forum_event_registrations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('pet_profile_id')
                ->nullable()
                ->constrained('pet_profiles')
                ->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->string('status', 30);
            $table->string('attendance_format', 30);
            $table->unsignedSmallInteger('guest_count')->default(0);
            $table->text('requirements_note')->nullable();
            $table->string('photo_consent', 30)->default('ask_first');
            $table->boolean('requirements_accepted')->default(false);
            $table->unsignedInteger('waitlist_position')->nullable();
            $table->string('check_in_method', 30)->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason_code', 100)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(
                ['forum_event_id', 'user_id'],
                'forum_event_registrations_event_user_unique',
            );
            $table->unique(
                ['forum_event_id', 'waitlist_position'],
                'forum_event_registrations_waitlist_unique',
            );
            $table->index(
                ['forum_event_id', 'status', 'waitlist_position', 'id'],
                'forum_event_registrations_event_state_wait_idx',
            );
            $table->index(
                ['user_id', 'status', 'created_at', 'id'],
                'forum_event_registrations_user_state_created_idx',
            );
            $table->index(
                ['forum_event_id', 'checked_in_at', 'id'],
                'forum_event_registrations_checkin_idx',
            );
            $table->index(
                ['pet_profile_id', 'forum_event_id', 'id'],
                'forum_event_registrations_pet_event_idx',
            );
        });

        Schema::create('forum_event_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('invited_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->string('status', 30)->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['forum_event_id', 'invited_user_id'],
                'forum_event_invitations_event_user_unique',
            );
            $table->index(
                ['invited_user_id', 'status', 'expires_at', 'id'],
                'forum_event_invitations_user_state_expiry_idx',
            );
            $table->index(
                ['forum_event_id', 'status', 'expires_at', 'id'],
                'forum_event_invitations_event_state_expiry_idx',
            );
            $table->index(
                ['invited_by_user_id', 'created_at', 'id'],
                'forum_event_invitations_inviter_created_idx',
            );
        });

        Schema::create('forum_event_updates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->foreignId('author_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->string('type', 30)->default('general');
            $table->string('audience', 30)->default('public');
            $table->string('title', 180);
            $table->text('body');
            $table->timestamp('published_at');
            $table->timestamps();

            $table->index(
                ['forum_event_id', 'audience', 'published_at', 'id'],
                'forum_event_updates_event_audience_time_idx',
            );
            $table->index(
                ['author_user_id', 'published_at', 'id'],
                'forum_event_updates_author_time_idx',
            );
        });

        Schema::create('forum_event_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->foreignId('sender_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->string('audience', 30)->default('attendees');
            $table->text('body');
            $table->timestamps();

            $table->index(
                ['forum_event_id', 'created_at', 'id'],
                'forum_event_messages_event_created_idx',
            );
            $table->index(
                ['sender_user_id', 'created_at', 'id'],
                'forum_event_messages_sender_created_idx',
            );
        });

        Schema::create('forum_event_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
                ->restrictOnDelete();
            $table->foreignId('reviewer_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->unsignedTinyInteger('rating');
            $table->string('title', 180);
            $table->text('body');
            $table->string('status', 30)->default('published');
            $table->timestamps();

            $table->unique(
                ['forum_event_id', 'reviewer_user_id'],
                'forum_event_reviews_event_reviewer_unique',
            );
            $table->index(
                ['forum_event_id', 'status', 'created_at', 'id'],
                'forum_event_reviews_event_state_created_idx',
            );
            $table->index(
                ['reviewer_user_id', 'created_at', 'id'],
                'forum_event_reviews_reviewer_created_idx',
            );
        });

        Schema::create('forum_event_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')
                ->constrained('forum_events')
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
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->string('reason_code', 120);
            $table->string('summary_translation_key', 190);
            $table->json('metadata')->nullable();
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->timestamp('created_at');

            $table->index(
                ['forum_event_id', 'created_at', 'id'],
                'forum_event_history_event_created_idx',
            );
            $table->index(
                ['actor_user_id', 'event_type', 'created_at', 'id'],
                'forum_event_history_actor_type_idx',
            );
            $table->index(
                ['subject_user_id', 'created_at', 'id'],
                'forum_event_history_subject_created_idx',
            );
        });

        Schema::table('forum_group_activities', function (Blueprint $table): void {
            $table->foreignId('forum_event_id')
                ->nullable()
                ->after('forum_group_id')
                ->unique()
                ->constrained('forum_events')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('forum_group_activities', function (Blueprint $table): void {
            $table->dropUnique(['forum_event_id']);
        });

        Schema::table('forum_group_activities', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('forum_event_id');
        });

        Schema::dropIfExists('forum_event_history');
        Schema::dropIfExists('forum_event_reviews');
        Schema::dropIfExists('forum_event_messages');
        Schema::dropIfExists('forum_event_updates');
        Schema::dropIfExists('forum_event_invitations');
        Schema::dropIfExists('forum_event_registrations');
        Schema::dropIfExists('forum_event_taxon');
        Schema::dropIfExists('forum_events');
    }
};

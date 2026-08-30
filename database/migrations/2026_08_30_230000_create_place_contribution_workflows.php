<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_corrections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('submitter_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('applied_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stable_key', 96)->unique();
            $table->string('idempotency_key', 96);
            $table->enum('correction_field', [
                'name',
                'summary',
                'public_address',
                'public_phone',
                'public_website',
                'public_email',
                'pet_rules',
            ]);
            $table->text('original_value')->nullable();
            $table->unsignedInteger('original_version');
            $table->text('proposed_value')->nullable();
            $table->text('explanation');
            $table->text('evidence')->nullable();
            $table->enum('source', [
                'personal_observation',
                'place_manager',
                'public_source',
                'official_source',
                'other',
                'legacy_import',
            ])->default('personal_observation');
            $table->timestamp('observed_at')->nullable();
            $table->enum('moderation_status', [
                'pending',
                'in_review',
                'needs_information',
                'accepted',
                'partially_accepted',
                'rejected',
                'withdrawn',
                'superseded',
            ])->default('pending');
            $table->enum('resolution', [
                'applied',
                'partially_applied',
                'not_applied',
                'stale_conflict',
                'withdrawn',
                'superseded',
            ])->nullable();
            $table->text('decision_reason')->nullable();
            $table->text('applied_value')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->string('pending_fingerprint', 64)->nullable()->unique();
            $table->timestamps();

            $table->unique(
                ['submitter_user_id', 'idempotency_key'],
                'place_corrections_submitter_idempotency_unique',
            );
            $table->index(
                ['place_id', 'moderation_status', 'created_at', 'id'],
                'place_corrections_place_status_created_idx',
            );
        });

        Schema::create('place_correction_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_correction_id')->constrained('place_corrections')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96)->nullable();
            $table->string('event_type', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->string('public_summary_key', 190)->nullable();
            $table->text('private_note')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['place_correction_id', 'created_at', 'id'],
                'place_correction_events_correction_created_idx',
            );
            $table->unique(['actor_user_id', 'idempotency_key'], 'place_correction_events_actor_idempotency_unique');
        });

        Schema::create('place_warnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('moderator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stable_key', 96)->unique();
            $table->string('idempotency_key', 96);
            $table->enum('category', [
                'access',
                'animal_health',
                'closure',
                'contamination',
                'crowding',
                'hazard',
                'other',
            ]);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->string('affected_scope', 240);
            $table->enum('source', [
                'community',
                'emergency_service',
                'manager',
                'official',
                'personal_observation',
                'other',
                'legacy_import',
            ]);
            $table->string('title', 180);
            $table->text('detail');
            $table->text('evidence')->nullable();
            $table->enum('status', [
                'needs_review',
                'published',
                'disputed',
                'resolved',
                'expired',
                'rejected',
                'removed',
            ])->default('needs_review');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('disputed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->enum('resolution', [
                'condition_ended',
                'corrected',
                'false_report',
                'insufficient_evidence',
                'expired',
                'removed',
            ])->nullable();
            $table->text('moderation_reason')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->unique(
                ['author_user_id', 'idempotency_key'],
                'place_warnings_author_idempotency_unique',
            );
            $table->index(
                ['place_id', 'status', 'expires_at', 'id'],
                'place_warnings_place_status_expiry_idx',
            );
            $table->index(
                ['severity', 'status', 'created_at', 'id'],
                'place_warnings_severity_status_created_idx',
            );
        });

        Schema::create('place_warning_confirmations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_warning_id')->constrained('place_warnings')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('idempotency_key', 96);
            $table->timestamp('confirmed_at');
            $table->timestamps();

            $table->unique(
                ['place_warning_id', 'user_id'],
                'place_warning_confirmations_warning_user_unique',
            );
            $table->unique(
                ['user_id', 'idempotency_key'],
                'place_warning_confirmations_user_idempotency_unique',
            );
        });

        Schema::create('place_warning_disputes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_warning_id')->constrained('place_warnings')->restrictOnDelete();
            $table->foreignId('disputant_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96);
            $table->text('reason');
            $table->text('evidence')->nullable();
            $table->enum('status', ['submitted', 'in_review', 'upheld', 'rejected', 'withdrawn'])
                ->default('submitted');
            $table->text('decision_reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['disputant_user_id', 'idempotency_key'],
                'place_warning_disputes_user_idempotency_unique',
            );
            $table->index(
                ['place_warning_id', 'status', 'created_at', 'id'],
                'place_warning_disputes_warning_status_idx',
            );
        });

        Schema::create('place_warning_appeals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_warning_id')->constrained('place_warnings')->restrictOnDelete();
            $table->foreignId('appellant_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96);
            $table->enum('status', ['submitted', 'in_review', 'upheld', 'denied', 'withdrawn'])
                ->default('submitted');
            $table->text('reason');
            $table->text('evidence')->nullable();
            $table->text('decision_reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['appellant_user_id', 'idempotency_key'],
                'place_warning_appeals_user_idempotency_unique',
            );
            $table->index(
                ['place_warning_id', 'status', 'created_at', 'id'],
                'place_warning_appeals_warning_status_idx',
            );
        });

        Schema::create('place_warning_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_warning_id')->constrained('place_warnings')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96)->nullable();
            $table->string('event_type', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->string('public_summary_key', 190)->nullable();
            $table->text('private_note')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['place_warning_id', 'created_at', 'id'],
                'place_warning_events_warning_created_idx',
            );
            $table->unique(['actor_user_id', 'idempotency_key'], 'place_warning_events_actor_idempotency_unique');
        });

        Schema::create('place_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('pet_profile_id')->nullable()->constrained('pet_profiles')->nullOnDelete();
            $table->foreignId('moderator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stable_key', 96)->unique();
            $table->string('idempotency_key', 96);
            $table->enum('eligibility_context', ['visit', 'service', 'event', 'other']);
            $table->boolean('verified_visit')->default(false);
            $table->unsignedTinyInteger('rating_overall');
            $table->unsignedTinyInteger('rating_service')->nullable();
            $table->unsignedTinyInteger('rating_accessibility')->nullable();
            $table->unsignedTinyInteger('rating_pet_friendliness')->nullable();
            $table->text('body');
            $table->enum('anonymity_mode', ['named', 'anonymous'])->default('named');
            $table->enum('moderation_status', ['pending', 'published', 'hidden', 'removed'])
                ->default('published');
            $table->unsignedInteger('current_version')->default(1);
            $table->text('moderation_reason')->nullable();
            $table->text('deletion_reason')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['place_id', 'author_user_id'],
                'place_reviews_place_author_unique',
            );
            $table->unique(
                ['author_user_id', 'idempotency_key'],
                'place_reviews_author_idempotency_unique',
            );
            $table->index(
                ['place_id', 'moderation_status', 'deleted_at', 'created_at', 'id'],
                'place_reviews_place_visibility_idx',
            );
        });

        Schema::create('place_review_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_review_id')->constrained('place_reviews')->restrictOnDelete();
            $table->foreignId('editor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96)->nullable();
            $table->unsignedInteger('version');
            $table->unsignedTinyInteger('rating_overall');
            $table->unsignedTinyInteger('rating_service')->nullable();
            $table->unsignedTinyInteger('rating_accessibility')->nullable();
            $table->unsignedTinyInteger('rating_pet_friendliness')->nullable();
            $table->text('body');
            $table->string('anonymity_mode', 24);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['place_review_id', 'version'],
                'place_review_versions_review_version_unique',
            );
            $table->unique(['editor_user_id', 'idempotency_key'], 'place_review_versions_editor_idempotency_unique');
        });

        Schema::create('place_review_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_review_id')->constrained('place_reviews')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96)->nullable();
            $table->string('event_type', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->string('public_summary_key', 190)->nullable();
            $table->text('private_note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['place_review_id', 'created_at', 'id'],
                'place_review_events_review_created_idx',
            );
            $table->unique(['actor_user_id', 'idempotency_key'], 'place_review_events_actor_idempotency_unique');
        });

        Schema::create('place_review_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_review_id')->unique()->constrained('place_reviews')->restrictOnDelete();
            $table->foreignId('author_user_id')->constrained('users')->restrictOnDelete();
            $table->string('stable_key', 96)->unique();
            $table->string('idempotency_key', 96);
            $table->text('body');
            $table->unsignedInteger('current_version')->default(1);
            $table->timestamps();

            $table->unique(
                ['author_user_id', 'idempotency_key'],
                'place_review_responses_author_idempotency_unique',
            );
        });

        Schema::create('place_review_response_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_review_response_id')->constrained('place_review_responses')->restrictOnDelete();
            $table->foreignId('editor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96)->nullable();
            $table->unsignedInteger('version');
            $table->text('body');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['place_review_response_id', 'version'],
                'place_review_response_versions_response_version_unique',
            );
            $table->unique(['editor_user_id', 'idempotency_key'], 'place_response_versions_editor_idempotency_unique');
        });

        Schema::table('place_questions', function (Blueprint $table): void {
            $table->enum('moderation_status', ['pending', 'approved', 'hidden', 'removed'])
                ->default('approved')
                ->after('status');
            $table->foreignId('duplicate_question_id')
                ->nullable()
                ->after('moderation_status')
                ->constrained('place_questions')
                ->nullOnDelete();
            $table->foreignId('closed_by_user_id')
                ->nullable()
                ->after('duplicate_question_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('closed_at')->nullable()->after('answered_at');
            $table->text('close_reason')->nullable()->after('closed_at');

            $table->index(
                ['place_id', 'moderation_status', 'status', 'created_at', 'id'],
                'place_questions_place_moderation_status_idx',
            );
        });

        Schema::table('place_question_answers', function (Blueprint $table): void {
            $table->unsignedInteger('current_version')->default(1)->after('body');
            $table->text('correction_reason')->nullable()->after('current_version');
        });

        Schema::create('place_question_answer_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_question_answer_id')->constrained('place_question_answers')->restrictOnDelete();
            $table->foreignId('editor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96)->nullable();
            $table->unsignedInteger('version');
            $table->text('body');
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['place_question_answer_id', 'version'],
                'place_question_answer_versions_answer_version_unique',
            );
            $table->unique(['editor_user_id', 'idempotency_key'], 'place_answer_versions_editor_idempotency_unique');
        });

        Schema::create('place_question_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_question_id')->constrained('place_questions')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 96)->nullable();
            $table->string('event_type', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->string('public_summary_key', 190)->nullable();
            $table->text('private_note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['place_question_id', 'created_at', 'id'],
                'place_question_events_question_created_idx',
            );
            $table->unique(['actor_user_id', 'idempotency_key'], 'place_question_events_actor_idempotency_unique');
        });

        Schema::create('place_compatibility_backfills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_domain_state_id')->constrained('user_domain_states')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('contribution_type', ['correction', 'warning', 'review', 'question', 'report']);
            $table->string('legacy_key', 190);
            $table->string('payload_checksum', 64);
            $table->string('target_type', 190)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->enum('status', ['imported', 'skipped', 'failed'])->default('imported');
            $table->string('error_code', 120)->nullable();
            $table->timestamps();

            $table->unique(
                ['user_domain_state_id', 'contribution_type', 'legacy_key'],
                'place_compatibility_backfills_source_unique',
            );
            $table->index(
                ['status', 'contribution_type', 'id'],
                'place_compatibility_backfills_status_type_idx',
            );
            $table->index(
                ['target_type', 'target_id'],
                'place_compatibility_backfills_target_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('place_compatibility_backfills');
        Schema::dropIfExists('place_question_events');
        Schema::dropIfExists('place_question_answer_versions');

        Schema::table('place_question_answers', function (Blueprint $table): void {
            $table->dropColumn(['current_version', 'correction_reason']);
        });

        Schema::table('place_questions', function (Blueprint $table): void {
            $table->dropIndex('place_questions_place_moderation_status_idx');
            $table->dropForeign(['duplicate_question_id']);
            $table->dropForeign(['closed_by_user_id']);
            $table->dropColumn([
                'moderation_status',
                'duplicate_question_id',
                'closed_by_user_id',
                'closed_at',
                'close_reason',
            ]);
        });

        Schema::dropIfExists('place_review_response_versions');
        Schema::dropIfExists('place_review_responses');
        Schema::dropIfExists('place_review_events');
        Schema::dropIfExists('place_review_versions');
        Schema::dropIfExists('place_reviews');
        Schema::dropIfExists('place_warning_events');
        Schema::dropIfExists('place_warning_appeals');
        Schema::dropIfExists('place_warning_disputes');
        Schema::dropIfExists('place_warning_confirmations');
        Schema::dropIfExists('place_warnings');
        Schema::dropIfExists('place_correction_events');
        Schema::dropIfExists('place_corrections');
    }
};

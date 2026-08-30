<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_management_reviewers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->foreignId('appointed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 30);
            $table->boolean('is_active')->default(true);
            $table->timestamp('appointed_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'role', 'expires_at', 'id'], 'place_mgmt_reviewers_active_role_idx');
        });

        Schema::create('place_management_claims', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 100)->unique();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('claimant_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('represented_organization_id')
                ->nullable()
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->foreignId('predecessor_claim_id')
                ->nullable()
                ->constrained('place_management_claims')
                ->restrictOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('claim_purpose', 40)->default('initial');
            $table->string('requested_role', 40);
            $table->string('verification_method', 50);
            $table->text('contact_details')->nullable();
            $table->string('status', 30)->default('pending');
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision_reason_code', 100)->nullable();
            $table->text('decision_detail')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('evidence_expires_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason_code', 100)->nullable();
            $table->foreignId('superseded_by_claim_id')
                ->nullable()
                ->constrained('place_management_claims')
                ->restrictOnDelete();
            $table->string('active_conflict_key', 190)->nullable()->unique();
            $table->uuid('submission_idempotency_key')->unique();
            $table->char('submission_payload_fingerprint', 64);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(['place_id', 'status', 'submitted_at', 'id'], 'place_mgmt_claims_place_state_idx');
            $table->index(['claimant_user_id', 'status', 'submitted_at', 'id'], 'place_mgmt_claims_user_state_idx');
            $table->index(['represented_organization_id', 'status', 'id'], 'place_mgmt_claims_org_state_idx');
            $table->index(['predecessor_claim_id', 'status', 'id'], 'place_mgmt_claims_predecessor_idx');
            $table->index(['target_user_id', 'status', 'id'], 'place_mgmt_claims_target_idx');
            $table->index(['reviewer_user_id', 'status', 'submitted_at', 'id'], 'place_mgmt_claims_reviewer_state_idx');
            $table->index(['status', 'evidence_expires_at', 'expires_at', 'id'], 'place_mgmt_claims_expiry_idx');
        });

        Schema::create('place_management_claim_scopes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_management_claim_id')
                ->constrained('place_management_claims')
                ->cascadeOnDelete();
            $table->string('scope', 50);
            $table->timestamp('created_at');

            $table->unique(['place_management_claim_id', 'scope'], 'place_mgmt_claim_scope_unique');
            $table->index(['scope', 'place_management_claim_id'], 'place_mgmt_claim_scope_idx');
        });

        Schema::create('place_management_claim_evidence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_management_claim_id')
                ->constrained('place_management_claims')
                ->restrictOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('stable_key', 100)->unique();
            $table->string('private_disk', 40)->default('local');
            $table->string('private_path', 500);
            $table->text('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('byte_size');
            $table->char('checksum_sha256', 64);
            $table->string('evidence_type', 60);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->uuid('upload_idempotency_key');
            $table->char('upload_payload_fingerprint', 64);
            $table->timestamp('created_at');

            $table->unique(
                ['place_management_claim_id', 'checksum_sha256'],
                'place_mgmt_claim_evidence_checksum_unique',
            );
            $table->unique(
                ['place_management_claim_id', 'upload_idempotency_key'],
                'place_mgmt_claim_evidence_idem_unique',
            );
            $table->index(['place_management_claim_id', 'expires_at', 'id'], 'place_mgmt_claim_evidence_expiry_idx');
        });

        Schema::create('place_manager_authorities', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 100)->unique();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('source_claim_id')
                ->unique()
                ->constrained('place_management_claims')
                ->restrictOnDelete();
            $table->foreignId('granted_to_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('represented_organization_id')
                ->nullable()
                ->constrained('organizations')
                ->restrictOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 40);
            $table->string('status', 30)->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('ended_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ended_at')->nullable();
            $table->string('end_reason_code', 100)->nullable();
            $table->foreignId('superseded_by_authority_id')
                ->nullable()
                ->constrained('place_manager_authorities')
                ->restrictOnDelete();
            $table->string('active_authority_key', 190)->nullable()->unique();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index(['place_id', 'status', 'expires_at', 'id'], 'place_mgr_authorities_place_state_idx');
            $table->index(['granted_to_user_id', 'status', 'expires_at', 'id'], 'place_mgr_authorities_user_state_idx');
            $table->index(['represented_organization_id', 'status', 'expires_at', 'id'], 'place_mgr_authorities_org_state_idx');
        });

        Schema::create('place_manager_authority_scopes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_manager_authority_id')
                ->constrained('place_manager_authorities')
                ->cascadeOnDelete();
            $table->string('scope', 50);
            $table->timestamp('created_at');

            $table->unique(['place_manager_authority_id', 'scope'], 'place_mgr_authority_scope_unique');
            $table->index(['scope', 'place_manager_authority_id'], 'place_mgr_authority_scope_idx');
        });

        Schema::create('place_management_claim_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_management_claim_id')
                ->constrained('place_management_claims')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('place_manager_authority_id')
                ->nullable()
                ->constrained('place_manager_authorities')
                ->restrictOnDelete();
            $table->string('action', 50);
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->string('reason_code', 100);
            $table->uuid('idempotency_key')->unique();
            $table->char('payload_fingerprint', 64);
            $table->text('audit_context')->nullable();
            $table->unsignedInteger('expected_lock_version')->nullable();
            $table->unsignedInteger('result_lock_version');
            $table->timestamp('created_at');

            $table->index(['place_management_claim_id', 'created_at', 'id'], 'place_mgmt_claim_events_timeline_idx');
            $table->index(['actor_user_id', 'action', 'created_at', 'id'], 'place_mgmt_claim_events_actor_idx');
        });

        Schema::create('place_management_reviewer_recusals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_management_claim_id')
                ->constrained('place_management_claims')
                ->restrictOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->restrictOnDelete();
            $table->string('reason_code', 100);
            $table->text('private_note')->nullable();
            $table->uuid('idempotency_key')->unique();
            $table->timestamp('created_at');

            $table->unique(
                ['place_management_claim_id', 'reviewer_user_id'],
                'place_mgmt_reviewer_recusal_unique',
            );
        });

        Schema::create('place_management_notification_intents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_management_claim_event_id')
                ->constrained('place_management_claim_events')
                ->restrictOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->restrictOnDelete();
            $table->string('notification_kind', 60);
            $table->string('message_key', 190);
            $table->text('safe_payload');
            $table->string('deduplication_key', 190)->unique();
            $table->string('status', 30)->default('pending');
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(
                ['place_management_claim_event_id', 'recipient_user_id', 'notification_kind'],
                'place_mgmt_notification_event_recipient_unique',
            );
            $table->index(['status', 'created_at', 'id'], 'place_mgmt_notification_pending_idx');
        });

        Schema::create('place_management_abuse_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 100)->unique();
            $table->foreignId('reporter_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('place_management_claim_id')
                ->nullable()
                ->constrained('place_management_claims')
                ->restrictOnDelete();
            $table->foreignId('place_manager_authority_id')
                ->nullable()
                ->constrained('place_manager_authorities')
                ->restrictOnDelete();
            $table->string('reason_code', 80);
            $table->text('details');
            $table->string('status', 30)->default('open');
            $table->uuid('idempotency_key');
            $table->char('payload_fingerprint', 64);
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();

            $table->unique(
                ['reporter_user_id', 'idempotency_key'],
                'place_mgmt_abuse_report_idem_unique',
            );
            $table->index(['place_id', 'status', 'created_at', 'id'], 'place_mgmt_abuse_place_queue_idx');
            $table->index(['reporter_user_id', 'created_at', 'id'], 'place_mgmt_abuse_reporter_idx');
        });

        Schema::table('place_question_answers', function (Blueprint $table): void {
            $table->foreignId('place_manager_authority_id')
                ->nullable()
                ->constrained('place_manager_authorities')
                ->restrictOnDelete();
            $table->json('verification_scope')->nullable();
            $table->string('verification_source', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('place_question_answers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('place_manager_authority_id');
            $table->dropColumn(['verification_scope', 'verification_source']);
        });

        Schema::dropIfExists('place_management_abuse_reports');
        Schema::dropIfExists('place_management_notification_intents');
        Schema::dropIfExists('place_management_reviewer_recusals');
        Schema::dropIfExists('place_management_claim_events');
        Schema::dropIfExists('place_manager_authority_scopes');
        Schema::dropIfExists('place_manager_authorities');
        Schema::dropIfExists('place_management_claim_evidence');
        Schema::dropIfExists('place_management_claim_scopes');
        Schema::dropIfExists('place_management_claims');
        Schema::dropIfExists('place_management_reviewers');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_review_panels', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type', 80);
            $table->unsignedBigInteger('subject_id');
            $table->string('panel_type', 60);
            $table->string('risk_class', 40)->default('low');
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('state', 40)->default('awaiting-assignment');
            $table->unsignedSmallInteger('required_reviewers')->default(3);
            $table->string('decision', 40)->nullable();
            $table->text('decision_reason')->nullable();
            $table->foreignId('moderator_override_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('forum_moderation_case_id')->nullable()->constrained('forum_moderation_cases')->nullOnDelete();
            $table->foreignId('forum_moderation_appeal_id')->nullable()->constrained('forum_moderation_appeals')->nullOnDelete();
            $table->foreignId('appealed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('appeal_reason')->nullable();
            $table->timestamp('appealed_at')->nullable();
            $table->string('active_key', 220)->nullable()->unique();
            $table->timestamp('review_deadline_at');
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('public_context')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['subject_type', 'subject_id', 'panel_type', 'state'],
                'forum_review_panels_subject_type_state_idx',
            );
            $table->index(
                ['state', 'review_deadline_at', 'id'],
                'forum_review_panels_state_deadline_idx',
            );
            $table->index(
                'requested_by_user_id',
                'forum_review_panels_requester_fk_idx',
            );
            $table->index(
                'moderator_override_by_user_id',
                'forum_review_panels_override_actor_fk_idx',
            );
            $table->index(
                'forum_moderation_case_id',
                'forum_review_panels_case_fk_idx',
            );
            $table->index(
                'forum_moderation_appeal_id',
                'forum_review_panels_appeal_fk_idx',
            );
            $table->index(
                'appealed_by_user_id',
                'forum_review_panels_appealed_by_fk_idx',
            );
        });

        Schema::create('forum_review_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_review_panel_id')->constrained('forum_review_panels')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users')->restrictOnDelete();
            $table->string('state', 30)->default('assigned');
            $table->string('decision', 40)->nullable();
            $table->text('reasoning')->nullable();
            $table->boolean('has_conflict')->default(false);
            $table->string('conflict_type', 100)->nullable();
            $table->string('anonymous_reviewer_key', 64);
            $table->foreignId('replacement_for_assignment_id')->nullable()->constrained('forum_review_assignments')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('review_deadline_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('recused_at')->nullable();
            $table->string('idempotency_key', 180)->nullable()->unique();
            $table->timestamps();

            $table->unique(
                ['forum_review_panel_id', 'reviewer_user_id'],
                'forum_review_assignments_panel_reviewer_unique',
            );
            $table->index(
                ['reviewer_user_id', 'state', 'review_deadline_at'],
                'forum_review_assignments_reviewer_state_idx',
            );
            $table->index(
                'replacement_for_assignment_id',
                'forum_review_assignments_replacement_fk_idx',
            );
        });

        Schema::create('forum_review_panel_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_review_panel_id')->constrained('forum_review_panels')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('from_state', 40)->nullable();
            $table->string('to_state', 40)->nullable();
            $table->string('reason_code', 120);
            $table->string('summary_translation_key', 180);
            $table->json('metadata')->nullable();
            $table->string('idempotency_key', 180)->nullable()->unique();
            $table->timestamp('created_at');

            $table->index(
                ['forum_review_panel_id', 'created_at', 'id'],
                'forum_review_panel_events_panel_created_idx',
            );
            $table->index(
                ['actor_user_id', 'event_type', 'created_at'],
                'forum_review_panel_events_actor_type_idx',
            );
        });

        Schema::create('forum_community_notes', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type', 80);
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('proposer_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('subject_author_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note_type', 60);
            $table->string('status', 40)->default('proposed');
            $table->text('body');
            $table->json('evidence')->nullable();
            $table->text('author_response')->nullable();
            $table->string('jurisdiction', 120)->nullable();
            $table->string('species_context', 180)->nullable();
            $table->boolean('is_safety_notice')->default(false);
            $table->foreignId('forum_review_panel_id')->nullable()->constrained('forum_review_panels')->nullOnDelete();
            $table->foreignId('moderator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('moderator_decision', 40)->nullable();
            $table->text('decision_reason')->nullable();
            $table->unsignedInteger('current_version')->default(1);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('revalidation_due_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(
                ['subject_type', 'subject_id', 'status', 'published_at'],
                'forum_community_notes_subject_status_idx',
            );
            $table->index(
                ['status', 'revalidation_due_at', 'id'],
                'forum_community_notes_status_revalidation_idx',
            );
            $table->index(
                ['proposer_user_id', 'status', 'created_at'],
                'forum_community_notes_proposer_status_idx',
            );
            $table->index(
                'subject_author_user_id',
                'forum_community_notes_subject_author_fk_idx',
            );
            $table->index(
                'forum_review_panel_id',
                'forum_community_notes_panel_fk_idx',
            );
            $table->index(
                'moderator_user_id',
                'forum_community_notes_moderator_fk_idx',
            );
        });

        Schema::create('forum_community_note_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_community_note_id')->constrained('forum_community_notes')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->foreignId('editor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40);
            $table->text('body');
            $table->json('evidence')->nullable();
            $table->text('author_response')->nullable();
            $table->string('change_reason', 500);
            $table->string('source_event', 60);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            $table->unique(
                ['forum_community_note_id', 'version_number'],
                'forum_community_note_versions_note_version_unique',
            );
            $table->index(
                ['editor_user_id', 'created_at'],
                'forum_community_note_versions_editor_created_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_community_note_versions');
        Schema::dropIfExists('forum_community_notes');
        Schema::dropIfExists('forum_review_panel_events');
        Schema::dropIfExists('forum_review_assignments');
        Schema::dropIfExists('forum_review_panels');
    }
};

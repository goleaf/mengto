<?php

declare(strict_types=1);

use App\Models\ForumAnswer;
use App\Models\ForumComment;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_report_reasons', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 120)->unique();
            $table->string('translation_key', 190);
            $table->string('default_priority', 20)->default('standard');
            $table->boolean('allows_immediate_safety')->default(false);
            $table->boolean('requires_specialist_review')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('forum_reports', function (Blueprint $table): void {
            $table->foreignId('reporter_id')
                ->nullable()
                ->after('reporter_key')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('forum_report_reason_id')
                ->nullable()
                ->after('reason')
                ->constrained('forum_report_reasons')
                ->restrictOnDelete();
            $table->string('subject_type', 150)->nullable()->after('comment_id');
            $table->string('subject_id', 190)->nullable()->after('subject_type');
            $table->foreignId('affected_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('affected_pet_profile_id')
                ->nullable()
                ->constrained('pet_profiles')
                ->nullOnDelete();
            $table->foreignId('duplicate_of_report_id')
                ->nullable()
                ->constrained('forum_reports')
                ->nullOnDelete();
            $table->string('urgency', 20)->default('standard');
            $table->string('location_scope', 190)->nullable();
            $table->string('contact_preference', 40)->default('platform');
            $table->boolean('immediate_safety')->default(false);
            $table->boolean('truthfulness_confirmed')->default(false);
            $table->string('deduplication_key', 64)->nullable();
            $table->json('metadata')->nullable();

            $table->index(
                ['subject_type', 'subject_id', 'status'],
                'forum_reports_subject_status_idx',
            );
            $table->index(
                ['priority', 'status', 'created_at'],
                'forum_reports_priority_status_created_v2_idx',
            );
            $table->index(
                ['deduplication_key', 'status'],
                'forum_reports_deduplication_status_idx',
            );
        });

        Schema::create('forum_report_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_report_id')->constrained('forum_reports')->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('disk', 80);
            $table->string('path', 500);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum_sha256', 64);
            $table->string('visibility', 30)->default('moderators');
            $table->timestamps();
        });

        Schema::create('forum_report_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_report_id')->constrained('forum_reports')->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 100);
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();
            $table->string('user_message_translation_key', 190)->nullable();
            $table->text('internal_note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['forum_report_id', 'created_at'],
                'forum_report_events_report_created_idx',
            );
        });

        Schema::create('forum_moderation_cases', function (Blueprint $table): void {
            $table->id();
            $table->string('case_number', 40)->unique();
            $table->string('status', 50)->default('awaiting-review');
            $table->string('priority', 20)->default('standard');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('opened_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('subject_type', 150)->nullable();
            $table->string('subject_id', 190)->nullable();
            $table->string('summary_translation_key', 190);
            $table->text('internal_summary')->nullable();
            $table->timestamp('review_due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('retention_until')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['status', 'priority', 'review_due_at'],
                'forum_moderation_cases_queue_idx',
            );
            $table->index(
                ['subject_type', 'subject_id', 'status'],
                'forum_moderation_cases_subject_status_idx',
            );
        });

        Schema::create('forum_moderation_case_reports', function (Blueprint $table): void {
            $table->foreignId('forum_moderation_case_id')
                ->constrained('forum_moderation_cases')
                ->cascadeOnDelete();
            $table->foreignId('forum_report_id')->constrained('forum_reports')->restrictOnDelete();
            $table->foreignId('linked_by_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->primary(
                ['forum_moderation_case_id', 'forum_report_id'],
                'forum_moderation_case_reports_primary',
            );
        });

        Schema::create('forum_moderation_action_definitions', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 120)->unique();
            $table->string('translation_key', 190);
            $table->boolean('is_restrictive')->default(false);
            $table->boolean('is_appealable')->default(true);
            $table->boolean('requires_end_at')->default(false);
            $table->boolean('requires_senior_review')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('forum_moderation_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_moderation_case_id')
                ->constrained('forum_moderation_cases')
                ->restrictOnDelete();
            $table->foreignId('forum_moderation_action_definition_id')
                ->constrained('forum_moderation_action_definitions')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_type', 150)->nullable();
            $table->string('target_id', 190)->nullable();
            $table->string('rule_id', 120);
            $table->string('policy_basis', 190);
            $table->string('scope_type', 60)->default('global');
            $table->string('scope_key', 190)->default('global');
            $table->string('user_reason_translation_key', 190);
            $table->text('internal_reason');
            $table->json('evidence')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('review_at')->nullable();
            $table->boolean('appeal_available')->default(true);
            $table->foreignId('reversal_of_action_id')
                ->nullable()
                ->constrained('forum_moderation_actions')
                ->restrictOnDelete();
            $table->timestamp('reversed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['target_user_id', 'scope_type', 'scope_key', 'ends_at'],
                'forum_moderation_actions_user_scope_end_idx',
            );
        });

        Schema::create('forum_moderation_appeals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_moderation_action_id')
                ->constrained('forum_moderation_actions')
                ->restrictOnDelete();
            $table->foreignId('appellant_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 40)->default('submitted');
            $table->text('reason');
            $table->json('evidence')->nullable();
            $table->text('decision_reason')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['forum_moderation_action_id', 'appellant_user_id'],
                'forum_moderation_appeals_action_appellant_unique',
            );
        });

        Schema::create('forum_moderator_recusals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_moderation_case_id')
                ->constrained('forum_moderation_cases')
                ->cascadeOnDelete();
            $table->foreignId('moderator_user_id')->constrained('users')->restrictOnDelete();
            $table->string('reason_code', 100);
            $table->text('private_note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['forum_moderation_case_id', 'moderator_user_id'],
                'forum_moderator_recusals_case_moderator_unique',
            );
        });

        $this->backfillLegacyReportSubjects();
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_moderator_recusals');
        Schema::dropIfExists('forum_moderation_appeals');
        Schema::dropIfExists('forum_moderation_actions');
        Schema::dropIfExists('forum_moderation_action_definitions');
        Schema::dropIfExists('forum_moderation_case_reports');
        Schema::dropIfExists('forum_moderation_cases');
        Schema::dropIfExists('forum_report_events');
        Schema::dropIfExists('forum_report_attachments');

        Schema::table('forum_reports', function (Blueprint $table): void {
            $table->dropForeign(['reporter_id']);
            $table->dropForeign(['forum_report_reason_id']);
            $table->dropForeign(['affected_user_id']);
            $table->dropForeign(['affected_pet_profile_id']);
            $table->dropForeign(['duplicate_of_report_id']);
            $table->dropIndex('forum_reports_subject_status_idx');
            $table->dropIndex('forum_reports_priority_status_created_v2_idx');
            $table->dropIndex('forum_reports_deduplication_status_idx');
            $table->dropColumn([
                'reporter_id',
                'forum_report_reason_id',
                'subject_type',
                'subject_id',
                'affected_user_id',
                'affected_pet_profile_id',
                'duplicate_of_report_id',
                'urgency',
                'location_scope',
                'contact_preference',
                'immediate_safety',
                'truthfulness_confirmed',
                'deduplication_key',
                'metadata',
            ]);
        });

        Schema::dropIfExists('forum_report_reasons');
    }

    private function backfillLegacyReportSubjects(): void
    {
        ForumReport::query()
            ->whereNull('subject_type')
            ->lazyById()
            ->each(function (ForumReport $report): void {
                [$type, $id] = match (true) {
                    $report->answer_id !== null => [ForumAnswer::class, $report->answer_id],
                    $report->comment_id !== null => [ForumComment::class, $report->comment_id],
                    default => [ForumTopic::class, $report->topic_id],
                };

                $report->forceFill([
                    'subject_type' => $type,
                    'subject_id' => (string) $id,
                    'priority' => $report->priority === 'normal'
                        ? 'standard'
                        : $report->priority,
                    'urgency' => 'standard',
                    'contact_preference' => 'platform',
                    'immediate_safety' => false,
                    'truthfulness_confirmed' => false,
                    'deduplication_key' => hash(
                        'sha256',
                        implode('|', [$type, (string) $id, $report->reason]),
                    ),
                ])->save();
            });
    }
};

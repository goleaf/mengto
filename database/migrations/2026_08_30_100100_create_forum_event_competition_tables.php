<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_event_competitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')->constrained('forum_events')->restrictOnDelete();
            $table->foreignId('organizer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('name', 180);
            $table->string('status', 30)->default('draft');
            $table->string('result_visibility', 30)->default('private');
            $table->unsignedInteger('current_rule_version_number')->default(1);
            $table->unsignedInteger('current_result_version_number')->default(0);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('judging_opens_at')->nullable();
            $table->timestamp('judging_closes_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['forum_event_id', 'status', 'id'], 'event_competitions_event_state_idx');
        });
        Schema::create('forum_event_competition_rule_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('competition_id')->constrained('forum_event_competitions')->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->text('rules');
            $table->char('checksum', 64);
            $table->boolean('is_material')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at');
            $table->unique(['competition_id', 'version_number'], 'event_comp_rules_version_unique');
        });
        Schema::create('forum_event_competition_categories', function (Blueprint $table): void {
            $table->id(); $table->foreignId('competition_id')->constrained('forum_event_competitions')->restrictOnDelete();
            $table->string('stable_key', 190)->unique(); $table->string('name', 120); $table->unsignedSmallInteger('position')->default(0);
            $table->json('eligibility_rules')->nullable(); $table->timestamps();
            $table->unique(['competition_id', 'name'], 'event_comp_categories_name_unique');
            $table->index(['competition_id', 'position', 'id'], 'event_comp_categories_position_idx');
        });
        Schema::create('forum_event_competition_classes', function (Blueprint $table): void {
            $table->id(); $table->foreignId('category_id')->constrained('forum_event_competition_categories')->restrictOnDelete();
            $table->string('stable_key', 190)->unique(); $table->string('name', 120); $table->unsignedSmallInteger('position')->default(0);
            $table->json('eligibility_rules')->nullable(); $table->timestamps();
            $table->unique(['category_id', 'name'], 'event_comp_classes_name_unique');
        });
        Schema::create('forum_event_competition_entries', function (Blueprint $table): void {
            $table->id(); $table->foreignId('competition_id')->constrained('forum_event_competitions')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('forum_event_competition_categories')->restrictOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('forum_event_competition_classes')->restrictOnDelete();
            $table->foreignId('forum_event_registration_id')->constrained('forum_event_registrations')->restrictOnDelete();
            $table->foreignId('entrant_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('rule_version_id')->constrained('forum_event_competition_rule_versions')->restrictOnDelete();
            $table->string('stable_key', 190)->unique(); $table->string('display_name', 180); $table->string('status', 30)->default('awaiting_eligibility');
            $table->string('eligibility_status', 30)->default('pending'); $table->json('eligibility_snapshot')->nullable();
            $table->timestamp('rules_accepted_at')->nullable(); $table->timestamp('withdrawn_at')->nullable(); $table->timestamp('disqualified_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0); $table->timestamps();
            $table->unique(['competition_id', 'forum_event_registration_id'], 'event_comp_entries_registration_unique');
            $table->index(['competition_id', 'category_id', 'class_id', 'status', 'id'], 'event_comp_entries_queue_idx');
        });
        Schema::create('forum_event_competition_entry_pets', function (Blueprint $table): void {
            $table->id(); $table->foreignId('entry_id')->constrained('forum_event_competition_entries')->restrictOnDelete();
            $table->foreignId('pet_profile_id')->constrained('pet_profiles')->restrictOnDelete(); $table->timestamps();
            $table->unique(['entry_id', 'pet_profile_id'], 'event_comp_entry_pet_unique');
        });
        Schema::create('forum_event_competition_judge_assignments', function (Blueprint $table): void {
            $table->id(); $table->foreignId('competition_id')->constrained('forum_event_competitions')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('forum_event_competition_categories')->restrictOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('forum_event_competition_classes')->restrictOnDelete();
            $table->foreignId('judge_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('forum_event_team_membership_id')->constrained('forum_event_team_memberships')->restrictOnDelete();
            $table->string('status', 30)->default('active'); $table->boolean('identity_verified')->default(false);
            $table->timestamp('scoring_opens_at')->nullable(); $table->timestamp('scoring_closes_at')->nullable(); $table->timestamps();
            $table->unique(['competition_id', 'category_id', 'judge_user_id'], 'event_comp_judge_category_unique');
            $table->index(['judge_user_id', 'status', 'scoring_closes_at', 'id'], 'event_comp_judge_queue_idx');
        });
        Schema::create('forum_event_competition_judge_conflicts', function (Blueprint $table): void {
            $table->id(); $table->foreignId('judge_assignment_id')->constrained('forum_event_competition_judge_assignments')->restrictOnDelete();
            $table->foreignId('entry_id')->constrained('forum_event_competition_entries')->restrictOnDelete(); $table->string('status', 30)->default('open');
            $table->string('conflict_type', 60); $table->text('details')->nullable(); $table->timestamps();
            $table->unique(['judge_assignment_id', 'entry_id'], 'event_comp_judge_entry_conflict_unique');
        });
        Schema::create('forum_event_competition_criteria', function (Blueprint $table): void {
            $table->id(); $table->foreignId('category_id')->constrained('forum_event_competition_categories')->restrictOnDelete();
            $table->foreignId('rule_version_id')->constrained('forum_event_competition_rule_versions')->restrictOnDelete();
            $table->string('stable_key', 190)->unique(); $table->string('name', 120); $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedBigInteger('minimum_units'); $table->unsignedBigInteger('maximum_units'); $table->unsignedInteger('scale_factor')->default(1000);
            $table->unsignedInteger('weight_basis_points')->default(10000); $table->boolean('comment_required')->default(false); $table->timestamps();
            $table->unique(['category_id', 'rule_version_id', 'name'], 'event_comp_criteria_unique');
        });
        Schema::create('forum_event_competition_scores', function (Blueprint $table): void {
            $table->id(); $table->foreignId('judge_assignment_id')->constrained('forum_event_competition_judge_assignments')->restrictOnDelete();
            $table->foreignId('entry_id')->constrained('forum_event_competition_entries')->restrictOnDelete();
            $table->foreignId('criterion_id')->constrained('forum_event_competition_criteria')->restrictOnDelete();
            $table->unsignedInteger('current_revision_number')->default(1); $table->timestamps();
            $table->unique(['judge_assignment_id', 'entry_id', 'criterion_id'], 'event_comp_score_independent_unique');
        });
        Schema::create('forum_event_competition_score_revisions', function (Blueprint $table): void {
            $table->id(); $table->foreignId('score_id')->constrained('forum_event_competition_scores')->restrictOnDelete();
            $table->unsignedInteger('revision_number'); $table->unsignedBigInteger('value_units'); $table->text('comment')->nullable();
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete(); $table->string('reason_code', 100); $table->string('idempotency_key', 190)->unique(); $table->timestamp('created_at');
            $table->unique(['score_id', 'revision_number'], 'event_comp_score_revision_unique');
        });
        Schema::create('forum_event_competition_result_versions', function (Blueprint $table): void {
            $table->id(); $table->foreignId('competition_id')->constrained('forum_event_competitions')->restrictOnDelete();
            $table->unsignedInteger('version_number'); $table->string('status', 30)->default('finalized'); $table->string('idempotency_key', 190)->unique();
            $table->char('checksum', 64); $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete(); $table->timestamp('finalized_at'); $table->timestamp('published_at')->nullable();
            $table->unique(['competition_id', 'version_number'], 'event_comp_result_version_unique');
        });
        Schema::create('forum_event_competition_results', function (Blueprint $table): void {
            $table->id(); $table->foreignId('result_version_id')->constrained('forum_event_competition_result_versions')->restrictOnDelete();
            $table->foreignId('entry_id')->constrained('forum_event_competition_entries')->restrictOnDelete(); $table->unsignedInteger('rank')->nullable();
            $table->unsignedBigInteger('score_units')->default(0); $table->string('status', 30); $table->string('display_name', 180); $table->timestamps();
            $table->unique(['result_version_id', 'entry_id'], 'event_comp_result_entry_unique');
            $table->index(['result_version_id', 'rank', 'id'], 'event_comp_results_public_rank_idx');
        });
        Schema::create('forum_event_competition_appeals', function (Blueprint $table): void {
            $table->id(); $table->foreignId('entry_id')->constrained('forum_event_competition_entries')->restrictOnDelete();
            $table->foreignId('result_version_id')->constrained('forum_event_competition_result_versions')->restrictOnDelete();
            $table->foreignId('appellant_user_id')->constrained('users')->restrictOnDelete(); $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('submitted'); $table->string('rule_reference', 120); $table->text('evidence')->nullable(); $table->text('decision')->nullable();
            $table->timestamp('deadline_at'); $table->timestamp('decided_at')->nullable(); $table->string('active_key', 190)->nullable()->unique(); $table->timestamps();
        });
        Schema::create('forum_event_competition_history', function (Blueprint $table): void {
            $table->id(); $table->foreignId('competition_id')->constrained('forum_event_competitions')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('event_type', 80); $table->string('reason_code', 120);
            $table->json('metadata')->nullable(); $table->string('idempotency_key', 190)->nullable()->unique(); $table->timestamp('created_at');
            $table->index(['competition_id', 'created_at', 'id'], 'event_comp_history_timeline_idx');
        });
    }
    public function down(): void
    {
        foreach (['forum_event_competition_history','forum_event_competition_appeals','forum_event_competition_results','forum_event_competition_result_versions','forum_event_competition_score_revisions','forum_event_competition_scores','forum_event_competition_criteria','forum_event_competition_judge_conflicts','forum_event_competition_judge_assignments','forum_event_competition_entry_pets','forum_event_competition_entries','forum_event_competition_classes','forum_event_competition_categories','forum_event_competition_rule_versions','forum_event_competitions'] as $table) { Schema::dropIfExists($table); }
    }
};

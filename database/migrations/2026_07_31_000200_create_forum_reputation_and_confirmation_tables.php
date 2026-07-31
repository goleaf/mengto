<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_reputation_dimensions', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 100)->unique();
            $table->string('name_translation_key', 190);
            $table->string('description_translation_key', 190);
            $table->integer('daily_actor_recipient_cap')->default(10);
            $table->integer('relationship_cap')->default(50);
            $table->boolean('is_public_by_default')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('forum_reputation_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('forum_reputation_dimension_id')
                ->constrained('forum_reputation_dimensions')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('forum_category_id')
                ->nullable()
                ->constrained('forum_categories')
                ->nullOnDelete();
            $table->foreignId('taxon_id')->nullable()->constrained('taxa')->nullOnDelete();
            $table->foreignId('reversal_of_event_id')
                ->nullable()
                ->constrained('forum_reputation_events')
                ->restrictOnDelete();
            $table->string('event_type', 100);
            $table->string('source_entity_type', 120);
            $table->string('source_entity_id', 190);
            $table->integer('amount');
            $table->string('reason_code', 120);
            $table->string('explanation_translation_key', 190);
            $table->string('location_scope_key', 190)->nullable();
            $table->string('status', 40)->default('active');
            $table->string('idempotency_key', 190)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('effective_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('review_at')->nullable();
            $table->timestamps();

            $table->index(
                ['user_id', 'forum_reputation_dimension_id', 'status', 'effective_at'],
                'forum_reputation_events_user_dimension_status_idx',
            );
            $table->index(
                ['actor_user_id', 'user_id', 'effective_at'],
                'forum_reputation_events_actor_recipient_idx',
            );
            $table->index(
                ['source_entity_type', 'source_entity_id', 'status'],
                'forum_reputation_events_source_status_idx',
            );
        });

        Schema::create('forum_reputation_aggregates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('forum_reputation_dimension_id')
                ->constrained('forum_reputation_dimensions')
                ->restrictOnDelete();
            $table->foreignId('forum_category_id')
                ->nullable()
                ->constrained('forum_categories')
                ->cascadeOnDelete();
            $table->foreignId('taxon_id')->nullable()->constrained('taxa')->cascadeOnDelete();
            $table->string('location_scope_key', 190)->nullable();
            $table->string('scope_key', 64);
            $table->bigInteger('total')->default(0);
            $table->timestamp('last_event_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'forum_reputation_dimension_id', 'scope_key'],
                'forum_reputation_aggregates_owner_dimension_scope_unique',
            );
        });

        Schema::create('forum_trust_levels', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 100)->unique();
            $table->string('name_translation_key', 190);
            $table->string('description_translation_key', 190);
            $table->unsignedSmallInteger('position');
            $table->boolean('is_professional')->default(false);
            $table->boolean('is_moderation_role')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('criteria')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('forum_user_trust_levels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('forum_trust_level_id')
                ->constrained('forum_trust_levels')
                ->restrictOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('scope_type', 60)->default('global');
            $table->string('scope_key', 190)->default('global');
            $table->string('reason_code', 120);
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'scope_type', 'scope_key'],
                'forum_user_trust_levels_user_scope_unique',
            );
        });

        Schema::create('forum_trust_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_forum_trust_level_id')
                ->nullable()
                ->constrained('forum_trust_levels')
                ->restrictOnDelete();
            $table->foreignId('to_forum_trust_level_id')
                ->constrained('forum_trust_levels')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('scope_type', 60);
            $table->string('scope_key', 190);
            $table->string('reason_code', 120);
            $table->json('evidence')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['user_id', 'scope_type', 'scope_key', 'created_at'],
                'forum_trust_history_user_scope_created_idx',
            );
        });

        Schema::create('forum_badges', function (Blueprint $table): void {
            $table->id();
            $table->string('stable_key', 120)->unique();
            $table->string('name_translation_key', 190);
            $table->string('description_translation_key', 190);
            $table->unsignedSmallInteger('criteria_version')->default(1);
            $table->json('criteria');
            $table->json('revocation_rules')->nullable();
            $table->boolean('requires_moderation_review')->default(false);
            $table->boolean('expires')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('forum_user_badges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('forum_badge_id')->constrained('forum_badges')->restrictOnDelete();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('scope_key', 190)->default('global');
            $table->string('status', 40)->default('active');
            $table->string('reason_code', 120);
            $table->boolean('is_public')->default(true);
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(
                ['user_id', 'forum_badge_id', 'scope_key'],
                'forum_user_badges_user_badge_scope_unique',
            );
        });

        Schema::create('forum_confirmations', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type', 120);
            $table->string('subject_id', 190);
            $table->foreignId('requester_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('state', 50)->default('awaiting-confirmation');
            $table->text('claim_text')->nullable();
            $table->json('structured_claim')->nullable();
            $table->json('scope')->nullable();
            $table->string('risk_class', 40)->default('low');
            $table->unsignedSmallInteger('required_quorum')->default(3);
            $table->unsignedSmallInteger('required_diversity')->default(2);
            $table->decimal('confidence', 5, 4)->default(0);
            $table->unsignedInteger('supporting_votes')->default(0);
            $table->unsignedInteger('opposing_votes')->default(0);
            $table->unsignedInteger('abstentions')->default(0);
            $table->foreignId('moderator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('moderator_decision', 80)->nullable();
            $table->timestamp('review_deadline_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revalidation_due_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['subject_type', 'subject_id', 'state'],
                'forum_confirmations_subject_state_idx',
            );
            $table->index(
                ['state', 'review_deadline_at'],
                'forum_confirmations_state_deadline_idx',
            );
        });

        Schema::create('forum_confirmation_votes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_confirmation_id')
                ->constrained('forum_confirmations')
                ->cascadeOnDelete();
            $table->foreignId('voter_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('stance', 20);
            $table->decimal('weight', 4, 2)->default(1);
            $table->boolean('has_conflict')->default(false);
            $table->string('conflict_type', 100)->nullable();
            $table->string('independence_cluster', 100);
            $table->text('reasoning')->nullable();
            $table->string('status', 40)->default('eligible');
            $table->timestamps();

            $table->unique(
                ['forum_confirmation_id', 'voter_user_id'],
                'forum_confirmation_votes_confirmation_voter_unique',
            );
            $table->index(
                ['forum_confirmation_id', 'stance', 'status'],
                'forum_confirmation_votes_confirmation_stance_idx',
            );
        });

        Schema::create('forum_confirmation_evidence', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_confirmation_id')
                ->constrained('forum_confirmations')
                ->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('evidence_type', 60);
            $table->text('summary');
            $table->string('source_url', 500)->nullable();
            $table->string('private_disk')->nullable();
            $table->string('private_path', 500)->nullable();
            $table->string('status', 40)->default('submitted');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('forum_votes', function (Blueprint $table): void {
            $table->foreignId('user_id')
                ->nullable()
                ->after('answer_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('reputation_event_id')
                ->nullable()
                ->after('user_id')
                ->constrained('forum_reputation_events')
                ->nullOnDelete();
            $table->unsignedInteger('effect_revision')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('forum_votes', function (Blueprint $table): void {
            $table->dropForeign(['reputation_event_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'reputation_event_id', 'effect_revision']);
        });

        Schema::dropIfExists('forum_confirmation_evidence');
        Schema::dropIfExists('forum_confirmation_votes');
        Schema::dropIfExists('forum_confirmations');
        Schema::dropIfExists('forum_user_badges');
        Schema::dropIfExists('forum_badges');
        Schema::dropIfExists('forum_trust_history');
        Schema::dropIfExists('forum_user_trust_levels');
        Schema::dropIfExists('forum_trust_levels');
        Schema::dropIfExists('forum_reputation_aggregates');
        Schema::dropIfExists('forum_reputation_events');
        Schema::dropIfExists('forum_reputation_dimensions');
    }
};

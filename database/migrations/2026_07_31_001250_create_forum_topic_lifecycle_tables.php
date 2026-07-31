<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_topics', function (Blueprint $table): void {
            $table->timestamp('state_entered_at')->nullable();
            $table->timestamp('last_author_update_at')->nullable();
            $table->timestamp('last_bumped_at')->nullable();
            $table->timestamp('stale_review_requested_at')->nullable();
            $table->timestamp('outdated_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamp('restored_at')->nullable();
            $table->timestamp('redirected_at')->nullable();
            $table->json('redirect_path')->nullable();
            $table->timestamp('legal_hold_at')->nullable();
            $table->timestamp('retention_until')->nullable();

            $table->index(
                ['status', 'state_entered_at', 'id'],
                'forum_topics_lifecycle_state_idx',
            );
            $table->index(
                ['forum_category_id', 'last_activity_at', 'id'],
                'forum_topics_lifecycle_category_activity_idx',
            );
            $table->index(
                ['legal_hold_at', 'retention_until', 'id'],
                'forum_topics_lifecycle_retention_idx',
            );
            $table->index(
                ['merged_into_topic_id', 'status'],
                'forum_topics_lifecycle_redirect_idx',
            );
        });

        Schema::create('forum_category_lifecycle_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_category_id')
                ->unique()
                ->constrained('forum_categories')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('stale_after_days')->default(180);
            $table->unsignedSmallInteger('necropost_after_days')->default(90);
            $table->unsignedSmallInteger('archive_review_after_days')->nullable();
            $table->unsignedSmallInteger('retention_review_after_days')->nullable();
            $table->unsignedSmallInteger('bump_cooldown_hours')->default(168);
            $table->boolean('allow_author_reopen')->default(true);
            $table->boolean('allow_author_archive')->default(true);
            $table->boolean('allow_author_remove')->default(true);
            $table->boolean('allow_bumping')->default(true);
            $table->boolean('auto_archive_enabled')->default(false);
            $table->unsignedSmallInteger('rules_version')->default(1);
            $table->boolean('is_system_managed')->default(true);
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                'updated_by_user_id',
                'forum_category_lifecycle_rules_updater_idx',
            );
        });

        Schema::create('forum_topic_lifecycle_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_topic_id')
                ->constrained('forum_topics')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('event_type', 60);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40)->nullable();
            $table->string('reason_code', 100);
            $table->string('reason_translation_key', 190)->nullable();
            $table->unsignedInteger('lock_version');
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(
                ['forum_topic_id', 'occurred_at', 'id'],
                'forum_topic_lifecycle_events_topic_time_idx',
            );
            $table->index(
                ['event_type', 'occurred_at', 'id'],
                'forum_topic_lifecycle_events_type_time_idx',
            );
            $table->index(
                'actor_user_id',
                'forum_topic_lifecycle_events_actor_idx',
            );
        });

        Schema::create('forum_topic_update_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_topic_id')
                ->constrained('forum_topics')
                ->restrictOnDelete();
            $table->foreignId('requester_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('kind', 40);
            $table->string('status', 40)->default('pending');
            $table->text('reason');
            $table->text('proposed_body')->nullable();
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('resolution_reason')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->string('idempotency_key', 190)->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['forum_topic_id', 'status', 'created_at', 'id'],
                'forum_topic_update_requests_topic_status_idx',
            );
            $table->index(
                ['requester_user_id', 'created_at', 'id'],
                'forum_topic_update_requests_requester_time_idx',
            );
            $table->index(
                'reviewed_by_user_id',
                'forum_topic_update_requests_reviewer_idx',
            );
        });

        Schema::create('forum_topic_legal_holds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_topic_id')
                ->constrained('forum_topics')
                ->restrictOnDelete();
            $table->foreignId('applied_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('reason_code', 100);
            $table->text('private_reason');
            $table->timestamp('starts_at');
            $table->timestamp('review_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('released_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('release_reason')->nullable();
            $table->string('active_key', 190)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['forum_topic_id', 'released_at', 'id'],
                'forum_topic_legal_holds_topic_active_idx',
            );
            $table->index(
                ['review_at', 'released_at', 'id'],
                'forum_topic_legal_holds_review_idx',
            );
            $table->index(
                'applied_by_user_id',
                'forum_topic_legal_holds_applier_idx',
            );
            $table->index(
                'released_by_user_id',
                'forum_topic_legal_holds_releaser_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_topic_legal_holds');
        Schema::dropIfExists('forum_topic_update_requests');
        Schema::dropIfExists('forum_topic_lifecycle_events');
        Schema::dropIfExists('forum_category_lifecycle_rules');

        Schema::table('forum_topics', function (Blueprint $table): void {
            $table->dropIndex('forum_topics_lifecycle_redirect_idx');
            $table->dropIndex('forum_topics_lifecycle_retention_idx');
            $table->dropIndex('forum_topics_lifecycle_category_activity_idx');
            $table->dropIndex('forum_topics_lifecycle_state_idx');
            $table->dropColumn([
                'state_entered_at',
                'last_author_update_at',
                'last_bumped_at',
                'stale_review_requested_at',
                'outdated_at',
                'locked_at',
                'removed_at',
                'restored_at',
                'redirected_at',
                'redirect_path',
                'legal_hold_at',
                'retention_until',
            ]);
        });
    }
};

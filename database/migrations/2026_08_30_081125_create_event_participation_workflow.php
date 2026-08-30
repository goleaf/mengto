<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_event_registrations', function (Blueprint $table): void {
            $table->dropUnique('forum_event_registrations_occurrence_user_unique');
            $table->dropUnique('forum_event_registrations_waitlist_unique');
            $table->string('active_scope_key', 64)->nullable()->after('idempotency_key');
            $table->string('participation_role', 40)->default('attendee')->after('active_scope_key');
            $table->unsignedBigInteger('current_snapshot_id')->nullable();
            $table->unsignedBigInteger('current_eligibility_decision_set_id')->nullable();
            $table->timestamp('eligibility_stale_at')->nullable();
            $table->timestamp('acceptance_stale_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->index(
                ['forum_event_id', 'forum_event_occurrence_id', 'user_id', 'status', 'id'],
                'fe_reg_scope_status_idx',
            );
        });

        Schema::table('forum_event_occurrences', function (Blueprint $table): void {
            $table->unsignedInteger('lock_version')->default(0);
        });

        $terminal = [
            'attended', 'completed', 'no_show', 'withdrawn', 'rejected', 'declined',
            'cancelled', 'cancelled_by_organizer', 'expired', 'refunded',
        ];

        DB::table('forum_event_registrations')
            ->select(['id', 'forum_event_id', 'forum_event_occurrence_id', 'user_id', 'status'])
            ->orderBy('id')
            ->chunkById(200, function ($registrations) use ($terminal): void {
                foreach ($registrations as $registration) {
                    DB::table('forum_event_registrations')
                        ->where('id', $registration->id)
                        ->update([
                            'active_scope_key' => in_array($registration->status, $terminal, true)
                                ? null
                                : hash('sha256', implode(':', [
                                    'event-registration',
                                    (string) $registration->forum_event_id,
                                    (string) ($registration->forum_event_occurrence_id ?? 'event'),
                                    (string) $registration->user_id,
                                    'attendee',
                                ])),
                            'status_changed_at' => now(),
                        ]);
                }
            });

        Schema::table('forum_event_registrations', function (Blueprint $table): void {
            $table->unique('active_scope_key', 'fe_reg_active_scope_unique');
        });

        Schema::create('forum_event_registration_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('supersedes_snapshot_id')
                ->nullable()->constrained('forum_event_registration_snapshots')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('kind', 30);
            $table->unsignedSmallInteger('schema_version')->default(1);
            $table->text('payload');
            $table->char('content_checksum', 64);
            $table->char('integrity_mac', 64);
            $table->unsignedSmallInteger('integrity_key_version')->default(1);
            $table->timestamp('created_at');
            $table->unique(
                ['forum_event_registration_id', 'sequence'],
                'fe_reg_snap_sequence_unique',
            );
            $table->index(
                ['forum_event_registration_id', 'content_checksum', 'id'],
                'fe_reg_snap_checksum_idx',
            );
        });

        Schema::create('forum_event_eligibility_decision_sets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_registration_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('generation');
            $table->unsignedInteger('event_version_number');
            $table->char('requirement_set_checksum', 64);
            $table->char('subject_fingerprint', 64);
            $table->string('status', 30);
            $table->text('decision_snapshot');
            $table->char('content_checksum', 64);
            $table->timestamp('evaluated_at');
            $table->timestamp('valid_until')->nullable();
            $table->timestamp('stale_at')->nullable();
            $table->string('stale_reason_code', 100)->nullable();
            $table->string('trigger_type', 40);
            $table->string('trigger_key', 190);
            $table->timestamps();
            $table->unique(
                ['forum_event_registration_id', 'generation'],
                'fe_elig_set_generation_unique',
            );
            $table->index(
                ['forum_event_registration_id', 'status', 'stale_at', 'id'],
                'fe_elig_set_current_idx',
            );
        });

        Schema::create('forum_event_eligibility_decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_eligibility_decision_set_id')
                ->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_registration_pet_id')
                ->constrained('forum_event_registration_pets')->restrictOnDelete();
            $table->foreignId('pet_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requirement_key', 100);
            $table->string('status', 30);
            $table->string('reason_code', 100);
            $table->char('subject_fingerprint', 64);
            $table->text('conditions')->nullable();
            $table->string('verification_source', 40);
            $table->timestamp('decided_at');
            $table->timestamp('valid_until')->nullable();
            $table->timestamp('stale_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['forum_event_eligibility_decision_set_id', 'forum_event_registration_pet_id', 'requirement_key'],
                'fe_elig_dec_subject_req_unique',
            );
            $table->index(
                ['pet_profile_id', 'status', 'valid_until', 'id'],
                'fe_elig_dec_pet_status_idx',
            );
        });

        Schema::create('forum_event_participation_operations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_occurrence_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_registration_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('principal_key', 190);
            $table->string('operation_type', 60);
            $table->string('idempotency_key', 190);
            $table->char('request_checksum', 64);
            $table->string('status', 30)->default('processing');
            $table->string('result_type', 60)->nullable();
            $table->unsignedBigInteger('result_id')->nullable();
            $table->string('result_status', 40)->nullable();
            $table->unsignedInteger('expected_version')->nullable();
            $table->unsignedInteger('result_version')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->unique(
                ['forum_event_id', 'principal_key', 'operation_type', 'idempotency_key'],
                'fe_part_operation_unique',
            );
            $table->index(['status', 'created_at', 'id'], 'fe_part_operation_status_idx');
        });

        Schema::create('forum_event_participation_transitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_participation_operation_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version');
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('reason_code', 100);
            $table->text('participant_explanation')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->unique(
                ['forum_event_registration_id', 'version'],
                'fe_part_transition_version_unique',
            );
            $table->index(
                ['forum_event_registration_id', 'occurred_at', 'id'],
                'fe_part_transition_time_idx',
            );
        });

        Schema::create('forum_event_capacity_pools', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_occurrence_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_session_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('scope_type', 30);
            $table->string('scope_key', 190);
            $table->string('scope_identity', 190);
            $table->string('capacity_type', 40);
            $table->string('dimension_key', 190)->default('all');
            $table->unsignedInteger('effective_capacity');
            $table->unsignedInteger('allocated_quantity')->default(0);
            $table->unsignedInteger('held_quantity')->default(0);
            $table->string('reservation_mode', 30)->default('direct');
            $table->boolean('waitlist_enabled')->default(false);
            $table->unsignedInteger('hold_duration_seconds')->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('reconciled_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['scope_identity', 'capacity_type', 'dimension_key'],
                'fe_capacity_pool_dimension_unique',
            );
            $table->index(
                ['forum_event_id', 'scope_type', 'status', 'id'],
                'fe_capacity_pool_scope_idx',
            );
        });

        Schema::create('forum_event_capacity_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_capacity_pool_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('active_registration_id')
                ->nullable()->constrained('forum_event_registrations')->restrictOnDelete();
            $table->foreignId('forum_event_participation_operation_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('status', 30)->default('active');
            $table->timestamp('allocated_at');
            $table->timestamp('released_at')->nullable();
            $table->string('release_reason_code', 100)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->unique(
                ['forum_event_capacity_pool_id', 'active_registration_id'],
                'fe_capacity_allocation_active_unique',
            );
            $table->index(
                ['forum_event_registration_id', 'status', 'id'],
                'fe_capacity_allocation_reg_idx',
            );
        });

        Schema::create('forum_event_capacity_holds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_occurrence_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('active_registration_id')
                ->nullable()->unique('fe_capacity_hold_active_reg_unique')
                ->constrained('forum_event_registrations')->restrictOnDelete();
            $table->foreignId('forum_event_participation_operation_id')->constrained()->restrictOnDelete();
            $table->string('purpose', 30);
            $table->string('status', 30)->default('active');
            $table->timestamp('expires_at');
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->string('release_reason_code', 100)->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->index(['status', 'expires_at', 'id'], 'fe_capacity_hold_expiry_idx');
        });

        Schema::create('forum_event_capacity_hold_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_capacity_hold_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_capacity_pool_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(
                ['forum_event_capacity_hold_id', 'forum_event_capacity_pool_id'],
                'fe_capacity_hold_item_unique',
            );
            $table->index(
                ['forum_event_capacity_pool_id', 'forum_event_capacity_hold_id'],
                'fe_capacity_hold_item_pool_idx',
            );
        });

        Schema::create('forum_event_waitlists', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_occurrence_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('primary_capacity_pool_id')
                ->unique('fe_waitlist_primary_pool_unique')
                ->constrained('forum_event_capacity_pools')->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('ordering_mode', 40)->default('priority_requested_id');
            $table->unsignedInteger('policy_version')->default(1);
            $table->unsignedInteger('offer_duration_seconds')->nullable();
            $table->string('status', 30)->default('active');
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->index(
                ['forum_event_id', 'forum_event_occurrence_id', 'status', 'id'],
                'fe_waitlist_event_occurrence_idx',
            );
        });

        Schema::create('forum_event_waitlist_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_waitlist_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_registration_id')->constrained()->restrictOnDelete();
            $table->foreignId('active_registration_id')
                ->nullable()->unique('fe_wait_entry_active_reg_unique')
                ->constrained('forum_event_registrations')->restrictOnDelete();
            $table->foreignId('forum_event_capacity_hold_id')
                ->nullable()->constrained('forum_event_capacity_holds')->restrictOnDelete();
            $table->string('status', 30)->default('waiting');
            $table->unsignedSmallInteger('priority_band')->default(100);
            $table->string('priority_rule_code', 60)->default('chronological');
            $table->timestamp('requested_at');
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();
            $table->index(
                ['forum_event_waitlist_id', 'status', 'priority_band', 'requested_at', 'id'],
                'fe_wait_entry_order_idx',
            );
        });

        Schema::create('forum_event_waitlist_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_event_waitlist_entry_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_capacity_pool_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
            $table->unique(
                ['forum_event_waitlist_entry_id', 'forum_event_capacity_pool_id'],
                'fe_wait_requirement_unique',
            );
            $table->index(
                ['forum_event_capacity_pool_id', 'forum_event_waitlist_entry_id'],
                'fe_wait_requirement_pool_idx',
            );
        });

        Schema::create('forum_event_notification_intents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_id')->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_registration_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('forum_event_participation_transition_id')
                ->nullable()->constrained()->restrictOnDelete();
            $table->char('deduplication_key', 64)->unique();
            $table->string('type', 80);
            $table->string('title_translation_key', 190);
            $table->string('body_translation_key', 190);
            $table->text('replacements')->nullable();
            $table->string('locale', 12);
            $table->string('status', 30)->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedInteger('delivery_attempts')->default(0);
            $table->timestamps();
            $table->index(['status', 'created_at', 'id'], 'fe_notify_intent_status_idx');
        });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('forum_event_registrations', function (Blueprint $table): void {
                $table->foreign('current_snapshot_id', 'fe_reg_current_snapshot_fk')
                    ->references('id')->on('forum_event_registration_snapshots')->restrictOnDelete();
                $table->foreign('current_eligibility_decision_set_id', 'fe_reg_current_elig_set_fk')
                    ->references('id')->on('forum_event_eligibility_decision_sets')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('forum_event_registrations', function (Blueprint $table): void {
                $table->dropForeign('fe_reg_current_snapshot_fk');
                $table->dropForeign('fe_reg_current_elig_set_fk');
            });
        }

        Schema::dropIfExists('forum_event_notification_intents');
        Schema::dropIfExists('forum_event_waitlist_requirements');
        Schema::dropIfExists('forum_event_waitlist_entries');
        Schema::dropIfExists('forum_event_waitlists');
        Schema::dropIfExists('forum_event_capacity_hold_items');
        Schema::dropIfExists('forum_event_capacity_holds');
        Schema::dropIfExists('forum_event_capacity_allocations');
        Schema::dropIfExists('forum_event_capacity_pools');
        Schema::dropIfExists('forum_event_participation_transitions');
        Schema::dropIfExists('forum_event_participation_operations');
        Schema::dropIfExists('forum_event_eligibility_decisions');
        Schema::dropIfExists('forum_event_eligibility_decision_sets');
        Schema::dropIfExists('forum_event_registration_snapshots');

        Schema::table('forum_event_occurrences', function (Blueprint $table): void {
            $table->dropColumn('lock_version');
        });

        Schema::table('forum_event_registrations', function (Blueprint $table): void {
            $table->dropUnique('fe_reg_active_scope_unique');
            $table->dropIndex('fe_reg_scope_status_idx');
            $table->dropColumn([
                'active_scope_key',
                'participation_role',
                'current_snapshot_id',
                'current_eligibility_decision_set_id',
                'eligibility_stale_at',
                'acceptance_stale_at',
                'status_changed_at',
            ]);
            $table->unique(
                ['forum_event_id', 'forum_event_occurrence_id', 'user_id'],
                'forum_event_registrations_occurrence_user_unique',
            );
            $table->unique(
                ['forum_event_id', 'waitlist_position'],
                'forum_event_registrations_waitlist_unique',
            );
        });
    }
};

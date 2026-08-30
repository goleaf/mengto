<?php

declare(strict_types=1);

use App\Models\Place;
use App\Services\PlaceIdentityNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table): void {
            $table->string('normalized_name', 190)->nullable()->after('name');
            $table->string('normalized_address', 500)->nullable()->after('public_address');
            $table->string('normalized_phone', 40)->nullable()->after('public_phone');
            $table->string('normalized_email', 255)->nullable()->after('public_email');
            $table->string('normalized_website', 500)->nullable()->after('public_website');
            $table->foreignId('merged_into_place_id')
                ->nullable()
                ->after('archived_at')
                ->constrained('places')
                ->restrictOnDelete();

            $table->index(['normalized_name', 'status', 'id'], 'places_normalized_name_idx');
            $table->index(['normalized_address', 'status', 'id'], 'places_normalized_address_idx');
            $table->index(['normalized_phone', 'status', 'id'], 'places_normalized_phone_idx');
            $table->index(['normalized_email', 'status', 'id'], 'places_normalized_email_idx');
            $table->index(['normalized_website', 'status', 'id'], 'places_normalized_website_idx');
            $table->index(['merged_into_place_id', 'status', 'id'], 'places_merged_destination_idx');
        });

        $normalizer = new PlaceIdentityNormalizer;

        foreach (Place::query()
            ->select([
                'id', 'name', 'public_address', 'public_phone', 'public_email', 'public_website',
                'normalized_name', 'normalized_address', 'normalized_phone', 'normalized_email',
                'normalized_website',
            ])
            ->lazyById(250) as $place) {
            $place->timestamps = false;
            $place->forceFill([
                'normalized_name' => $normalizer->name($place->name),
                'normalized_address' => $normalizer->address($place->public_address),
                'normalized_phone' => $normalizer->phone($place->public_phone),
                'normalized_email' => $normalizer->email($place->public_email),
                'normalized_website' => $normalizer->website($place->public_website),
            ])->saveQuietly();
        }

        Schema::create('place_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('submitter_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('canonical_organization_id')->nullable()->constrained('organizations')->restrictOnDelete();
            $table->foreignId('published_place_id')->nullable()->constrained('places')->restrictOnDelete();
            $table->foreignId('linked_place_id')->nullable()->constrained('places')->restrictOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('idempotency_key', 190);
            $table->string('payload_fingerprint', 64);
            $table->string('status', 40)->default('submitted');
            $table->string('resolution', 40)->default('none');
            $table->string('source_kind', 40);
            $table->text('source_reference')->nullable();
            $table->string('relationship_to_place', 40);
            $table->string('location_precision', 40);
            $table->string('locale', 10);
            $table->string('name', 180);
            $table->string('normalized_name', 190);
            $table->string('catalog_category', 40);
            $table->string('place_type', 50);
            $table->text('summary')->nullable();
            $table->string('public_region', 160);
            $table->string('public_address', 500)->nullable();
            $table->string('normalized_address', 500)->nullable();
            $table->decimal('public_latitude', 9, 6)->nullable();
            $table->decimal('public_longitude', 9, 6)->nullable();
            $table->text('exact_address')->nullable();
            $table->text('exact_latitude')->nullable();
            $table->text('exact_longitude')->nullable();
            $table->string('public_phone', 40)->nullable();
            $table->string('normalized_phone', 40)->nullable();
            $table->string('public_email', 255)->nullable();
            $table->string('normalized_email', 255)->nullable();
            $table->string('public_website', 2048)->nullable();
            $table->string('normalized_website', 500)->nullable();
            $table->string('identity_hash', 64);
            $table->text('submitted_facts')->nullable();
            $table->string('consent_version', 40);
            $table->timestamp('consented_at');
            $table->timestamp('observed_at')->nullable();
            $table->text('audit_context')->nullable();
            $table->boolean('continued_as_distinct')->default(false);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->unique(['submitter_user_id', 'idempotency_key'], 'place_submissions_submitter_request_unique');
            $table->index(['status', 'submitted_at', 'id'], 'place_submissions_review_queue_idx');
            $table->index(['submitter_user_id', 'submitted_at', 'id'], 'place_submissions_submitter_timeline_idx');
            $table->index(['canonical_organization_id', 'status', 'id'], 'place_submissions_organization_status_idx');
            $table->index(['normalized_name', 'status', 'id'], 'place_submissions_name_status_idx');
            $table->index(['normalized_address', 'status', 'id'], 'place_submissions_address_status_idx');
            $table->index(['normalized_phone', 'status', 'id'], 'place_submissions_phone_status_idx');
            $table->index(['normalized_email', 'status', 'id'], 'place_submissions_email_status_idx');
            $table->index(['normalized_website', 'status', 'id'], 'place_submissions_website_status_idx');
            $table->index(['identity_hash', 'status', 'id'], 'place_submissions_identity_status_idx');
            $table->index(['published_place_id', 'status', 'id'], 'place_submissions_published_status_idx');
            $table->index(['linked_place_id', 'status', 'id'], 'place_submissions_linked_status_idx');
            $table->index('reviewed_by_user_id', 'place_submissions_reviewer_idx');
        });

        Schema::create('place_submission_identity_locks', function (Blueprint $table): void {
            $table->string('identity_hash', 64)->primary();
            $table->foreignId('first_submission_id')->nullable()->constrained('place_submissions')->restrictOnDelete();
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestamps();

            $table->index('first_submission_id', 'place_submission_identity_locks_first_idx');
        });

        Schema::create('place_submission_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_submission_id')->constrained('place_submissions')->restrictOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users')->restrictOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->unsignedInteger('revision_number');
            $table->string('kind', 40);
            $table->text('summary')->nullable();
            $table->timestamp('created_at');

            $table->unique(['place_submission_id', 'revision_number'], 'place_submission_revisions_number_unique');
            $table->index(['place_submission_id', 'created_at', 'id'], 'place_submission_revisions_timeline_idx');
            $table->index('submitted_by_user_id', 'place_submission_revisions_submitter_idx');
        });

        Schema::create('place_facts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_submission_id')->nullable()->constrained('place_submissions')->restrictOnDelete();
            $table->foreignId('place_submission_revision_id')->nullable()->constrained('place_submission_revisions')->restrictOnDelete();
            $table->foreignId('place_id')->nullable()->constrained('places')->restrictOnDelete();
            $table->foreignId('origin_place_id')->nullable()->constrained('places')->restrictOnDelete();
            $table->foreignId('copied_from_fact_id')->nullable()->constrained('place_facts')->restrictOnDelete();
            $table->foreignId('submitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stable_key', 190)->unique();
            $table->string('field_key', 80);
            $table->text('field_value');
            $table->string('value_hash', 64);
            $table->string('source_kind', 40);
            $table->text('source_reference')->nullable();
            $table->string('provenance_scope', 40);
            $table->string('visibility_scope', 40);
            $table->timestamp('observed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('created_at');

            $table->index(['place_submission_id', 'field_key', 'id'], 'place_facts_submission_field_idx');
            $table->index(['place_submission_revision_id', 'field_key', 'id'], 'place_facts_revision_field_idx');
            $table->index(['place_id', 'field_key', 'created_at', 'id'], 'place_facts_place_field_idx');
            $table->index(['origin_place_id', 'created_at', 'id'], 'place_facts_origin_timeline_idx');
            $table->index('copied_from_fact_id', 'place_facts_copied_from_idx');
            $table->index('submitted_by_user_id', 'place_facts_submitter_idx');
            $table->index('reviewed_by_user_id', 'place_facts_reviewer_idx');
        });

        Schema::create('place_duplicate_candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_submission_id')->constrained('place_submissions')->restrictOnDelete();
            $table->foreignId('candidate_place_id')->nullable()->constrained('places')->restrictOnDelete();
            $table->foreignId('candidate_submission_id')->nullable()->constrained('place_submissions')->restrictOnDelete();
            $table->string('candidate_key', 190)->unique();
            $table->string('algorithm_version', 40);
            $table->string('signals_fingerprint', 64);
            $table->unsignedSmallInteger('score');
            $table->string('confidence', 30);
            $table->json('matched_signals');
            $table->unsignedInteger('distance_meters')->nullable();
            $table->string('presentation_scope', 30)->default('review_only');
            $table->timestamp('created_at');

            $table->index(['place_submission_id', 'score', 'id'], 'place_duplicate_candidates_submission_score_idx');
            $table->unique(['place_submission_id', 'candidate_place_id'], 'place_duplicate_candidates_place_unique');
            $table->unique(['place_submission_id', 'candidate_submission_id'], 'place_duplicate_candidates_submission_unique');
            $table->index(['candidate_place_id', 'score', 'id'], 'place_duplicate_candidates_place_score_idx');
            $table->index(['candidate_submission_id', 'score', 'id'], 'place_duplicate_candidates_submission_candidate_idx');
        });

        Schema::create('place_submission_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('place_submission_id')->constrained('place_submissions')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('place_duplicate_candidate_id')->nullable()->constrained('place_duplicate_candidates')->restrictOnDelete();
            $table->foreignId('candidate_place_id')->nullable()->constrained('places')->restrictOnDelete();
            $table->foreignId('destination_place_id')->nullable()->constrained('places')->restrictOnDelete();
            $table->string('idempotency_key', 190)->nullable()->unique();
            $table->string('action', 40);
            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->string('reason_code', 80)->nullable();
            $table->text('reason_detail')->nullable();
            $table->string('payload_fingerprint', 64)->nullable();
            $table->unsignedInteger('expected_lock_version')->nullable();
            $table->unsignedInteger('result_lock_version')->nullable();
            $table->text('audit_context')->nullable();
            $table->timestamp('created_at');

            $table->index(['place_submission_id', 'created_at', 'id'], 'place_submission_events_timeline_idx');
            $table->index(['actor_user_id', 'created_at', 'id'], 'place_submission_events_actor_idx');
            $table->index('candidate_place_id', 'place_submission_events_candidate_idx');
            $table->index('place_duplicate_candidate_id', 'place_submission_events_candidate_snapshot_idx');
            $table->index('destination_place_id', 'place_submission_events_destination_idx');
        });

        Schema::create('place_merge_redirects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('destination_place_id')->constrained('places')->restrictOnDelete();
            $table->foreignId('place_submission_event_id')->nullable()->constrained('place_submission_events')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_identifier', 190)->unique();
            $table->string('source_visibility', 30);
            $table->timestamp('restored_at')->nullable();
            $table->timestamp('created_at');

            $table->index(['source_place_id', 'restored_at', 'id'], 'place_merge_redirects_source_idx');
            $table->index(['destination_place_id', 'restored_at', 'id'], 'place_merge_redirects_destination_idx');
            $table->index('place_submission_event_id', 'place_merge_redirects_event_idx');
            $table->index('created_by_user_id', 'place_merge_redirects_creator_idx');
            $table->index('restored_by_user_id', 'place_merge_redirects_restorer_idx');
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('place_merge_redirects');
        Schema::dropIfExists('place_submission_events');
        Schema::dropIfExists('place_duplicate_candidates');
        Schema::dropIfExists('place_facts');
        Schema::dropIfExists('place_submission_revisions');
        Schema::dropIfExists('place_submission_identity_locks');
        Schema::dropIfExists('place_submissions');

        Schema::table('places', function (Blueprint $table): void {
            $table->dropForeign(['merged_into_place_id']);
            $table->dropIndex('places_normalized_name_idx');
            $table->dropIndex('places_normalized_address_idx');
            $table->dropIndex('places_normalized_phone_idx');
            $table->dropIndex('places_normalized_email_idx');
            $table->dropIndex('places_normalized_website_idx');
            $table->dropIndex('places_merged_destination_idx');
            $table->dropColumn([
                'normalized_name',
                'normalized_address',
                'normalized_phone',
                'normalized_email',
                'normalized_website',
                'merged_into_place_id',
            ]);
        });
    }
};

<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('place contribution tables preserve canonical relations and immutable history', function (): void {
    $tables = [
        'place_corrections',
        'place_correction_events',
        'place_warnings',
        'place_warning_confirmations',
        'place_warning_disputes',
        'place_warning_appeals',
        'place_warning_events',
        'place_reviews',
        'place_review_versions',
        'place_review_events',
        'place_review_responses',
        'place_review_response_versions',
        'place_question_answer_versions',
        'place_question_events',
        'place_compatibility_backfills',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }
});

test('place contribution projections carry lifecycle idempotency and moderation fields', function (): void {
    expect(Schema::hasColumns('place_corrections', [
        'place_id',
        'submitter_user_id',
        'reviewer_user_id',
        'stable_key',
        'idempotency_key',
        'correction_field',
        'original_value',
        'original_version',
        'proposed_value',
        'explanation',
        'evidence',
        'moderation_status',
        'resolution',
        'applied_value',
        'applied_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('place_warnings', [
            'place_id',
            'author_user_id',
            'moderator_user_id',
            'stable_key',
            'idempotency_key',
            'category',
            'severity',
            'affected_scope',
            'source',
            'evidence',
            'status',
            'published_at',
            'expires_at',
            'disputed_at',
            'resolved_at',
            'resolution',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('place_reviews', [
            'place_id',
            'author_user_id',
            'pet_profile_id',
            'stable_key',
            'idempotency_key',
            'eligibility_context',
            'verified_visit',
            'rating_overall',
            'rating_service',
            'rating_accessibility',
            'rating_pet_friendliness',
            'body',
            'anonymity_mode',
            'moderation_status',
            'current_version',
            'deleted_at',
            'restored_at',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('place_questions', [
            'moderation_status',
            'duplicate_question_id',
            'closed_by_user_id',
            'closed_at',
            'close_reason',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('place_question_answers', [
            'current_version',
            'correction_reason',
        ]))->toBeTrue();
});

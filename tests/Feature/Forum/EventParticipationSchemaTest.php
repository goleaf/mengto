<?php

declare(strict_types=1);

use App\Enums\ForumEventRegistrationStatus;
use Illuminate\Support\Facades\Schema;

test('event participation persistence is relational and keeps private decision history', function () {
    foreach ([
        'forum_event_registration_snapshots',
        'forum_event_eligibility_decision_sets',
        'forum_event_eligibility_decisions',
        'forum_event_participation_operations',
        'forum_event_participation_transitions',
        'forum_event_capacity_pools',
        'forum_event_capacity_allocations',
        'forum_event_capacity_holds',
        'forum_event_capacity_hold_items',
        'forum_event_waitlists',
        'forum_event_waitlist_entries',
        'forum_event_waitlist_requirements',
        'forum_event_notification_intents',
    ] as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Missing {$table}.");
    }

    expect(Schema::hasColumns('forum_event_registrations', [
        'active_scope_key',
        'participation_role',
        'current_snapshot_id',
        'current_eligibility_decision_set_id',
        'eligibility_stale_at',
        'acceptance_stale_at',
        'status_changed_at',
    ]))->toBeTrue();
});

test('registration states expose an explicit transition and capacity contract', function () {
    expect(ForumEventRegistrationStatus::Pending->canTransitionTo(
        ForumEventRegistrationStatus::Confirmed,
    ))->toBeTrue()
        ->and(ForumEventRegistrationStatus::Pending->canTransitionTo(
            ForumEventRegistrationStatus::Rejected,
        ))->toBeTrue()
        ->and(ForumEventRegistrationStatus::Waitlisted->canTransitionTo(
            ForumEventRegistrationStatus::Confirmed,
        ))->toBeTrue()
        ->and(ForumEventRegistrationStatus::Confirmed->canTransitionTo(
            ForumEventRegistrationStatus::CheckedIn,
        ))->toBeTrue()
        ->and(ForumEventRegistrationStatus::CheckedIn->canTransitionTo(
            ForumEventRegistrationStatus::Completed,
        ))->toBeTrue()
        ->and(ForumEventRegistrationStatus::Completed->isTerminal())->toBeTrue()
        ->and(ForumEventRegistrationStatus::Rejected->isTerminal())->toBeTrue()
        ->and(ForumEventRegistrationStatus::Waitlisted->consumesCapacity())->toBeFalse()
        ->and(ForumEventRegistrationStatus::Confirmed->consumesCapacity())->toBeTrue()
        ->and(ForumEventRegistrationStatus::Completed->consumesCapacity())->toBeFalse();
});

test('event invitations preserve history while enforcing one active recipient generation', function (): void {
    $indexes = collect(Schema::getIndexes('forum_event_invitations'))->keyBy('name');

    expect(Schema::hasColumns('forum_event_invitations', [
        'active_pair_key',
        'request_checksum',
    ]))->toBeTrue()
        ->and($indexes)->toHaveKey('forum_event_invitations_active_pair_unique')
        ->and($indexes['forum_event_invitations_active_pair_unique']['unique'])->toBeTrue()
        ->and($indexes)->toHaveKey('forum_event_invitations_pair_history_idx')
        ->and($indexes)->not->toHaveKey('forum_event_invitations_event_user_unique');
});

test('meetup moderation visibility uses an indexed canonical target lookup', function (): void {
    $indexes = collect(Schema::getIndexes('forum_moderation_actions'))->keyBy('name');

    expect($indexes)->toHaveKey('forum_moderation_actions_target_active_idx')
        ->and($indexes['forum_moderation_actions_target_active_idx']['columns'])
        ->toBe(['target_type', 'target_id', 'reversed_at', 'starts_at', 'ends_at']);
});

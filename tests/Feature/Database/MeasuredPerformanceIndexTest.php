<?php

declare(strict_types=1);

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('measured timeline and directory queries have order compatible indexes', function () {
    $expected = [
        'care_entries' => ['care_entries_journal_started_id_idx', ['care_journal_id', 'started_at', 'id']],
        'device_readings' => ['device_readings_device_recorded_id_idx', ['smart_device_id', 'recorded_at', 'id']],
        'device_events' => ['device_events_device_occurred_id_idx', ['smart_device_id', 'occurred_at', 'id']],
        'search_cases' => ['search_cases_public_recency_id_idx', ['moderation_status', 'visibility', 'archived_at', 'last_sighting_at', 'last_seen_at', 'id']],
        'audit_logs' => ['audit_logs_target_created_id_idx', ['target_type', 'target_id', 'created_at', 'id']],
        'forum_event_messages' => ['forum_event_messages_event_id_idx', ['forum_event_id', 'id']],
        'forum_groups' => ['forum_groups_status_updated_id_idx', ['status', 'updated_at', 'id']],
    ];

    foreach ($expected as $table => [$name, $columns]) {
        $index = collect(Schema::getIndexes($table))->keyBy('name')->get($name);

        expect($index, $table)->not->toBeNull()
            ->and($index['columns'], $table)->toBe($columns);
    }
});

test('sqlite plans use the measured order compatible indexes', function (): void {
    $plans = [
        'care_entries_journal_started_id_idx' => measuredIndexPlan(
            DB::table('care_entries')
                ->where('care_journal_id', 1)
                ->whereBetween('started_at', ['2026-08-01', '2026-09-01'])
                ->orderByDesc('started_at')
                ->orderByDesc('id')
                ->limit(30),
        ),
        'device_readings_device_recorded_id_idx' => measuredIndexPlan(
            DB::table('device_readings')
                ->where('smart_device_id', 1)
                ->orderByDesc('recorded_at')
                ->orderByDesc('id')
                ->limit(30),
        ),
        'device_events_device_occurred_id_idx' => measuredIndexPlan(
            DB::table('device_events')
                ->where('smart_device_id', 1)
                ->orderByDesc('occurred_at')
                ->orderByDesc('id')
                ->limit(20),
        ),
        'search_cases_public_recency_id_idx' => measuredIndexPlan(
            DB::table('search_cases')
                ->where('moderation_status', 'approved')
                ->where('visibility', 'public')
                ->whereNull('archived_at')
                ->orderByDesc('last_sighting_at')
                ->orderByDesc('last_seen_at')
                ->orderByDesc('id')
                ->limit(10),
        ),
        'audit_logs_target_created_id_idx' => measuredIndexPlan(
            DB::table('audit_logs')
                ->where('target_type', 'App\\Models\\MedicalAccessGrant')
                ->where('target_id', '1')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(16),
        ),
        'forum_event_messages_event_id_idx' => measuredIndexPlan(
            DB::table('forum_event_messages')
                ->where('forum_event_id', 1)
                ->orderByDesc('id')
                ->limit(50),
        ),
        'forum_groups_status_updated_id_idx' => measuredIndexPlan(
            DB::table('forum_groups')
                ->where('status', 'active')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->limit(12),
        ),
    ];

    foreach ($plans as $index => $plan) {
        expect($plan, $index)->toContain($index)
            ->not->toContain('USE TEMP B-TREE FOR ORDER BY');
    }
});

test('the measured performance index migration is reversible', function (): void {
    $migration = require database_path(
        'migrations/2026_08_30_260000_add_measured_performance_indexes.php',
    );

    $migration->down();

    expect(Schema::hasIndex('care_entries', 'care_entries_journal_started_id_idx'))->toBeFalse()
        ->and(Schema::hasIndex('forum_groups', 'forum_groups_status_updated_id_idx'))->toBeFalse();

    $migration->up();

    expect(Schema::hasIndex('care_entries', 'care_entries_journal_started_id_idx'))->toBeTrue()
        ->and(Schema::hasIndex('forum_groups', 'forum_groups_status_updated_id_idx'))->toBeTrue();
});

function measuredIndexPlan(Builder $query): string
{
    return collect(DB::select(
        'EXPLAIN QUERY PLAN '.$query->toSql(),
        $query->getBindings(),
    ))
        ->pluck('detail')
        ->implode(' | ');
}

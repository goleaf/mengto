<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

test('measured timeline and directory queries have order compatible indexes', function () {
    $expected = [
        'care_entries' => 'care_entries_journal_started_id_idx',
        'device_readings' => 'device_readings_device_recorded_id_idx',
        'device_events' => 'device_events_device_occurred_id_idx',
        'search_cases' => 'search_cases_public_recency_id_idx',
        'audit_logs' => 'audit_logs_target_created_id_idx',
        'forum_event_messages' => 'forum_event_messages_event_id_idx',
        'forum_groups' => 'forum_groups_status_updated_id_idx',
    ];

    foreach ($expected as $table => $index) {
        expect(collect(Schema::getIndexes($table))->pluck('name'), $table)
            ->toContain($index);
    }
});

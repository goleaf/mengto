<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, list<string>>
     */
    private const INDEXES = [
        'availability_slots' => ['service_id'],
        'bookings' => ['client_id', 'availability_slot_id', 'service_id'],
        'care_journals' => ['owner_id'],
        'device_automation_runs' => ['device_event_id'],
        'device_events' => [
            'search_case_id',
            'care_entry_id',
            'device_pet_assignment_id',
        ],
        'device_readings' => [
            'weight_entry_id',
            'medical_event_id',
            'care_entry_id',
            'device_pet_assignment_id',
        ],
        'document_grants' => ['booking_id'],
        'expert_profiles' => ['owner_id'],
        'expert_reports' => ['review_id', 'booking_id', 'expert_profile_id'],
        'forum_answers' => ['author_id'],
        'forum_comments' => ['author_id'],
        'forum_reports' => ['comment_id'],
        'forum_topics' => ['author_id'],
        'listing_reports' => ['reporter_id'],
        'listings' => ['owner_id'],
        'medical_documents' => ['vaccination_id', 'medical_event_id'],
        'medical_records' => ['owner_id'],
        'order_disputes' => ['listing_id'],
        'orders' => ['seller_id', 'buyer_id'],
        'reservations' => ['requester_id'],
        'reviews' => ['service_id'],
        'search_cases' => ['owner_id'],
        'search_reports' => ['reporter_id', 'sighting_id'],
        'sightings' => ['reporter_id'],
        'smart_devices' => ['owner_id'],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns): void {
                foreach ($columns as $column) {
                    $blueprint->index($column, self::indexName($table, $column));
                }
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::INDEXES, true) as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns): void {
                foreach (array_reverse($columns) as $column) {
                    $blueprint->dropIndex(self::indexName($table, $column));
                }
            });
        }
    }

    private static function indexName(string $table, string $column): string
    {
        return "{$table}_{$column}_fk_idx";
    }
};

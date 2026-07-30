<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('device_events', function (Blueprint $table): void {
            $table->unsignedSmallInteger('occurrence_count')
                ->default(1)
                ->after('status');
            $table->timestamp('first_occurred_at')
                ->nullable()
                ->after('occurrence_count');
            $table->timestamp('last_occurred_at')
                ->nullable()
                ->after('first_occurred_at');

            $table->index(
                ['smart_device_id', 'type', 'status', 'last_occurred_at'],
                'device_events_grouping_window_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('device_events', function (Blueprint $table): void {
            $table->dropIndex('device_events_grouping_window_idx');
            $table->dropColumn([
                'occurrence_count',
                'first_occurred_at',
                'last_occurred_at',
            ]);
        });
    }
};

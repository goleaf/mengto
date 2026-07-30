<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_devices', function (Blueprint $table): void {
            $table->unsignedSmallInteger('location_retention_days')
                ->default(30)
                ->after('location_accuracy_meters');
            $table->unsignedSmallInteger('media_retention_days')
                ->default(7)
                ->after('location_retention_days');
            $table->unsignedSmallInteger('telemetry_retention_days')
                ->default(365)
                ->after('media_retention_days');
            $table->text('safety_state')
                ->nullable()
                ->after('telemetry_retention_days');
            $table->timestamp('safety_state_recorded_at')
                ->nullable()
                ->after('safety_state');
            $table->string('provider_status', 32)
                ->default('not-configured')
                ->after('connection_type');

            $table->index(
                ['provider_status', 'connection_status'],
                'smart_devices_provider_connection_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('smart_devices', function (Blueprint $table): void {
            $table->dropIndex('smart_devices_provider_connection_idx');
            $table->dropColumn([
                'location_retention_days',
                'media_retention_days',
                'telemetry_retention_days',
                'safety_state',
                'safety_state_recorded_at',
                'provider_status',
            ]);
        });
    }
};

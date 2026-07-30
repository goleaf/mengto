<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\SmartDevice;

final class EnforceDeviceRetention
{
    /**
     * @return array{readings: int, events: int, location_readings: int}
     */
    public function handle(SmartDevice $device, ?int $protectedReadingId = null): array
    {
        $telemetryCutoff = now()->subDays($device->telemetry_retention_days);
        $readings = $device->readings()
            ->when(
                $protectedReadingId !== null,
                fn ($query) => $query->whereKeyNot($protectedReadingId),
            )
            ->whereNull('care_entry_id')
            ->whereNull('medical_event_id')
            ->whereNull('weight_entry_id')
            ->where('recorded_at', '<', $telemetryCutoff)
            ->delete();
        $events = $device->events()
            ->whereNull('care_entry_id')
            ->whereNull('search_case_id')
            ->where('occurred_at', '<', $telemetryCutoff)
            ->delete();

        $locationQuery = $device->readings()
            ->where('metric_type', 'location')
            ->whereNull('care_entry_id')
            ->whereNull('medical_event_id')
            ->whereNull('weight_entry_id');

        if ($protectedReadingId !== null) {
            $locationQuery->whereKeyNot($protectedReadingId);
        }

        if ($device->location_retention_days === 0) {
            $latestLocationId = $device->readings()
                ->where('metric_type', 'location')
                ->latest('recorded_at')
                ->value('id');

            if ($latestLocationId !== null) {
                $locationQuery->whereKeyNot($latestLocationId);
            }
        } else {
            $locationQuery->where(
                'recorded_at',
                '<',
                now()->subDays($device->location_retention_days),
            );
        }

        return [
            'readings' => $readings,
            'events' => $events,
            'location_readings' => $locationQuery->delete(),
        ];
    }
}

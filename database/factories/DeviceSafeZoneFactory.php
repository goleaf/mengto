<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeviceSafeZone;
use App\Models\SmartDevice;

/**
 * @extends ApplicationFactory<DeviceSafeZone>
 */
class DeviceSafeZoneFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'smart_device_id' => SmartDevice::factory(),
            'name' => 'Home',
            'shape' => 'circle',
            'public_area_label' => 'Home area',
            'exact_geometry' => [
                'latitude' => 45.5202,
                'longitude' => -122.6742,
                'radius_meters' => 120,
            ],
            'schedule' => ['always_active' => true],
            'exit_delay_seconds' => 45,
            'accuracy_threshold_meters' => 35,
            'status' => 'active',
            'is_home' => true,
        ];
    }
}

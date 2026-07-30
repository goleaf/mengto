<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceConnectionStatus;
use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\SmartDevice;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<SmartDevice>
 */
class SmartDeviceFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Scout GPS', 'Kitchen feeder', 'Living room camera',
            'Hallway litter box', 'Bedroom temperature',
        ]);

        return [
            'owner_key' => 'mia-carter',
            'slug' => Str::slug($name.'-'.fake()->unique()->numerify('####')),
            'name' => $name,
            'type' => fake()->randomElement(DeviceType::cases()),
            'brand' => fake()->company(),
            'model' => fake()->bothify('Pet-##??'),
            'serial_number' => fake()->bothify('SN-########'),
            'public_zone_label' => 'Home area',
            'private_location_label' => 'Private room inside the home',
            'privacy' => 'private',
            'status' => DeviceStatus::Active,
            'connection_status' => DeviceConnectionStatus::Online,
            'operating_mode' => 'normal',
            'connection_type' => 'wi-fi',
            'provider_status' => 'not-configured',
            'firmware_version' => '1.2.0',
            'battery_percent' => fake()->numberBetween(35, 100),
            'signal_strength' => fake()->numberBetween(-85, -35),
            'last_seen_at' => now()->subMinutes(2),
            'last_synced_at' => now()->subMinutes(2),
            'location_retention_days' => 30,
            'media_retention_days' => 7,
            'telemetry_retention_days' => 365,
            'has_backup_power' => true,
            'supports_local_operation' => true,
            'requires_cloud' => false,
            'is_medical_device' => false,
            'is_blocked' => false,
            'is_reported_stolen' => false,
            'lock_version' => 1,
        ];
    }
}

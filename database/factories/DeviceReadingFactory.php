<?php

namespace Database\Factories;

use App\Enums\DeviceConfidence;
use App\Models\DeviceReading;
use App\Models\SmartDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeviceReading>
 */
class DeviceReadingFactory extends Factory
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
            'pet_profile_key' => 'scout',
            'pet_name' => 'Scout',
            'external_event_id' => 'reading-'.Str::uuid(),
            'metric_type' => 'activity-minutes',
            'numeric_value' => fake()->numberBetween(15, 120),
            'unit' => 'min',
            'recorded_at' => now()->subMinutes(fake()->numberBetween(1, 240)),
            'timezone' => 'Europe/Vilnius',
            'confidence' => DeviceConfidence::Medium,
            'verification_status' => 'device-unverified',
            'original_payload' => ['source' => 'factory'],
            'processed_payload' => ['method' => 'device estimate'],
            'is_stale' => false,
        ];
    }
}

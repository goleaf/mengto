<?php

namespace Database\Factories;

use App\Enums\DeviceConfidence;
use App\Enums\DeviceEventSeverity;
use App\Models\DeviceEvent;
use App\Models\SmartDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeviceEvent>
 */
class DeviceEventFactory extends Factory
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
            'external_event_id' => 'event-'.Str::uuid(),
            'type' => 'device-offline',
            'severity' => DeviceEventSeverity::Important,
            'status' => 'open',
            'title' => 'Device connection needs checking',
            'summary' => 'The device has not sent a recent update.',
            'details' => ['last_signal' => now()->subMinutes(20)->toAtomString()],
            'occurred_at' => now()->subMinutes(20),
            'timezone' => 'Europe/Vilnius',
            'confidence' => DeviceConfidence::High,
            'source' => 'device',
            'requires_attention' => true,
        ];
    }
}

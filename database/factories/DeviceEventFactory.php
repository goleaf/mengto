<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceConfidence;
use App\Enums\DeviceEventSeverity;
use App\Models\DeviceEvent;
use App\Models\SmartDevice;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<DeviceEvent>
 */
class DeviceEventFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $occurredAt = now()->subMinutes(10);

        return [
            'smart_device_id' => SmartDevice::factory(),
            'pet_profile_key' => 'scout',
            'pet_name' => 'Scout',
            'external_event_id' => 'event-'.Str::uuid(),
            'type' => 'device-offline',
            'severity' => DeviceEventSeverity::Important,
            'status' => 'open',
            'occurrence_count' => 1,
            'first_occurred_at' => $occurredAt,
            'last_occurred_at' => $occurredAt,
            'title' => 'Device connection needs checking',
            'summary' => 'The device has not sent a recent update.',
            'details' => ['last_signal' => now()->subMinutes(20)->toAtomString()],
            'occurred_at' => $occurredAt,
            'timezone' => 'Europe/Vilnius',
            'confidence' => DeviceConfidence::High,
            'source' => 'device',
            'requires_attention' => true,
        ];
    }
}

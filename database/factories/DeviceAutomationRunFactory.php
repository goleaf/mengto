<?php

namespace Database\Factories;

use App\Models\DeviceAutomation;
use App\Models\DeviceAutomationRun;
use App\Models\SmartDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeviceAutomationRun>
 */
class DeviceAutomationRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_automation_id' => DeviceAutomation::factory(),
            'smart_device_id' => SmartDevice::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'trigger_snapshot' => ['type' => 'device-offline'],
            'action_snapshot' => ['type' => 'send-notification'],
            'status' => 'simulated',
            'is_simulation' => true,
            'started_at' => now(),
            'completed_at' => now(),
            'result' => ['message' => 'Test completed without a real command'],
        ];
    }
}

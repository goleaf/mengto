<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceAutomationStatus;
use App\Models\DeviceAutomation;
use App\Models\SmartDevice;

/**
 * @extends ApplicationFactory<DeviceAutomation>
 */
class DeviceAutomationFactory extends ApplicationFactory
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
            'owner_key' => 'mia-carter',
            'name' => 'Notify when device goes offline',
            'trigger_type' => 'device-offline',
            'trigger_config' => ['after_minutes' => 15],
            'condition_config' => ['home_mode' => 'any'],
            'action_type' => 'send-notification',
            'action_config' => ['recipients' => ['mia-carter']],
            'status' => DeviceAutomationStatus::Enabled,
            'priority' => 'important',
            'safety_level' => 'normal',
            'max_runs_per_hour' => 2,
            'cooldown_seconds' => 900,
            'version' => 1,
        ];
    }
}

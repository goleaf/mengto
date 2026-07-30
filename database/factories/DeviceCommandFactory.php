<?php

namespace Database\Factories;

use App\Enums\DeviceCommandStatus;
use App\Models\DeviceCommand;
use App\Models\SmartDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeviceCommand>
 */
class DeviceCommandFactory extends Factory
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
            'author_key' => 'mia-carter',
            'author_name' => 'Mia Carter',
            'idempotency_key' => (string) Str::uuid(),
            'command_type' => 'refresh-status',
            'parameters' => [],
            'status' => DeviceCommandStatus::Completed,
            'safety_level' => 'normal',
            'requires_confirmation' => false,
            'issued_at' => now(),
            'delivered_at' => now(),
            'completed_at' => now(),
            'result' => ['message' => 'Status refreshed'],
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\DeviceAccessGrant;
use App\Models\SmartDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeviceAccessGrant>
 */
class DeviceAccessGrantFactory extends Factory
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
            'granted_by_key' => 'mia-carter',
            'recipient_name' => fake()->name(),
            'recipient_role' => 'sitter',
            'label' => 'Weekend device access',
            'token_hash' => hash('sha256', (string) Str::uuid()),
            'permissions' => ['view-status'],
            'allow_location' => false,
            'allow_camera' => false,
            'allow_commands' => false,
            'allow_audio' => false,
            'max_views' => 20,
            'views_used' => 0,
            'starts_at' => now(),
            'expires_at' => now()->addDays(2),
        ];
    }
}

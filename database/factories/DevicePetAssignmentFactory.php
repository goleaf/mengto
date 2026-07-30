<?php

namespace Database\Factories;

use App\Enums\DeviceConfidence;
use App\Models\DevicePetAssignment;
use App\Models\SmartDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DevicePetAssignment>
 */
class DevicePetAssignmentFactory extends Factory
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
            'relationship_type' => 'assigned',
            'identification_method' => 'manual',
            'confidence' => DeviceConfidence::High,
            'is_primary' => true,
        ];
    }
}

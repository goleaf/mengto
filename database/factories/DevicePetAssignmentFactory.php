<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceConfidence;
use App\Models\DevicePetAssignment;
use App\Models\SmartDevice;

/**
 * @extends ApplicationFactory<DevicePetAssignment>
 */
class DevicePetAssignmentFactory extends ApplicationFactory
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

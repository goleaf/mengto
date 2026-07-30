<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeviceLifecycleKind;
use App\Enums\DeviceLifecycleStatus;
use App\Models\DeviceLifecycleRecord;
use App\Models\SmartDevice;

/**
 * @extends ApplicationFactory<DeviceLifecycleRecord>
 */
final class DeviceLifecycleRecordFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'smart_device_id' => SmartDevice::factory(),
            'kind' => DeviceLifecycleKind::Maintenance,
            'status' => DeviceLifecycleStatus::Pending,
            'created_by_key' => 'mia-carter',
            'severity' => 'normal',
            'details' => ['note' => fake()->sentence()],
            'effective_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => DeviceLifecycleStatus::Completed,
            'resolved_at' => now(),
        ]);
    }

    public function criticalRecall(): static
    {
        return $this->state(fn (): array => [
            'kind' => DeviceLifecycleKind::Recall,
            'status' => DeviceLifecycleStatus::Pending,
            'severity' => 'critical',
        ]);
    }
}

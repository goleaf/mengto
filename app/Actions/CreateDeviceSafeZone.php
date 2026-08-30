<?php

namespace App\Actions;

use App\Enums\DeviceType;
use App\Models\AuditLog;
use App\Models\DeviceSafeZone;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateDeviceSafeZone
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(SmartDevice $device, array $data): DeviceSafeZone
    {
        if ($device->type !== DeviceType::GpsTracker) {
            throw ValidationException::withMessages([
                'name' => __('messages.safe_zones_can_only_be_added_to_a_gps_tracker'),
            ]);
        }

        return DB::transaction(function () use ($device, $data): DeviceSafeZone {
            $zone = $device->safeZones()->create([
                'name' => $data['name'],
                'shape' => $data['shape'],
                'public_area_label' => $data['public_area_label'],
                'exact_geometry' => [
                    'latitude' => (float) $data['latitude'],
                    'longitude' => (float) $data['longitude'],
                    'radius_meters' => isset($data['radius_meters'])
                        ? (float) $data['radius_meters']
                        : null,
                ],
                'schedule' => [
                    'always_active' => (bool) ($data['always_active'] ?? false),
                    'timezone' => 'Europe/Vilnius',
                ],
                'exit_delay_seconds' => $data['exit_delay_seconds'],
                'accuracy_threshold_meters' => $data['accuracy_threshold_meters'],
                'status' => 'active',
                'is_home' => (bool) ($data['is_home'] ?? false),
            ]);

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'device-safe-zone.created',
                'target_type' => DeviceSafeZone::class,
                'target_id' => (string) $zone->id,
                'metadata' => [
                    'smart_device_id' => $device->id,
                    'shape' => $zone->shape,
                    'is_home' => $zone->is_home,
                    'public_area_label' => $zone->public_area_label,
                ],
            ]);

            return $zone;
        });
    }
}

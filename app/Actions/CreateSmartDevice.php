<?php

namespace App\Actions;

use App\Enums\DeviceConnectionStatus;
use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\AuditLog;
use App\Models\SmartDevice;
use App\Services\ForumActor;
use App\Services\ProfilePresenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateSmartDevice
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly ProfilePresenter $profiles,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): SmartDevice
    {
        return DB::transaction(function () use ($data): SmartDevice {
            $pets = collect($data['pet_profile_keys'])
                ->unique()
                ->map(function (string $key): array {
                    $pet = $this->profiles->pet($key);

                    if ($pet === null) {
                        throw ValidationException::withMessages([
                            'pet_profile_keys' => __('messages.one_selected_pet_profile_is_unavailable'),
                        ]);
                    }

                    return ['key' => $key, 'name' => $pet['name']];
                })
                ->values();

            $type = DeviceType::from($data['type']);
            $device = SmartDevice::query()->create([
                'owner_key' => $this->actor->key(),
                'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(6)),
                'name' => $data['name'],
                'type' => $type,
                'brand' => $data['brand'] ?? null,
                'model' => $data['model'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'public_zone_label' => $data['public_zone_label'] ?? null,
                'private_location_label' => $data['private_location_label'] ?? null,
                'privacy' => 'private',
                'status' => DeviceStatus::Active,
                'connection_status' => DeviceConnectionStatus::Connecting,
                'operating_mode' => 'normal',
                'connection_type' => $data['connection_type'] ?? 'manual',
                'firmware_version' => $data['firmware_version'] ?? null,
                'battery_percent' => $data['battery_percent'] ?? null,
                'has_backup_power' => (bool) ($data['has_backup_power'] ?? false),
                'supports_local_operation' => (bool) ($data['supports_local_operation'] ?? false),
                'requires_cloud' => (bool) ($data['requires_cloud'] ?? true),
                'is_medical_device' => (bool) ($data['is_medical_device'] ?? false),
            ]);

            foreach ($pets as $index => $pet) {
                $device->assignments()->create([
                    'pet_profile_key' => $pet['key'],
                    'pet_name' => $pet['name'],
                    'relationship_type' => $pets->count() === 1 ? 'assigned' : 'shared',
                    'identification_method' => 'manual',
                    'confidence' => 'high',
                    'is_primary' => $index === 0,
                ]);
            }

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'device-owner',
                'action' => 'smart-device.created',
                'target_type' => SmartDevice::class,
                'target_id' => (string) $device->id,
                'metadata' => [
                    'type' => $type->value,
                    'pet_profile_keys' => $pets->pluck('key')->all(),
                    'connection_type' => $device->connection_type,
                    'privacy' => 'private',
                ],
            ]);

            return $device;
        });
    }
}

<?php

namespace App\Actions;

use App\Enums\DeviceEventSeverity;
use App\Models\AuditLog;
use App\Models\DevicePetAssignment;
use App\Models\DeviceReading;
use App\Models\SmartDevice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordDeviceReading
{
    /** @param array<string, mixed> $data */
    public function handle(SmartDevice $device, array $data): DeviceReading
    {
        return DB::transaction(function () use ($device, $data): DeviceReading {
            $existing = DeviceReading::query()
                ->select(['id', 'smart_device_id'])
                ->where('smart_device_id', $device->id)
                ->where('external_event_id', $data['external_event_id'])
                ->first();

            if ($existing !== null) {
                return DeviceReading::query()->findOrFail($existing->id);
            }

            $lockedDevice = SmartDevice::query()
                ->select([
                    'id', 'owner_key', 'name', 'type', 'status',
                    'connection_status', 'battery_percent', 'last_seen_at',
                    'last_synced_at', 'last_location_at', 'current_latitude',
                    'current_longitude', 'location_accuracy_meters',
                    'lock_version',
                ])
                ->lockForUpdate()
                ->findOrFail($device->id);
            $assignments = DevicePetAssignment::query()
                ->select([
                    'id', 'smart_device_id', 'pet_profile_key', 'pet_name',
                    'confidence', 'is_primary',
                ])
                ->where('smart_device_id', $device->id)
                ->get();
            $assignment = $this->assignment($assignments, $data['pet_profile_key'] ?? null);
            $recordedAt = CarbonImmutable::parse($data['recorded_at'], $data['timezone']);

            $reading = DeviceReading::query()->create([
                'smart_device_id' => $lockedDevice->id,
                'device_pet_assignment_id' => $assignment?->id,
                'pet_profile_key' => $assignment?->pet_profile_key,
                'pet_name' => $assignment?->pet_name,
                'external_event_id' => $data['external_event_id'],
                'metric_type' => $data['metric_type'],
                'numeric_value' => $data['numeric_value'] ?? null,
                'text_value' => $data['text_value'] ?? null,
                'unit' => $data['unit'] ?? null,
                'recorded_at' => $recordedAt,
                'timezone' => $data['timezone'],
                'accuracy_meters' => $data['accuracy_meters'] ?? null,
                'confidence' => $data['confidence'],
                'verification_status' => 'device-unverified',
                'original_payload' => $data['original_payload'] ?? [],
                'processed_payload' => $data['processed_payload'] ?? [],
                'is_stale' => (bool) ($data['is_stale'] ?? false),
            ]);

            $this->refreshDeviceState($lockedDevice, $reading);
            $this->recordEvent($lockedDevice, $reading);
            $lockedDevice->increment('lock_version');

            AuditLog::query()->create([
                'actor_key' => 'device-'.$lockedDevice->id,
                'actor_role' => 'connected-device',
                'action' => 'device-reading.recorded',
                'target_type' => DeviceReading::class,
                'target_id' => (string) $reading->id,
                'metadata' => [
                    'smart_device_id' => $lockedDevice->id,
                    'metric_type' => $reading->metric_type,
                    'pet_profile_key' => $reading->pet_profile_key,
                    'confidence' => $reading->confidence->value,
                    'verification_status' => $reading->verification_status,
                ],
            ]);

            return $reading;
        });
    }

    /** @param Collection<int, DevicePetAssignment> $assignments */
    private function assignment(
        Collection $assignments,
        ?string $petProfileKey,
    ): ?DevicePetAssignment {
        if ($petProfileKey !== null) {
            $assignment = $assignments->firstWhere('pet_profile_key', $petProfileKey);

            if ($assignment === null) {
                throw ValidationException::withMessages([
                    'pet_profile_key' => 'This pet is not assigned to the device.',
                ]);
            }

            return $assignment;
        }

        return $assignments->count() === 1 ? $assignments->first() : null;
    }

    private function refreshDeviceState(SmartDevice $device, DeviceReading $reading): void
    {
        $updates = [
            'last_seen_at' => $reading->recorded_at,
            'last_synced_at' => now(),
            'connection_status' => 'online',
        ];

        if ($reading->metric_type === 'battery-percent' && $reading->numeric_value !== null) {
            $updates['battery_percent'] = max(0, min(100, (int) $reading->numeric_value));
        }

        if ($reading->metric_type === 'location') {
            $payload = $reading->original_payload ?? [];
            $updates += [
                'last_location_at' => $reading->recorded_at,
                'current_latitude' => isset($payload['latitude'])
                    ? (string) $payload['latitude']
                    : null,
                'current_longitude' => isset($payload['longitude'])
                    ? (string) $payload['longitude']
                    : null,
                'location_accuracy_meters' => $reading->accuracy_meters,
            ];
        }

        $device->forceFill($updates)->save();
    }

    private function recordEvent(SmartDevice $device, DeviceReading $reading): void
    {
        $event = match ($reading->metric_type) {
            'food-dispensed' => [
                'type' => 'food-dispensed',
                'title' => 'Portion dispensed; eating not confirmed',
                'severity' => DeviceEventSeverity::Routine,
            ],
            'litter-visit' => [
                'type' => 'litter-visit',
                'title' => 'Litter box visit detected',
                'severity' => DeviceEventSeverity::Routine,
            ],
            'water-use' => [
                'type' => 'water-use',
                'title' => 'Water use detected; intake not confirmed',
                'severity' => DeviceEventSeverity::Routine,
            ],
            default => null,
        };

        if ($event === null) {
            return;
        }

        $device->events()->create([
            'device_pet_assignment_id' => $reading->device_pet_assignment_id,
            'pet_profile_key' => $reading->pet_profile_key,
            'pet_name' => $reading->pet_name,
            'external_event_id' => 'reading:'.$reading->external_event_id,
            'type' => $event['type'],
            'severity' => $event['severity'],
            'status' => 'open',
            'title' => $event['title'],
            'summary' => 'Automatic device event. Confirm the real-world outcome before using it as a care fact.',
            'details' => [
                'reading_id' => $reading->id,
                'numeric_value' => $reading->numeric_value,
                'unit' => $reading->unit,
            ],
            'occurred_at' => $reading->recorded_at,
            'timezone' => $reading->timezone,
            'confidence' => $reading->confidence,
            'source' => 'device-reading',
            'requires_attention' => false,
        ]);
    }
}

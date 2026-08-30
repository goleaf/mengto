<?php

declare(strict_types=1);

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
    public function __construct(
        private readonly EnforceDeviceRetention $enforceRetention,
    ) {}

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
                    'location_retention_days', 'telemetry_retention_days',
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
            $pruned = $this->enforceRetention->handle($lockedDevice, $reading->id);
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
                    'retention_pruned' => $pruned,
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
                    'pet_profile_key' => __('messages.this_pet_is_not_assigned_to_the_device'),
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
                'title' => __('messages.portion_dispensed_eating_unconfirmed'),
                'severity' => DeviceEventSeverity::Routine,
            ],
            'litter-visit' => [
                'type' => 'litter-visit',
                'title' => __('messages.litter_box_visit_detected'),
                'severity' => DeviceEventSeverity::Routine,
            ],
            'water-use' => [
                'type' => 'water-use',
                'title' => __('messages.water_use_intake_unconfirmed'),
                'severity' => DeviceEventSeverity::Routine,
            ],
            default => null,
        };

        if ($event === null) {
            return;
        }

        if ($reading->is_stale && $this->groupStaleEvent($device, $reading, $event['type'])) {
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
            'occurrence_count' => 1,
            'first_occurred_at' => $reading->recorded_at,
            'last_occurred_at' => $reading->recorded_at,
            'title' => $event['title'],
            'summary' => __('messages.automatic_device_event_confirm_the_real_world_outcome_before_using_it_as_a_care_fact'),
            'details' => [
                'reading_id' => $reading->id,
                'reading_ids' => [$reading->id],
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

    private function groupStaleEvent(
        SmartDevice $device,
        DeviceReading $reading,
        string $eventType,
    ): bool {
        $existing = $device->events()
            ->select([
                'id', 'smart_device_id', 'type', 'status', 'occurrence_count',
                'first_occurred_at', 'last_occurred_at', 'occurred_at',
                'details',
            ])
            ->where('type', $eventType)
            ->where('status', 'open')
            ->where('last_occurred_at', '>=', $reading->recorded_at->copy()->subHour())
            ->where('last_occurred_at', '<=', $reading->recorded_at)
            ->latest('last_occurred_at')
            ->lockForUpdate()
            ->first();

        if ($existing === null) {
            return false;
        }

        $readingIds = collect($existing->details['reading_ids'] ?? [])
            ->push($reading->id)
            ->unique()
            ->take(-20)
            ->values()
            ->all();

        $existing->forceFill([
            'occurrence_count' => $existing->occurrence_count + 1,
            'last_occurred_at' => $reading->recorded_at,
            'occurred_at' => $reading->recorded_at,
            'details' => [
                ...($existing->details ?? []),
                'reading_ids' => $readingIds,
                'grouped_after_reconnect' => true,
            ],
        ])->save();

        return true;
    }
}

<?php

namespace App\Actions;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareSourceType;
use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\DeviceEvent;
use App\Models\SmartDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PromoteDeviceEventToCareJournal
{
    public function __construct(private readonly CreateCareEntry $createCareEntry) {}

    public function handle(SmartDevice $device, DeviceEvent $event): CareEntry
    {
        return DB::transaction(function () use ($device, $event): CareEntry {
            $locked = DeviceEvent::query()
                ->select([
                    'id', 'smart_device_id', 'pet_profile_key', 'pet_name',
                    'type', 'title', 'summary', 'details', 'occurred_at',
                    'timezone', 'care_entry_id',
                ])
                ->lockForUpdate()
                ->findOrFail($event->id);

            if ($locked->smart_device_id !== $device->id) {
                throw ValidationException::withMessages([
                    'event' => 'This event does not belong to the selected device.',
                ]);
            }

            if ($locked->care_entry_id !== null) {
                return CareEntry::query()->findOrFail($locked->care_entry_id);
            }

            if ($locked->pet_profile_key === null) {
                throw ValidationException::withMessages([
                    'event' => 'Choose the pet before adding this shared-device event to care.',
                ]);
            }

            $journal = CareJournal::query()
                ->select([
                    'id', 'owner_key', 'slug', 'pet_profile_key', 'pet_name',
                    'timezone', 'status', 'last_feeding_at', 'last_water_at',
                    'last_walk_at', 'last_toilet_at', 'lock_version',
                ])
                ->where('owner_key', $device->owner_key)
                ->where('pet_profile_key', $locked->pet_profile_key)
                ->first();

            if ($journal === null) {
                throw ValidationException::withMessages([
                    'event' => 'Create this pet’s care journal before adding the event.',
                ]);
            }

            $type = match ($locked->type) {
                'food-dispensed' => CareEntryType::Feeding,
                'water-use' => CareEntryType::Water,
                'litter-visit' => CareEntryType::Toilet,
                'walk-detected' => CareEntryType::Walk,
                'sleep-detected' => CareEntryType::Sleep,
                default => CareEntryType::Observation,
            };
            $details = $locked->details ?? [];

            $entry = $this->createCareEntry->handle($journal, [
                'idempotency_key' => (string) Str::uuid(),
                'entry_type' => $type->value,
                'subtype' => 'device-event',
                'title' => $locked->title,
                'started_at' => $locked->occurred_at,
                'ended_at' => null,
                'status' => CareEntryStatus::NeedsReview->value,
                'source_type' => CareSourceType::Device->value,
                'source_name' => $device->name,
                'quantity_value' => $details['portion_grams']
                    ?? $details['numeric_value']
                    ?? null,
                'quantity_unit' => isset($details['portion_grams'])
                    ? 'g'
                    : ($details['unit'] ?? null),
                'duration_minutes' => null,
                'distance_meters' => null,
                'appetite' => null,
                'intensity' => null,
                'notes' => $locked->summary,
                'is_unusual' => false,
                'confirm_duplicate' => false,
            ], [
                'key' => 'device-'.$device->id,
                'name' => $device->name,
                'role' => CareSourceType::Device->value,
            ]);

            $locked->forceFill(['care_entry_id' => $entry->id])->save();

            return $entry;
        });
    }
}

<?php

namespace App\Actions;

use App\Models\DeviceReading;
use App\Models\MedicalEvent;
use App\Models\MedicalRecord;
use App\Models\SmartDevice;
use App\Models\WeightEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PromoteDeviceReadingToMedicalRecord
{
    public function __construct(private readonly CreateMedicalEntry $createMedicalEntry) {}

    public function handle(SmartDevice $device, DeviceReading $reading): Model
    {
        return DB::transaction(function () use ($device, $reading): Model {
            $locked = DeviceReading::query()
                ->select([
                    'id', 'smart_device_id', 'pet_profile_key', 'pet_name',
                    'metric_type', 'numeric_value', 'text_value', 'unit',
                    'recorded_at', 'timezone', 'confidence',
                    'verification_status', 'medical_event_id', 'weight_entry_id',
                ])
                ->lockForUpdate()
                ->findOrFail($reading->id);

            if ($locked->smart_device_id !== $device->id) {
                throw ValidationException::withMessages([
                    'reading' => __('messages.this_reading_does_not_belong_to_the_selected_device_9eed5b281e'),
                ]);
            }

            if ($locked->weight_entry_id !== null) {
                return WeightEntry::query()->findOrFail($locked->weight_entry_id);
            }

            if ($locked->medical_event_id !== null) {
                return MedicalEvent::query()->findOrFail($locked->medical_event_id);
            }

            if ($locked->pet_profile_key === null) {
                throw ValidationException::withMessages([
                    'reading' => __('messages.choose_the_pet_before_adding_this_shared_device_reading_e757b00ba0'),
                ]);
            }

            $record = MedicalRecord::query()
                ->select([
                    'id', 'owner_key', 'pet_profile_key', 'pet_name',
                    'timezone', 'current_weight_grams', 'status', 'lock_version',
                ])
                ->where('owner_key', $device->owner_key)
                ->where('pet_profile_key', $locked->pet_profile_key)
                ->first();

            if ($record === null) {
                throw ValidationException::withMessages([
                    'reading' => __('messages.create_this_pet_s_health_record_before_adding_the_readin_8cd759dd90'),
                ]);
            }

            if (
                $locked->metric_type === 'weight-grams'
                && $locked->numeric_value !== null
            ) {
                $entry = $this->createMedicalEntry->handle($record, [
                    'entry_type' => 'weight',
                    'weight' => (float) $locked->numeric_value,
                    'weight_unit' => 'g',
                    'tare' => null,
                    'measured_at' => $locked->recorded_at,
                    'source_type' => 'device',
                    'source_name' => $device->name,
                    'measurement_context' => __('messages.connected_device_owner_promoted_for_review_884eff73b0'),
                    'notes' => __('messages.non_clinical_device_reading_confirm_calibration_and_cond_a3086fcbae'),
                ]);
                $locked->forceFill(['weight_entry_id' => $entry->getKey()])->save();

                return $entry;
            }

            $value = $locked->numeric_value ?? $locked->text_value ?? 'not reported';
            $entry = $this->createMedicalEntry->handle($record, [
                'entry_type' => 'event',
                'event_type' => 'note',
                'title' => str($locked->metric_type)->headline()->append(' device reading')->toString(),
                'occurred_at' => $locked->recorded_at,
                'event_status' => 'active',
                'source_type' => 'device',
                'source_name' => $device->name,
                'source_reference' => 'device-reading:'.$locked->id,
                'summary' => sprintf(
                    '%s %s. Non-clinical device reading promoted by the owner for review.',
                    $value,
                    $locked->unit ?? '',
                ),
                'severity' => 'not-assessed',
                'next_step' => __('messages.verify_the_device_context_and_interpretation_with_a_vete_3724cd9832'),
                'is_critical' => false,
            ]);
            $locked->forceFill(['medical_event_id' => $entry->getKey()])->save();

            return $entry;
        });
    }
}

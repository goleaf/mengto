<?php

namespace App\Actions;

use App\Enums\MedicationDoseStatus;
use App\Models\AuditLog;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\MedicationDose;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordMedicationDose
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(MedicalRecord $record, Medication $medication, array $data): MedicationDose
    {
        return DB::transaction(function () use ($record, $medication, $data): MedicationDose {
            $existingByKey = MedicationDose::query()
                ->select(['id', 'medical_record_id', 'medication_id'])
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existingByKey !== null) {
                if ($existingByKey->medical_record_id !== $record->id
                    || $existingByKey->medication_id !== $medication->id) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('messages.this_dose_submission_key_is_already_in_use_7f9d7e9c37'),
                    ]);
                }

                return $existingByKey;
            }

            $lockedMedication = Medication::query()
                ->select([
                    'id', 'medical_record_id', 'name', 'dose', 'status',
                    'timezone', 'remaining_quantity',
                ])
                ->lockForUpdate()
                ->findOrFail($medication->id);

            if ($lockedMedication->medical_record_id !== $record->id) {
                throw ValidationException::withMessages([
                    'medication_id' => __('messages.this_medication_does_not_belong_to_the_selected_medical__e0e36cb34b'),
                ]);
            }

            if (! $lockedMedication->status->isDoseable()) {
                throw ValidationException::withMessages([
                    'medication_id' => __('messages.this_medication_is_not_active_check_the_current_veterina_d487e8874b'),
                ]);
            }

            $existingSlot = MedicationDose::query()
                ->select([
                    'id', 'medication_id', 'scheduled_for', 'status',
                    'administered_at', 'administered_by_name',
                ])
                ->where('medication_id', $lockedMedication->id)
                ->where('scheduled_for', $data['scheduled_for'])
                ->first();

            if ($existingSlot !== null) {
                throw ValidationException::withMessages([
                    'scheduled_for' => sprintf(
                        __('messages.this_dose_slot_is_already_marked_s_by_s_4dcbe77c01'),
                        $existingSlot->status->label(),
                        $existingSlot->administered_by_name,
                    ),
                ]);
            }

            $status = MedicationDoseStatus::from($data['status']);
            $identity = $this->actor->identity();
            $dose = MedicationDose::query()->create([
                'medical_record_id' => $record->id,
                'medication_id' => $lockedMedication->id,
                'idempotency_key' => $data['idempotency_key'],
                'scheduled_for' => $data['scheduled_for'],
                'administered_at' => $status === MedicationDoseStatus::Missed
                    ? null
                    : ($data['administered_at'] ?? now()),
                'timezone' => $record->timezone,
                'status' => $status,
                'dose_given' => $data['dose_given'] ?? $lockedMedication->dose,
                'administered_by_key' => $identity['key'],
                'administered_by_name' => $identity['name'],
                'notes' => $data['notes'] ?? null,
            ]);

            $record->increment('lock_version');

            AuditLog::query()->create([
                'actor_key' => $identity['key'],
                'actor_role' => 'medication-caregiver',
                'action' => 'medication-dose.recorded',
                'target_type' => MedicationDose::class,
                'target_id' => (string) $dose->id,
                'metadata' => [
                    'medical_record_id' => $record->id,
                    'medication_id' => $medication->id,
                    'status' => $status->value,
                    'scheduled_for' => $dose->scheduled_for?->toAtomString(),
                ],
            ]);

            return $dose;
        });
    }
}

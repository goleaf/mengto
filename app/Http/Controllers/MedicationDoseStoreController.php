<?php

namespace App\Http\Controllers;

use App\Actions\RecordMedicationDose;
use App\Http\Requests\StoreMedicationDoseRequest;
use App\Models\MedicalRecord;
use App\Models\Medication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MedicationDoseStoreController extends Controller
{
    public function __invoke(
        StoreMedicationDoseRequest $request,
        MedicalRecord $medicalRecord,
        RecordMedicationDose $recordDose,
    ): RedirectResponse {
        Gate::authorize('update', $medicalRecord);
        $data = $request->validated();
        $medication = Medication::query()
            ->select([
                'id', 'medical_record_id', 'name', 'dose', 'status',
                'timezone', 'remaining_quantity',
            ])
            ->findOrFail($data['medication_id']);
        $recordDose->handle($medicalRecord, $medication, $data);

        return to_route('medical-records.show', $medicalRecord)
            ->with('feedback', __('messages.medication_outcome_recorded_other_caregivers_will_see_this_dose_slot_as_handled'));
    }
}

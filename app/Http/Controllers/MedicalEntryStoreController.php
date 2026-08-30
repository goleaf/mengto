<?php

namespace App\Http\Controllers;

use App\Actions\CreateMedicalEntry;
use App\Http\Requests\StoreMedicalEntryRequest;
use App\Models\MedicalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MedicalEntryStoreController extends Controller
{
    public function __invoke(
        StoreMedicalEntryRequest $request,
        MedicalRecord $medicalRecord,
        CreateMedicalEntry $create,
    ): RedirectResponse {
        Gate::authorize('update', $medicalRecord);
        $create->handle($medicalRecord, $request->validated());

        return to_route('medical-records.manage', $medicalRecord)
            ->with('feedback', __('messages.health_entry_saved_with_its_source_and_verification_status'));
    }
}

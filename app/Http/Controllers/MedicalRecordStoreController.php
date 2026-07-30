<?php

namespace App\Http\Controllers;

use App\Actions\CreateMedicalRecord;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Models\MedicalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MedicalRecordStoreController extends Controller
{
    public function __invoke(
        StoreMedicalRecordRequest $request,
        CreateMedicalRecord $create,
    ): RedirectResponse {
        Gate::authorize('create', MedicalRecord::class);
        $record = $create->handle($request->validated());

        return to_route('medical-records.show', $record)
            ->with('feedback', __('messages.private_medical_record_created_nothing_was_added_to_the__5e3864e478'));
    }
}

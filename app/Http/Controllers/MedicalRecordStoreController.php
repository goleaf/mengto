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
            ->with('feedback', 'Private medical record created. Nothing was added to the public pet profile.');
    }
}

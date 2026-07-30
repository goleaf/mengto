<?php

namespace App\Http\Controllers;

use App\Actions\StoreMedicalDocument;
use App\Http\Requests\StoreMedicalDocumentRequest;
use App\Models\MedicalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MedicalDocumentStoreController extends Controller
{
    public function __invoke(
        StoreMedicalDocumentRequest $request,
        MedicalRecord $medicalRecord,
        StoreMedicalDocument $store,
    ): RedirectResponse {
        Gate::authorize('update', $medicalRecord);
        $store->handle($medicalRecord, $request->validated());

        return to_route('medical-records.manage', $medicalRecord)
            ->with('feedback', __('messages.document_stored_privately_the_original_file_remains_avai_0f22068033'));
    }
}

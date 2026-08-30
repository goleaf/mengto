<?php

namespace App\Http\Controllers;

use App\Actions\CreateMedicalAccessGrant;
use App\Http\Requests\StoreMedicalAccessRequest;
use App\Models\MedicalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MedicalAccessStoreController extends Controller
{
    public function __invoke(
        StoreMedicalAccessRequest $request,
        MedicalRecord $medicalRecord,
        CreateMedicalAccessGrant $create,
    ): RedirectResponse {
        Gate::authorize('share', $medicalRecord);
        $result = $create->handle($medicalRecord, $request->validated());
        $url = route('medical-access.show', ['token' => $result['token']]);

        return to_route('medical-records.manage', $medicalRecord)
            ->with('feedback', __('messages.temporary_access_created_this_link_is_shown_once_and_expires_automatically'))
            ->with('medical_access_url', $url);
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\RevokeMedicalAccess;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MedicalAccessRevokeController extends Controller
{
    public function __invoke(
        MedicalRecord $medicalRecord,
        MedicalAccessGrant $medicalAccessGrant,
        RevokeMedicalAccess $revoke,
    ): RedirectResponse {
        Gate::authorize('share', $medicalRecord);
        $revoke->handle($medicalRecord, $medicalAccessGrant);

        return to_route('medical-records.manage', $medicalRecord)
            ->with('feedback', 'Access revoked. Cached portal views and the temporary link can no longer open the record.');
    }
}

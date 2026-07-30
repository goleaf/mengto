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
            ->with('feedback', __('messages.access_revoked_cached_portal_views_and_the_temporary_lin_eabd46297d'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\RevokeCareAccess;
use App\Models\CareAccessGrant;
use App\Models\CareJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CareAccessRevokeController extends Controller
{
    public function __invoke(
        CareJournal $careJournal,
        CareAccessGrant $careAccessGrant,
        RevokeCareAccess $revoke,
    ): RedirectResponse {
        Gate::authorize('share', $careJournal);
        $revoke->handle($careJournal, $careAccessGrant);

        return to_route('care-journals.manage', $careJournal)
            ->with('feedback', __('messages.temporary_care_access_revoked_immediately'));
    }
}

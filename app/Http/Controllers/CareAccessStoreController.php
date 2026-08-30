<?php

namespace App\Http\Controllers;

use App\Actions\CreateCareAccessGrant;
use App\Http\Requests\StoreCareAccessRequest;
use App\Models\CareJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CareAccessStoreController extends Controller
{
    public function __invoke(
        StoreCareAccessRequest $request,
        CareJournal $careJournal,
        CreateCareAccessGrant $create,
    ): RedirectResponse {
        Gate::authorize('share', $careJournal);
        $result = $create->handle($careJournal, $request->validated());
        $url = route('care-access.show', ['token' => $result['token']]);

        return to_route('care-journals.manage', $careJournal)
            ->with('feedback', __('messages.temporary_care_access_created_the_link_is_shown_once_and_expires_automatically'))
            ->with('care_access_url', $url);
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\CreateCareTask;
use App\Http\Requests\StoreCareTaskRequest;
use App\Models\CareJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CareTaskStoreController extends Controller
{
    public function __invoke(
        StoreCareTaskRequest $request,
        CareJournal $careJournal,
        CreateCareTask $create,
    ): RedirectResponse {
        Gate::authorize('update', $careJournal);
        $create->handle($careJournal, $request->validated());

        return to_route('care-journals.manage', $careJournal)
            ->with('feedback', __('messages.care_task_scheduled_and_assigned'));
    }
}

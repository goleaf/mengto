<?php

namespace App\Http\Controllers;

use App\Actions\CreateCareRoutine;
use App\Http\Requests\StoreCareRoutineRequest;
use App\Models\CareJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CareRoutineStoreController extends Controller
{
    public function __invoke(
        StoreCareRoutineRequest $request,
        CareJournal $careJournal,
        CreateCareRoutine $create,
    ): RedirectResponse {
        Gate::authorize('update', $careJournal);
        $create->handle($careJournal, $request->validated());

        return to_route('care-journals.manage', $careJournal)
            ->with('feedback', 'Routine saved as a versioned private care plan.');
    }
}

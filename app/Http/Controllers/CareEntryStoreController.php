<?php

namespace App\Http\Controllers;

use App\Actions\CreateCareEntry;
use App\Http\Requests\StoreCareEntryRequest;
use App\Models\CareJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CareEntryStoreController extends Controller
{
    public function __invoke(
        StoreCareEntryRequest $request,
        CareJournal $careJournal,
        CreateCareEntry $create,
    ): RedirectResponse {
        Gate::authorize('update', $careJournal);
        $create->handle($careJournal, $request->validated());

        return to_route('care-journals.show', $careJournal)
            ->with('feedback', 'Care action recorded with its author, source, and exact time.');
    }
}

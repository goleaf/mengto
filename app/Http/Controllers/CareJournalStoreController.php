<?php

namespace App\Http\Controllers;

use App\Actions\CreateCareJournal;
use App\Http\Requests\StoreCareJournalRequest;
use App\Models\CareJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CareJournalStoreController extends Controller
{
    public function __invoke(
        StoreCareJournalRequest $request,
        CreateCareJournal $create,
    ): RedirectResponse {
        Gate::authorize('create', CareJournal::class);
        $journal = $create->handle($request->validated());

        return to_route('care-journals.show', $journal)
            ->with('feedback', 'Private care journal created. Nothing was added to the public pet profile.');
    }
}

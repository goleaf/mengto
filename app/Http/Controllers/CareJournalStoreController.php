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
            ->with('feedback', __('messages.private_care_journal_created_nothing_was_added_to_the_public_pet_profile'));
    }
}

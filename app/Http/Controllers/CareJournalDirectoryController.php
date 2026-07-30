<?php

namespace App\Http\Controllers;

use App\Models\CareJournal;
use App\Services\CareJournalPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class CareJournalDirectoryController extends Controller
{
    public function __invoke(CareJournalPresenter $presenter): View
    {
        Gate::authorize('viewAny', CareJournal::class);

        return view('care-journals.index', $presenter->directory());
    }
}

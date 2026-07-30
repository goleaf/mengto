<?php

namespace App\Http\Controllers;

use App\Models\CareJournal;
use App\Services\CareJournalPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class CareJournalController extends Controller
{
    public function __invoke(
        CareJournal $careJournal,
        CareJournalPresenter $presenter,
    ): View {
        Gate::authorize('view', $careJournal);

        return view('care-journals.show', $presenter->show($careJournal));
    }
}

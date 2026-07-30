<?php

namespace App\Http\Controllers;

use App\Models\CareJournal;
use App\Services\CareJournalPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class CareJournalManageController extends Controller
{
    public function __invoke(
        CareJournal $careJournal,
        CareJournalPresenter $presenter,
    ): View {
        Gate::authorize('update', $careJournal);

        return view('care-journals.manage', $presenter->show($careJournal, true));
    }
}

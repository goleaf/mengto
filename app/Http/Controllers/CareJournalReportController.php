<?php

namespace App\Http\Controllers;

use App\Http\Requests\CareReportRequest;
use App\Models\CareJournal;
use App\Services\CareJournalPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class CareJournalReportController extends Controller
{
    public function __invoke(
        CareReportRequest $request,
        CareJournal $careJournal,
        CareJournalPresenter $presenter,
    ): View {
        Gate::authorize('export', $careJournal);

        return view(
            'care-journals.report',
            $presenter->report($careJournal, $request->validated()),
        );
    }
}

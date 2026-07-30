<?php

namespace App\Http\Controllers;

use App\Models\CareJournal;
use App\Services\CareJournalPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class CareJournalCreateController extends Controller
{
    public function __invoke(CareJournalPresenter $presenter): View
    {
        Gate::authorize('create', CareJournal::class);

        return view('care-journals.create', $presenter->editor());
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\ResolveCareAccess;
use App\Services\CareJournalPresenter;
use Illuminate\Contracts\View\View;

class CareSharedJournalController extends Controller
{
    public function __invoke(
        string $token,
        ResolveCareAccess $resolve,
        CareJournalPresenter $presenter,
    ): View {
        $grant = $resolve->handle($token);

        return view('care-journals.shared', $presenter->shared($grant, $token));
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;

class ExpertDashboardController extends Controller
{
    public function __invoke(ExpertPresenter $presenter): View
    {
        return view('experts.dashboard', $presenter->dashboard());
    }
}

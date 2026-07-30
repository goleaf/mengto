<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseExpertsRequest;
use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;

class ExpertDirectoryController extends Controller
{
    public function __invoke(BrowseExpertsRequest $request, ExpertPresenter $presenter): View
    {
        return view('experts.index', $presenter->directory($request->validated()));
    }
}

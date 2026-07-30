<?php

namespace App\Http\Controllers;

use App\Models\SearchCase;
use App\Services\SearchPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class SearchCoordinationController extends Controller
{
    public function __invoke(SearchCase $searchCase, SearchPresenter $presenter): View
    {
        Gate::authorize('coordinate', $searchCase);

        return view('lost-found.coordinate', $presenter->coordination($searchCase));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseSearchCasesRequest;
use App\Models\SearchCase;
use App\Services\SearchPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class SearchDirectoryController extends Controller
{
    public function __invoke(BrowseSearchCasesRequest $request, SearchPresenter $presenter): View
    {
        Gate::authorize('viewAny', SearchCase::class);

        return view('lost-found.index', $presenter->directory($request->validated()));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrowseListingsRequest;
use App\Models\Listing;
use App\Services\ListingPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ListingDirectoryController extends Controller
{
    public function __invoke(BrowseListingsRequest $request, ListingPresenter $presenter): View
    {
        Gate::authorize('viewAny', Listing::class);

        return view('marketplace.index', $presenter->directory($request->validated()));
    }
}

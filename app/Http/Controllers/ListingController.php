<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Services\ListingPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ListingController extends Controller
{
    public function __invoke(Listing $listing, ListingPresenter $presenter): View
    {
        Gate::authorize('view', $listing);

        return view('marketplace.show', $presenter->listing($listing));
    }
}

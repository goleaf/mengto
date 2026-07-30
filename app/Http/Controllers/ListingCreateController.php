<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Services\ListingPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class ListingCreateController extends Controller
{
    public function __invoke(ListingPresenter $presenter): View
    {
        Gate::authorize('create', Listing::class);

        return view('marketplace.create', $presenter->editor());
    }
}

<?php

namespace App\Http\Controllers;

use App\Actions\CreateListing;
use App\Enums\ListingStatus;
use App\Http\Requests\StoreListingRequest;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ListingStoreController extends Controller
{
    public function __invoke(StoreListingRequest $request, CreateListing $create): RedirectResponse
    {
        Gate::authorize('create', Listing::class);
        $listing = $create->handle($request->validated());

        return to_route('marketplace.show', $listing)
            ->with(
                'feedback',
                $listing->status === ListingStatus::Published
                    ? 'Listing published. Keep conversations and arrangements inside the platform.'
                    : 'Draft saved. Only you can see it.',
            );
    }
}

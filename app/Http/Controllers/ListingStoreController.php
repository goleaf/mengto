<?php

namespace App\Http\Controllers;

use App\Actions\CreateListing;
use App\Enums\ListingStatus;
use App\Enums\ModerationStatus;
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
                    ? __('messages.listing_published_keep_conversations_and_arrangements_in_991918c930')
                    : ($listing->moderation_status === ModerationStatus::Pending
                        ? __('messages.listing_saved_and_sent_for_safety_review_only_you_can_se_e0115ec05f')
                        : __('messages.draft_saved_only_you_can_see_it_6126d8ac6e')),
            );
    }
}

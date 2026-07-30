<?php

namespace App\Http\Controllers;

use App\Actions\CreateListingReview;
use App\Http\Requests\StoreListingReviewRequest;
use App\Models\Listing;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ListingReviewController extends Controller
{
    public function __invoke(
        StoreListingReviewRequest $request,
        Listing $listing,
        Order $order,
        CreateListingReview $createReview,
    ): RedirectResponse {
        abort_unless($order->listing_id === $listing->id, 404);
        Gate::authorize('review', $order);

        $createReview->handle($order, $request->validated());

        return to_route('marketplace.orders.show', [$listing, $order])
            ->with('feedback', __('messages.verified_review_published_8e20119f16'));
    }
}

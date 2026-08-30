<?php

namespace App\Http\Controllers;

use App\Actions\OpenOrderDispute;
use App\Http\Requests\StoreOrderDisputeRequest;
use App\Models\Listing;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class OrderDisputeController extends Controller
{
    public function __invoke(
        StoreOrderDisputeRequest $request,
        Listing $listing,
        Order $order,
        OpenOrderDispute $openDispute,
    ): RedirectResponse {
        abort_unless($order->listing_id === $listing->id, 404);
        Gate::authorize('dispute', $order);

        $openDispute->handle($order, $request->validated());

        return to_route('marketplace.orders.show', [$listing, $order])
            ->with('feedback', __('messages.dispute_opened_protected_payment_is_paused_while_the_evidence_is_reviewed'));
    }
}

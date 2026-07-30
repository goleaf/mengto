<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Order;
use App\Services\OrderPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __invoke(Listing $listing, Order $order, OrderPresenter $presenter): View
    {
        abort_unless($order->listing_id === $listing->id, 404);
        Gate::authorize('view', $order);

        return view('marketplace.orders.show', $presenter->show($listing, $order));
    }
}

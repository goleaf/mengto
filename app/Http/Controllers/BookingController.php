<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    public function __invoke(Booking $booking, ExpertPresenter $presenter): View
    {
        Gate::authorize('view', $booking);

        return view('experts.booking', $presenter->booking($booking));
    }
}

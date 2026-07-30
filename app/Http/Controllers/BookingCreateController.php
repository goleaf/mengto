<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ExpertProfile;
use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

class BookingCreateController extends Controller
{
    public function __invoke(ExpertProfile $expertProfile, ExpertPresenter $presenter): View
    {
        Gate::authorize('view', $expertProfile);
        Gate::authorize('create', Booking::class);

        return view('experts.book', $presenter->bookingForm($expertProfile));
    }
}

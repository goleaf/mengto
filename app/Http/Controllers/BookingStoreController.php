<?php

namespace App\Http\Controllers;

use App\Actions\CreateBooking;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ExpertProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class BookingStoreController extends Controller
{
    public function __invoke(
        StoreBookingRequest $request,
        ExpertProfile $expertProfile,
        CreateBooking $create,
    ): RedirectResponse {
        Gate::authorize('view', $expertProfile);
        Gate::authorize('create', Booking::class);
        $booking = $create->handle($expertProfile, $request->validated());

        return to_route('bookings.show', $booking)
            ->with('feedback', 'Appointment request saved. Call the provider directly if the situation becomes urgent.');
    }
}

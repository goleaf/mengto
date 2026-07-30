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
            ->with('feedback', __('messages.appointment_request_saved_call_the_provider_directly_if__8f5cc5a523'));
    }
}

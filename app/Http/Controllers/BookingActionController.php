<?php

namespace App\Http\Controllers;

use App\Actions\PerformBookingAction;
use App\Http\Requests\PerformBookingActionRequest;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class BookingActionController extends Controller
{
    public function __invoke(
        PerformBookingActionRequest $request,
        Booking $booking,
        PerformBookingAction $perform,
    ): RedirectResponse {
        Gate::authorize('update', $booking);
        $message = $perform->handle($booking, $request->validated());

        return back()->with('feedback', $message);
    }
}

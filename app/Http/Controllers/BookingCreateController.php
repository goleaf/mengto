<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ExpertProfile;
use App\Services\ExpertPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingCreateController extends Controller
{
    public function __invoke(
        Request $request,
        ExpertProfile $expertProfile,
        ExpertPresenter $presenter,
    ): View {
        Gate::authorize('view', $expertProfile);
        Gate::authorize('create', Booking::class);
        $query = $request->validate([
            'service' => ['nullable', 'integer', 'min:1'],
        ]);

        return view('experts.book', [
            ...$presenter->bookingForm($expertProfile),
            'selected_service_id' => isset($query['service']) ? (string) $query['service'] : null,
        ]);
    }
}

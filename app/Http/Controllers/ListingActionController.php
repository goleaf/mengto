<?php

namespace App\Http\Controllers;

use App\Actions\PerformListingAction;
use App\Http\Requests\PerformListingActionRequest;
use App\Models\Listing;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ListingActionController extends Controller
{
    public function __invoke(
        PerformListingActionRequest $request,
        Listing $listing,
        PerformListingAction $perform,
    ): RedirectResponse {
        $data = $request->validated();
        $reservation = $this->reservation($listing, $data);

        if ($reservation !== null) {
            $data['_reservation'] = $reservation;
        }

        match ($data['action']) {
            'request' => Gate::authorize('reserve', $listing),
            'cancel-request' => Gate::authorize('cancelReservation', [$listing, $reservation]),
            'accept-request', 'decline-request', 'mark-complete' => Gate::authorize('update', $listing),
            default => Gate::authorize('view', $listing),
        };

        $result = $perform->handle($listing, $data);

        return to_route('marketplace.show', $result['listing'])
            ->with('feedback', $result['message']);
    }

    /** @param array<string, mixed> $data */
    private function reservation(Listing $listing, array $data): ?Reservation
    {
        if (! isset($data['reservation_id'])) {
            return null;
        }

        return Reservation::query()
            ->select([
                'id', 'listing_id', 'requester_key', 'requester_name', 'status',
                'message', 'exchange_method', 'proposed_at', 'expires_at',
                'responded_at', 'completed_at', 'created_at',
            ])
            ->where('listing_id', $listing->id)
            ->findOrFail((int) $data['reservation_id']);
    }
}

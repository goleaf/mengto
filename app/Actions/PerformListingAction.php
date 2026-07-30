<?php

namespace App\Actions;

use App\Enums\ListingStatus;
use App\Enums\ReservationStatus;
use App\Models\AuditLog;
use App\Models\Listing;
use App\Models\ListingEngagement;
use App\Models\ListingReport;
use App\Models\Reservation;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformListingAction
{
    public function __construct(private readonly ForumActor $actor) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, listing: Listing}
     */
    public function handle(Listing $listing, array $data): array
    {
        return match ($data['action']) {
            'toggle-save' => $this->toggleSave($listing),
            'request' => $this->request($listing, $data),
            'cancel-request' => $this->cancelRequest($listing, $this->reservation($listing, $data)),
            'accept-request' => $this->acceptRequest($listing, $this->reservation($listing, $data)),
            'decline-request' => $this->declineRequest($listing, $this->reservation($listing, $data)),
            'mark-complete' => $this->complete($listing, $this->reservation($listing, $data)),
            'report' => $this->report($listing, $data),
        };
    }

    /** @return array{message: string, listing: Listing} */
    private function toggleSave(Listing $listing): array
    {
        $engagement = ListingEngagement::query()->firstOrCreate(
            ['listing_id' => $listing->id, 'user_key' => $this->actor->key()],
            ['is_saved' => false],
        );

        $engagement->update(['is_saved' => ! $engagement->is_saved]);

        return [
            'message' => $engagement->is_saved ? 'Listing saved.' : 'Listing removed from saved items.',
            'listing' => $listing,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, listing: Listing}
     */
    private function request(Listing $listing, array $data): array
    {
        return DB::transaction(function () use ($listing, $data): array {
            $existing = Reservation::query()
                ->select(['id', 'listing_id', 'requester_key'])
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing !== null) {
                if ($existing->listing_id !== $listing->id || $existing->requester_key !== $this->actor->key()) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => 'This request key is already in use.',
                    ]);
                }

                return ['message' => 'Your request was already received.', 'listing' => $listing];
            }

            $lockedListing = Listing::query()
                ->select(['id', 'owner_key', 'status'])
                ->lockForUpdate()
                ->findOrFail($listing->id);

            if ($lockedListing->owner_key === $this->actor->key()) {
                throw ValidationException::withMessages(['action' => 'You cannot request your own listing.']);
            }

            if ($lockedListing->status !== ListingStatus::Published) {
                throw ValidationException::withMessages(['action' => 'This listing is no longer available.']);
            }

            $hasActiveRequest = Reservation::query()
                ->where('listing_id', $listing->id)
                ->where('requester_key', $this->actor->key())
                ->whereIn('status', [
                    ReservationStatus::Requested->value,
                    ReservationStatus::Accepted->value,
                ])
                ->exists();

            if ($hasActiveRequest) {
                throw ValidationException::withMessages(['action' => 'You already have an active request.']);
            }

            $identity = $this->actor->identity();
            $reservation = Reservation::query()->create([
                'listing_id' => $listing->id,
                'requester_key' => $identity['key'],
                'requester_name' => $identity['name'],
                'idempotency_key' => $data['idempotency_key'],
                'status' => ReservationStatus::Requested,
                'message' => $data['message'],
                'exchange_method' => $data['exchange_method'],
                'proposed_at' => $data['proposed_at'] ?? null,
                'expires_at' => now()->addDays(3),
            ]);

            $this->audit('reservation.requested', $listing, [
                'reservation_id' => $reservation->id,
                'exchange_method' => $reservation->exchange_method,
            ]);

            return ['message' => 'Request sent. Keep payment and contact details inside the platform.', 'listing' => $listing];
        });
    }

    /** @return array{message: string, listing: Listing} */
    private function cancelRequest(Listing $listing, Reservation $reservation): array
    {
        if ($reservation->requester_key !== $this->actor->key()) {
            throw ValidationException::withMessages(['reservation_id' => 'This request does not belong to you.']);
        }

        if (! in_array($reservation->status, [ReservationStatus::Requested, ReservationStatus::Accepted], true)) {
            throw ValidationException::withMessages(['reservation_id' => 'This request can no longer be cancelled.']);
        }

        return DB::transaction(function () use ($listing, $reservation): array {
            $wasAccepted = $reservation->status === ReservationStatus::Accepted;
            $reservation->update([
                'status' => ReservationStatus::Cancelled,
                'responded_at' => now(),
            ]);

            if ($wasAccepted) {
                $listing->update([
                    'status' => ListingStatus::Published,
                    'reserved_at' => null,
                ]);
            }

            $this->audit('reservation.cancelled', $listing, ['reservation_id' => $reservation->id]);

            return ['message' => 'Request cancelled.', 'listing' => $listing];
        });
    }

    /** @return array{message: string, listing: Listing} */
    private function acceptRequest(Listing $listing, Reservation $reservation): array
    {
        if ($reservation->status !== ReservationStatus::Requested) {
            throw ValidationException::withMessages(['reservation_id' => 'Only pending requests can be accepted.']);
        }

        return DB::transaction(function () use ($listing, $reservation): array {
            $lockedListing = Listing::query()
                ->select(['id', 'status'])
                ->lockForUpdate()
                ->findOrFail($listing->id);

            if ($lockedListing->status !== ListingStatus::Published) {
                throw ValidationException::withMessages(['action' => 'This listing is already reserved or completed.']);
            }

            $reservation->update([
                'status' => ReservationStatus::Accepted,
                'responded_at' => now(),
            ]);

            Reservation::query()
                ->where('listing_id', $listing->id)
                ->whereKeyNot($reservation->id)
                ->where('status', ReservationStatus::Requested->value)
                ->update([
                    'status' => ReservationStatus::Declined->value,
                    'responded_at' => now(),
                ]);

            $listing->update([
                'status' => ListingStatus::Reserved,
                'reserved_at' => now(),
            ]);

            $this->audit('reservation.accepted', $listing, ['reservation_id' => $reservation->id]);

            return ['message' => 'Request accepted. Arrange a safe meetup before completing the exchange.', 'listing' => $listing];
        });
    }

    /** @return array{message: string, listing: Listing} */
    private function declineRequest(Listing $listing, Reservation $reservation): array
    {
        if ($reservation->status !== ReservationStatus::Requested) {
            throw ValidationException::withMessages(['reservation_id' => 'Only pending requests can be declined.']);
        }

        $reservation->update([
            'status' => ReservationStatus::Declined,
            'responded_at' => now(),
        ]);
        $this->audit('reservation.declined', $listing, ['reservation_id' => $reservation->id]);

        return ['message' => 'Request declined.', 'listing' => $listing];
    }

    /** @return array{message: string, listing: Listing} */
    private function complete(Listing $listing, Reservation $reservation): array
    {
        if ($reservation->status !== ReservationStatus::Accepted) {
            throw ValidationException::withMessages(['reservation_id' => 'Accept a request before completing the exchange.']);
        }

        return DB::transaction(function () use ($listing, $reservation): array {
            $reservation->update([
                'status' => ReservationStatus::Completed,
                'completed_at' => now(),
            ]);
            $listing->update([
                'status' => ListingStatus::Completed,
                'completed_at' => now(),
            ]);
            $this->audit('listing.completed', $listing, ['reservation_id' => $reservation->id]);

            return ['message' => 'Exchange marked complete.', 'listing' => $listing];
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, listing: Listing}
     */
    private function report(Listing $listing, array $data): array
    {
        $highPriority = in_array($data['reason'], [
            'fraud',
            'illegal-sale',
            'animal-welfare',
            'personal-data',
        ], true);

        ListingReport::query()->create([
            'listing_id' => $listing->id,
            'reporter_key' => $this->actor->key(),
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'priority' => $highPriority ? 'high' : 'normal',
            'status' => 'submitted',
        ]);

        $this->audit('listing.reported', $listing, [
            'reason' => $data['reason'],
            'priority' => $highPriority ? 'high' : 'normal',
        ]);

        return ['message' => 'Report submitted for safety review.', 'listing' => $listing];
    }

    /** @param array<string, mixed> $data */
    private function reservation(Listing $listing, array $data): Reservation
    {
        if (($data['_reservation'] ?? null) instanceof Reservation) {
            return $data['_reservation'];
        }

        return Reservation::query()
            ->select([
                'id', 'listing_id', 'requester_key', 'requester_name', 'status',
                'exchange_method', 'proposed_at', 'responded_at', 'completed_at',
            ])
            ->where('listing_id', $listing->id)
            ->findOrFail((int) $data['reservation_id']);
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, Listing $listing, array $metadata): void
    {
        AuditLog::query()->create([
            'actor_key' => $this->actor->key(),
            'actor_role' => 'marketplace-member',
            'action' => $action,
            'target_type' => Listing::class,
            'target_id' => (string) $listing->id,
            'metadata' => $metadata,
        ]);
    }
}

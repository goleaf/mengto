<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\AuditLog;
use App\Models\Listing;
use App\Models\ListingEngagement;
use App\Models\ListingReport;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformListingAction
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly CreateOrder $createOrder,
    ) {}

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
            default => throw ValidationException::withMessages([
                'action' => __('actions.invalid'),
            ]),
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
            'message' => $engagement->is_saved ? __('messages.listing_saved_13e0b90db7') : __('messages.listing_removed_from_saved_items_e8345e1edf'),
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
                        'idempotency_key' => __('messages.this_request_key_is_already_in_use_2a954a3352'),
                    ]);
                }

                return ['message' => __('messages.your_request_was_already_received_c8dbe9a7b8'), 'listing' => $listing];
            }

            $lockedListing = Listing::query()
                ->select(['id', 'owner_key', 'status', 'quantity'])
                ->lockForUpdate()
                ->findOrFail($listing->id);

            if ($lockedListing->owner_key === $this->actor->key()) {
                throw ValidationException::withMessages(['action' => __('messages.you_cannot_request_your_own_listing_2a8414f4f4')]);
            }

            if ($lockedListing->status !== ListingStatus::Published) {
                throw ValidationException::withMessages(['action' => __('messages.this_listing_is_no_longer_available_6fef992c08')]);
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
                throw ValidationException::withMessages(['action' => __('messages.you_already_have_an_active_request_f8c4726fd2')]);
            }

            $identity = $this->actor->identity();
            $reservation = Reservation::query()->create([
                'listing_id' => $listing->id,
                'requester_key' => $identity['key'],
                'requester_name' => $identity['name'],
                'idempotency_key' => $data['idempotency_key'],
                'status' => ReservationStatus::Requested,
                'request_kind' => $listing->type->requestKind(),
                'quantity' => $data['quantity'],
                'offered_price' => $data['offered_price'] ?? null,
                'message' => $data['message'],
                'exchange_method' => $data['exchange_method'],
                'proposed_at' => $data['proposed_at'] ?? null,
                'rental_starts_at' => $data['rental_starts_at'] ?? null,
                'rental_ends_at' => $data['rental_ends_at'] ?? null,
                'questionnaire' => array_filter([
                    'experience' => $data['experience'] ?? null,
                    'home_context' => $data['home_context'] ?? null,
                    'other_pets' => $data['other_pets'] ?? null,
                    'care_plan' => $data['care_plan'] ?? null,
                    'adoption_reason' => $data['adoption_reason'] ?? null,
                ], filled(...)),
                'terms_accepted' => $data['terms_accepted'],
                'privacy_accepted' => $data['privacy_accepted'],
                'expires_at' => now()->addDays(3),
            ]);

            $this->audit('reservation.requested', $listing, [
                'reservation_id' => $reservation->id,
                'exchange_method' => $reservation->exchange_method,
            ]);

            return ['message' => __('messages.request_sent_keep_payment_and_contact_details_inside_the_086f753d5f'), 'listing' => $listing];
        });
    }

    /** @return array{message: string, listing: Listing} */
    private function cancelRequest(Listing $listing, Reservation $reservation): array
    {
        if ($reservation->requester_key !== $this->actor->key()) {
            throw ValidationException::withMessages(['reservation_id' => __('messages.this_request_does_not_belong_to_you_4f247edd8d')]);
        }

        if (! in_array($reservation->status, [ReservationStatus::Requested, ReservationStatus::Accepted], true)) {
            throw ValidationException::withMessages(['reservation_id' => __('messages.this_request_can_no_longer_be_cancelled_aa646e6cbd')]);
        }

        return DB::transaction(function () use ($listing, $reservation): array {
            $lockedReservation = Reservation::query()
                ->select([
                    'id', 'listing_id', 'requester_key', 'status', 'quantity',
                    'responded_at',
                ])
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($lockedReservation->requester_key !== $this->actor->key()) {
                throw ValidationException::withMessages(['reservation_id' => __('messages.this_request_does_not_belong_to_you_4f247edd8d')]);
            }

            if (! in_array($lockedReservation->status, [ReservationStatus::Requested, ReservationStatus::Accepted], true)) {
                throw ValidationException::withMessages(['reservation_id' => __('messages.this_request_can_no_longer_be_cancelled_aa646e6cbd')]);
            }

            $wasAccepted = $lockedReservation->status === ReservationStatus::Accepted;

            $lockedReservation->update([
                'status' => ReservationStatus::Cancelled,
                'responded_at' => now(),
            ]);

            if ($wasAccepted) {
                $lockedListing = Listing::query()
                    ->select(['id', 'quantity', 'status', 'availability'])
                    ->lockForUpdate()
                    ->findOrFail($listing->id);
                $lockedListing->update([
                    'quantity' => $lockedListing->quantity + $lockedReservation->quantity,
                    'availability' => 'in-stock',
                    'status' => ListingStatus::Published,
                    'reserved_at' => null,
                ]);

                $order = Order::query()
                    ->select(['id', 'status', 'payment_status', 'cancelled_at'])
                    ->where('reservation_id', $lockedReservation->id)
                    ->first();

                if ($order?->payment_status === PaymentStatus::Paid) {
                    throw ValidationException::withMessages([
                        'reservation_id' => __('messages.a_paid_order_must_be_refunded_through_the_dispute_proces_6f9b41c249'),
                    ]);
                }

                $order?->update([
                    'status' => OrderStatus::Cancelled,
                    'payment_status' => $order->payment_status === PaymentStatus::NotRequired
                        ? PaymentStatus::NotRequired
                        : PaymentStatus::Cancelled,
                    'cancelled_at' => now(),
                ]);
            }

            $this->audit('reservation.cancelled', $listing, ['reservation_id' => $reservation->id]);

            return ['message' => __('messages.request_cancelled_5312dbee54'), 'listing' => $listing];
        });
    }

    /** @return array{message: string, listing: Listing} */
    private function acceptRequest(Listing $listing, Reservation $reservation): array
    {
        if ($reservation->status !== ReservationStatus::Requested) {
            throw ValidationException::withMessages(['reservation_id' => __('messages.only_pending_requests_can_be_accepted_5788365cdd')]);
        }

        return DB::transaction(function () use ($listing, $reservation): array {
            $lockedListing = Listing::query()
                ->select([
                    'id', 'owner_id', 'owner_key', 'owner_name', 'slug', 'type',
                    'category', 'brand', 'model', 'material', 'title',
                    'description', 'condition', 'price', 'currency', 'quantity',
                    'availability', 'exchange_preferences', 'species',
                    'pet_size', 'age_group', 'attributes', 'defects',
                    'hygiene_status', 'sealed_package', 'area', 'city',
                    'delivery_options', 'meetup_notes', 'return_policy',
                    'cover_url', 'gallery', 'seller_type',
                    'is_verified_seller', 'contact_policy', 'status',
                ])
                ->lockForUpdate()
                ->findOrFail($listing->id);

            if ($lockedListing->status !== ListingStatus::Published) {
                throw ValidationException::withMessages(['action' => __('messages.this_listing_is_already_reserved_or_completed_19d53e26a3')]);
            }

            $lockedReservation = Reservation::query()
                ->select([
                    'id', 'listing_id', 'requester_id', 'requester_key',
                    'requester_name', 'status', 'request_kind', 'quantity',
                    'offered_price', 'message', 'exchange_method', 'proposed_at',
                    'rental_starts_at', 'rental_ends_at', 'questionnaire',
                    'terms_accepted', 'privacy_accepted', 'responded_at',
                ])
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($lockedReservation->status !== ReservationStatus::Requested) {
                throw ValidationException::withMessages(['reservation_id' => __('messages.only_pending_requests_can_be_accepted_5788365cdd')]);
            }

            if ($lockedReservation->quantity > $lockedListing->quantity) {
                throw ValidationException::withMessages([
                    'reservation_id' => __('messages.the_requested_quantity_is_no_longer_available_91358bffe6'),
                ]);
            }

            $remainingQuantity = $lockedListing->quantity - $lockedReservation->quantity;

            $lockedReservation->update([
                'status' => ReservationStatus::Accepted,
                'responded_at' => now(),
            ]);

            if ($remainingQuantity === 0) {
                Reservation::query()
                    ->where('listing_id', $listing->id)
                    ->whereKeyNot($lockedReservation->id)
                    ->where('status', ReservationStatus::Requested->value)
                    ->update([
                        'status' => ReservationStatus::Declined->value,
                        'responded_at' => now(),
                    ]);
            }

            $lockedListing->update([
                'quantity' => $remainingQuantity,
                'availability' => $remainingQuantity === 0 ? 'out-of-stock' : 'in-stock',
                'status' => $remainingQuantity === 0 ? ListingStatus::Reserved : ListingStatus::Published,
                'reserved_at' => $remainingQuantity === 0 ? now() : null,
            ]);

            $order = $this->createOrder->handle($lockedListing, $lockedReservation);
            $this->audit('reservation.accepted', $listing, [
                'reservation_id' => $lockedReservation->id,
                'order_reference' => $order->reference,
                'remaining_quantity' => $remainingQuantity,
            ]);

            return [
                'message' => $order->payment_status === PaymentStatus::Pending
                    ? __('messages.request_accepted_the_order_is_ready_for_protected_paymen_81bfd02b4d')
                    : __('messages.request_accepted_the_order_is_confirmed_3923e9c76e'),
                'listing' => $listing,
            ];
        });
    }

    /** @return array{message: string, listing: Listing} */
    private function declineRequest(Listing $listing, Reservation $reservation): array
    {
        if ($reservation->status !== ReservationStatus::Requested) {
            throw ValidationException::withMessages(['reservation_id' => __('messages.only_pending_requests_can_be_declined_55b3b320c5')]);
        }

        $reservation->update([
            'status' => ReservationStatus::Declined,
            'responded_at' => now(),
        ]);
        $this->audit('reservation.declined', $listing, ['reservation_id' => $reservation->id]);

        return ['message' => __('messages.request_declined_796f2c771c'), 'listing' => $listing];
    }

    /** @return array{message: string, listing: Listing} */
    private function complete(Listing $listing, Reservation $reservation): array
    {
        if ($reservation->status !== ReservationStatus::Accepted) {
            throw ValidationException::withMessages(['reservation_id' => __('messages.accept_a_request_before_completing_the_exchange_820d048d0d')]);
        }

        return DB::transaction(function () use ($listing, $reservation): array {
            $lockedReservation = Reservation::query()
                ->select(['id', 'listing_id', 'status', 'quantity', 'completed_at'])
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($lockedReservation->status !== ReservationStatus::Accepted) {
                throw ValidationException::withMessages([
                    'reservation_id' => __('messages.accept_a_request_before_completing_the_exchange_820d048d0d'),
                ]);
            }

            $order = Order::query()
                ->select([
                    'id', 'reservation_id', 'status', 'payment_status',
                    'completed_at',
                ])
                ->where('reservation_id', $lockedReservation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($order->payment_status, [PaymentStatus::Paid, PaymentStatus::NotRequired], true)) {
                throw ValidationException::withMessages([
                    'reservation_id' => __('messages.protected_payment_must_be_confirmed_before_completion_fda7e3cbcd'),
                ]);
            }

            if ($order->status === OrderStatus::Disputed) {
                throw ValidationException::withMessages([
                    'reservation_id' => __('messages.resolve_the_active_dispute_before_completion_723f65cdf0'),
                ]);
            }

            $lockedReservation->update([
                'status' => ReservationStatus::Completed,
                'completed_at' => now(),
            ]);

            $order->update([
                'status' => OrderStatus::Completed,
                'completed_at' => now(),
            ]);

            $lockedListing = Listing::query()
                ->select(['id', 'type', 'quantity', 'status', 'availability'])
                ->lockForUpdate()
                ->findOrFail($listing->id);
            $isRental = $lockedListing->type === ListingType::Rental;
            $availableQuantity = $isRental
                ? $lockedListing->quantity + $lockedReservation->quantity
                : $lockedListing->quantity;

            $lockedListing->update([
                'quantity' => $availableQuantity,
                'availability' => $availableQuantity > 0 ? 'in-stock' : 'out-of-stock',
                'status' => $availableQuantity > 0 ? ListingStatus::Published : ListingStatus::Completed,
                'completed_at' => $availableQuantity > 0 ? null : now(),
                'reserved_at' => null,
            ]);
            $this->audit('listing.completed', $listing, [
                'reservation_id' => $lockedReservation->id,
                'order_id' => $order->id,
            ]);

            return ['message' => __('messages.exchange_marked_complete_1490cdff69'), 'listing' => $listing];
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

        return ['message' => __('messages.report_submitted_for_safety_review_4aa74b8eb9'), 'listing' => $listing];
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
                'request_kind', 'quantity', 'offered_price', 'message',
                'exchange_method', 'proposed_at', 'rental_starts_at',
                'rental_ends_at', 'questionnaire', 'terms_accepted',
                'privacy_accepted', 'responded_at', 'completed_at',
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

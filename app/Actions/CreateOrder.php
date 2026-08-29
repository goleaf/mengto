<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Reservation;
use App\ValueObjects\MinorUnitAmount;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use UnexpectedValueException;

class CreateOrder
{
    /**
     * Create an immutable order snapshot from an accepted request.
     */
    public function handle(Listing $listing, Reservation $reservation): Order
    {
        $existing = Order::query()
            ->select(['id', 'reference'])
            ->where('reservation_id', $reservation->id)
            ->first();

        if ($existing !== null) {
            return Order::query()->whereKey($existing->id)->firstOrFail();
        }

        $quantity = max(1, (int) $reservation->quantity);
        $unitPrice = MinorUnitAmount::fromDecimal($reservation->offered_price ?? $listing->price ?? 0);
        $rentalDays = $this->rentalDays($listing, $reservation);
        $deposit = $listing->type->value === 'rental'
            ? $this->amount(data_get($listing->attributes, 'deposit_amount', 0))
            : MinorUnitAmount::fromDecimal(0);
        $subtotal = $unitPrice->multiply($quantity)->multiply($rentalDays);
        $total = $subtotal->add($deposit);

        if ($total->isGreaterThan(MinorUnitAmount::fromDecimal('99999999.99'))) {
            throw ValidationException::withMessages([
                'quantity' => __('messages.the_order_total_exceeds_the_supported_payment_limit_8f2b39a201'),
            ]);
        }

        $requiresPayment = $listing->type->requiresPayment() && $total->isPositive();
        $reference = 'ORD-'.strtoupper(Str::random(10));

        $order = Order::query()->create([
            'listing_id' => $listing->id,
            'reservation_id' => $reservation->id,
            'buyer_id' => $reservation->requester_id,
            'seller_id' => $listing->owner_id,
            'reference' => $reference,
            'idempotency_key' => (string) Str::uuid(),
            'buyer_key' => $reservation->requester_key,
            'buyer_name' => $reservation->requester_name,
            'seller_key' => $listing->owner_key,
            'seller_name' => $listing->owner_name,
            'order_kind' => $listing->type->requestKind(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice->toDecimal(),
            'delivery_amount' => 0,
            'deposit_amount' => $deposit->toDecimal(),
            'total_amount' => $total->toDecimal(),
            'currency' => $listing->currency,
            'delivery_method' => $reservation->exchange_method,
            'public_delivery_area' => collect([$listing->area, $listing->city])->filter()->implode(', '),
            'status' => $requiresPayment ? OrderStatus::AwaitingPayment : OrderStatus::Confirmed,
            'payment_status' => $requiresPayment ? PaymentStatus::Pending : PaymentStatus::NotRequired,
            'item_snapshot' => $this->itemSnapshot($listing),
            'terms_snapshot' => $this->termsSnapshot($listing, $reservation, $rentalDays),
            'ordered_at' => now(),
        ]);

        AuditLog::query()->create([
            'actor_key' => $reservation->requester_key,
            'actor_role' => 'marketplace-buyer',
            'action' => 'order.created',
            'target_type' => Order::class,
            'target_id' => (string) $order->id,
            'metadata' => [
                'reference' => $order->reference,
                'listing_id' => $listing->id,
                'reservation_id' => $reservation->id,
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment_status->value,
            ],
        ]);

        return $order;
    }

    private function amount(mixed $value): MinorUnitAmount
    {
        if (is_float($value) && is_finite($value)) {
            return MinorUnitAmount::fromDecimal(number_format($value, 2, '.', ''));
        }

        if (! is_string($value) && ! is_int($value)) {
            throw new UnexpectedValueException('stored-money-not-decimal');
        }

        return MinorUnitAmount::fromDecimal($value);
    }

    private function rentalDays(Listing $listing, Reservation $reservation): int
    {
        if ($listing->type->value !== 'rental' || $reservation->rental_starts_at === null || $reservation->rental_ends_at === null) {
            return 1;
        }

        return max(1, (int) ceil($reservation->rental_starts_at->diffInHours($reservation->rental_ends_at) / 24));
    }

    /** @return array<string, mixed> */
    private function itemSnapshot(Listing $listing): array
    {
        return [
            'listing_slug' => $listing->slug,
            'type' => $listing->type->value,
            'category' => $listing->category,
            'title' => $listing->title,
            'description' => $listing->description,
            'brand' => $listing->brand,
            'model' => $listing->model,
            'material' => $listing->material,
            'condition' => $listing->condition,
            'species' => $listing->species,
            'pet_size' => $listing->pet_size,
            'age_group' => $listing->age_group,
            'attributes' => $listing->attributes,
            'defects' => $listing->defects,
            'hygiene_status' => $listing->hygiene_status,
            'sealed_package' => $listing->sealed_package,
            'cover_url' => $listing->cover_url,
            'gallery' => $listing->gallery,
            'seller_type' => $listing->seller_type->value,
            'seller_verified' => $listing->is_verified_seller,
        ];
    }

    /** @return array<string, mixed> */
    private function termsSnapshot(Listing $listing, Reservation $reservation, int $rentalDays): array
    {
        return [
            'return_policy' => $listing->return_policy,
            'delivery_options' => $listing->delivery_options,
            'meetup_notes' => $listing->meetup_notes,
            'contact_policy' => $listing->contact_policy,
            'exchange_preferences' => $listing->exchange_preferences,
            'request_message' => $reservation->message,
            'questionnaire' => $reservation->questionnaire,
            'rental_starts_at' => $reservation->rental_starts_at?->toIso8601String(),
            'rental_ends_at' => $reservation->rental_ends_at?->toIso8601String(),
            'rental_days' => $rentalDays,
            'terms_accepted' => $reservation->terms_accepted,
            'privacy_accepted' => $reservation->privacy_accepted,
            'captured_at' => now()->toIso8601String(),
        ];
    }
}

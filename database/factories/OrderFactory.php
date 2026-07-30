<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<Order>
 */
class OrderFactory extends ApplicationFactory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Order $order): void {
            if ($order->reservation_id === null) {
                return;
            }

            $reservation = Reservation::query()
                ->select(['id', 'listing_id', 'requester_key', 'requester_name'])
                ->findOrFail($order->reservation_id);

            $order->listing_id = $reservation->listing_id;
            $order->buyer_key = $reservation->requester_key;
            $order->buyer_name = $reservation->requester_name;
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'reservation_id' => Reservation::factory(),
            'reference' => (string) Str::uuid(),
            'idempotency_key' => (string) Str::uuid(),
            'buyer_key' => fake()->unique()->userName(),
            'buyer_name' => fake()->name(),
            'seller_key' => fake()->unique()->userName(),
            'seller_name' => fake()->name(),
            'order_kind' => 'purchase',
            'quantity' => 1,
            'unit_price' => 24,
            'delivery_amount' => 0,
            'deposit_amount' => 0,
            'total_amount' => 24,
            'currency' => 'EUR',
            'delivery_method' => 'meetup',
            'public_delivery_area' => 'Naujamiestis, Vilnius',
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::NotRequired,
            'item_snapshot' => [
                'title' => 'Reflective harness',
                'condition' => 'good',
                'quantity' => 1,
            ],
            'terms_snapshot' => [
                'contact_policy' => 'platform-only',
                'return_policy' => 'Inspect during handover.',
            ],
            'ordered_at' => now(),
        ];
    }
}

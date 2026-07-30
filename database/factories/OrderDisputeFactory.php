<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DisputeStatus;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderDispute;

/**
 * @extends ApplicationFactory<OrderDispute>
 */
class OrderDisputeFactory extends ApplicationFactory
{
    public function configure(): static
    {
        return $this->afterMaking(function (OrderDispute $dispute): void {
            if ($dispute->order_id === null) {
                return;
            }

            $order = Order::query()
                ->select(['id', 'listing_id', 'buyer_key'])
                ->findOrFail($dispute->order_id);

            $dispute->listing_id = $order->listing_id;
            $dispute->opened_by_key = $order->buyer_key;
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
            'order_id' => Order::factory(),
            'listing_id' => Listing::factory(),
            'opened_by_key' => fake()->unique()->userName(),
            'opened_by_role' => 'buyer',
            'reason' => 'not-as-described',
            'details' => fake()->paragraph(),
            'evidence' => [],
            'priority' => 'normal',
            'status' => DisputeStatus::Open,
        ];
    }
}

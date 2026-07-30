<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Listing;
use App\Models\ListingReview;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListingReview>
 */
class ListingReviewFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (ListingReview $review): void {
            if ($review->order_id === null) {
                return;
            }

            $order = Order::query()
                ->select(['id', 'listing_id', 'buyer_key', 'buyer_name'])
                ->findOrFail($review->order_id);

            $review->listing_id = $order->listing_id;
            $review->reviewer_key = $order->buyer_key;
            $review->reviewer_name = $order->buyer_name;
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
            'order_id' => Order::factory(),
            'reviewer_key' => fake()->unique()->userName(),
            'reviewer_name' => fake()->name(),
            'is_verified_buyer' => true,
            'item_rating' => fake()->numberBetween(3, 5),
            'seller_rating' => fake()->numberBetween(3, 5),
            'delivery_rating' => fake()->numberBetween(3, 5),
            'body' => fake()->paragraph(),
            'status' => ReviewStatus::Published,
        ];
    }
}

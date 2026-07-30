<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'requester_key' => fake()->unique()->userName(),
            'requester_name' => fake()->name(),
            'idempotency_key' => (string) Str::uuid(),
            'status' => ReservationStatus::Requested,
            'message' => fake()->sentence(14),
            'exchange_method' => 'meetup',
            'proposed_at' => now()->addDay(),
            'expires_at' => now()->addDays(3),
        ];
    }
}

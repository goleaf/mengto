<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<Reservation>
 */
class ReservationFactory extends ApplicationFactory
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
            'request_kind' => 'purchase',
            'quantity' => 1,
            'offered_price' => null,
            'message' => fake()->sentence(14),
            'exchange_method' => 'meetup',
            'proposed_at' => now()->addDay(),
            'rental_starts_at' => null,
            'rental_ends_at' => null,
            'questionnaire' => [],
            'terms_accepted' => true,
            'privacy_accepted' => true,
            'expires_at' => now()->addDays(3),
        ];
    }
}

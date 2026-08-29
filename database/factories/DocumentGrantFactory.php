<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Booking;
use App\Models\DocumentGrant;

/** @extends ApplicationFactory<DocumentGrant> */
class DocumentGrantFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'expert_profile_id' => null,
            'owner_key' => null,
            'label' => 'Selected laboratory result',
            'document_type' => 'laboratory-result',
            'file_path' => 'documents/private/'.fake()->uuid().'.pdf',
            'permissions' => ['view'],
            'expires_at' => now()->addWeek(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (DocumentGrant $grant): void {
            $booking = Booking::query()->findOrFail($grant->booking_id);

            $grant->expert_profile_id = $booking->expert_profile_id;
            $grant->owner_key = $booking->client_key;
        });
    }
}

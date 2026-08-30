<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceCorrectionStatus;
use App\Models\PlaceCorrection;
use App\Models\PlaceCorrectionEvent;

/** @extends ApplicationFactory<PlaceCorrectionEvent> */
final class PlaceCorrectionEventFactory extends ApplicationFactory
{
    protected $model = PlaceCorrectionEvent::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_correction_id' => PlaceCorrection::factory(),
            'actor_user_id' => null,
            'idempotency_key' => null,
            'event_type' => 'submitted',
            'from_status' => null,
            'to_status' => PlaceCorrectionStatus::Pending,
            'public_summary_key' => 'places.corrections.history.submitted',
            'private_note' => null,
            'metadata' => ['channel' => 'factory'],
            'created_at' => now(),
        ];
    }
}

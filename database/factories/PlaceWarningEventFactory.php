<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceWarning;
use App\Models\PlaceWarningEvent;
use App\Models\User;

/** @extends ApplicationFactory<PlaceWarningEvent> */
final class PlaceWarningEventFactory extends ApplicationFactory
{
    protected $model = PlaceWarningEvent::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_warning_id' => PlaceWarning::factory(),
            'actor_user_id' => User::factory(),
            'idempotency_key' => null,
            'event_type' => 'submitted',
            'from_status' => null,
            'to_status' => 'needs_review',
            'public_summary_key' => 'places.warnings.events.submitted',
            'private_note' => null,
            'metadata' => null,
            'created_at' => now(),
        ];
    }
}

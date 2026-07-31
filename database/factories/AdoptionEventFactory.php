<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;

/**
 * @extends ApplicationFactory<AdoptionEvent>
 */
final class AdoptionEventFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'adoption_case_id' => AdoptionCase::factory(),
            'adoption_application_id' => null,
            'actor_user_id' => null,
            'event_type' => 'case-created',
            'previous_status' => null,
            'current_status' => 'published',
            'reason_translation_key' => 'adoption.events.case_created',
            'metadata' => ['source' => 'factory'],
        ];
    }
}

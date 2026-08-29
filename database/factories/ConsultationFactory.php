<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConsultationStatus;
use App\Models\Booking;
use App\Models\Consultation;

/** @extends ApplicationFactory<Consultation> */
class ConsultationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'expert_profile_id' => null,
            'status' => ConsultationStatus::Scheduled,
            'private_notes' => 'Private working notes for the professional only.',
            'action_plan' => [],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Consultation $consultation): void {
            $consultation->expert_profile_id = Booking::query()
                ->findOrFail($consultation->booking_id)
                ->expert_profile_id;
        });
    }
}

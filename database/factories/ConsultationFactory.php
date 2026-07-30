<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConsultationStatus;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\ExpertProfile;

/** @extends ApplicationFactory<Consultation> */
class ConsultationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'expert_profile_id' => ExpertProfile::factory(),
            'status' => ConsultationStatus::Scheduled,
            'private_notes' => 'Private working notes for the professional only.',
            'action_plan' => [],
        ];
    }
}

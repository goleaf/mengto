<?php

namespace Database\Factories;

use App\Enums\ConsultationStatus;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\ExpertProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Consultation> */
class ConsultationFactory extends Factory
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

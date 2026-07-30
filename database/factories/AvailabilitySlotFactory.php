<?php

namespace Database\Factories;

use App\Models\AvailabilitySlot;
use App\Models\ExpertProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AvailabilitySlot> */
class AvailabilitySlotFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = now()->addDays(fake()->numberBetween(1, 10))->setTime(10, 0);

        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'service_id' => null,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(45),
            'timezone' => 'Europe/Vilnius',
            'format' => 'in-person',
            'location_label' => 'Vilnius clinic',
            'capacity' => 1,
            'booked_count' => 0,
            'status' => 'open',
        ];
    }
}

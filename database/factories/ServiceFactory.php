<?php

namespace Database\Factories;

use App\Models\ExpertProfile;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'slug' => fake()->unique()->slug(3),
            'name' => 'Initial consultation',
            'type' => 'initial-consultation',
            'format' => 'in-person',
            'description' => 'A structured first appointment with an agreed next step.',
            'duration_minutes' => 45,
            'price' => 55,
            'currency' => 'EUR',
            'pricing_model' => 'fixed',
            'includes' => ['Consultation', 'Written summary'],
            'excludes' => ['Diagnostics', 'Medication'],
            'preparation' => ['Bring current medication list'],
            'cancellation_policy' => 'Free cancellation up to 24 hours before the appointment.',
            'follow_up_days' => 3,
            'requires_payment' => false,
            'requires_approval' => false,
            'capacity' => 1,
            'status' => 'active',
        ];
    }
}

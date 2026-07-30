<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\ExpertProfile;
use App\Models\Service;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<Booking> */
class BookingFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $startsAt = now()->addDays(2)->setTime(11, 0);

        return [
            'expert_profile_id' => ExpertProfile::factory(),
            'service_id' => Service::factory(),
            'reference' => (string) Str::uuid(),
            'idempotency_key' => (string) Str::uuid(),
            'client_key' => 'mia-carter',
            'client_name' => 'Mia Carter',
            'pet_key' => 'scout',
            'pet_name' => 'Scout',
            'pet_species' => 'dog',
            'pet_age_label' => '4 years',
            'format' => 'in-person',
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMinutes(45),
            'timezone' => 'Europe/Vilnius',
            'location_label' => 'Vilnius clinic',
            'status' => BookingStatus::Confirmed,
            'questionnaire' => [
                'main_question' => 'We need a clear next step.',
                'started_at' => 'This week',
                'tried' => 'Kept notes and avoided forcing the situation.',
                'urgent_signs' => false,
            ],
            'documents' => [],
            'amount' => 55,
            'currency' => 'EUR',
            'payment_status' => PaymentStatus::NotRequired,
            'terms_accepted' => true,
            'data_consent' => true,
            'recording_consent' => false,
            'confirmed_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => BookingStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}

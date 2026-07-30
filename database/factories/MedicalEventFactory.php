<?php

namespace Database\Factories;

use App\Enums\MedicalEventType;
use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use App\Models\MedicalEvent;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalEvent>
 */
class MedicalEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'type' => MedicalEventType::Visit,
            'title' => 'Routine health review',
            'occurred_at' => now()->subWeeks(2),
            'timezone' => 'Europe/Vilnius',
            'status' => 'completed',
            'source_type' => MedicalSourceType::Clinic,
            'source_name' => 'Paws 24 Veterinary Center',
            'source_reference' => fake()->bothify('VISIT-####'),
            'verification_status' => MedicalVerificationStatus::OrganizationIssued,
            'summary' => 'General examination completed and home monitoring discussed.',
            'details' => ['follow_up' => 'Return if symptoms change.'],
            'created_by_key' => 'paws-24-vet',
            'created_by_name' => 'Paws 24 Veterinary Center',
            'confirmed_by_name' => 'Dr. Ema Petrauskė',
            'confirmed_at' => now()->subWeeks(2),
            'follow_up_at' => now()->addMonths(6),
            'is_critical' => false,
        ];
    }
}

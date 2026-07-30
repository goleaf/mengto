<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MedicalVerificationStatus;
use App\Enums\VaccinationStatus;
use App\Models\MedicalRecord;
use App\Models\Vaccination;

/**
 * @extends ApplicationFactory<Vaccination>
 */
class VaccinationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'name' => 'Rabies vaccination',
            'manufacturer' => 'Verified veterinary manufacturer',
            'lot_number' => fake()->bothify('LOT-####'),
            'product_expires_on' => now()->addYear()->toDateString(),
            'administered_on' => now()->subMonths(10)->toDateString(),
            'next_due_on' => now()->addMonths(2)->toDateString(),
            'status' => VaccinationStatus::Completed,
            'dose' => 'As administered by clinic',
            'route' => 'Subcutaneous',
            'clinic_name' => 'Paws 24 Veterinary Center',
            'veterinarian_name' => 'Dr. Ema Petrauskė',
            'reaction' => 'No reaction reported.',
            'verification_status' => MedicalVerificationStatus::OrganizationIssued,
            'created_by_key' => 'paws-24-vet',
        ];
    }
}

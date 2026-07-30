<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MedicalVerificationStatus;
use App\Enums\MedicationStatus;
use App\Models\MedicalRecord;
use App\Models\Medication;

/**
 * @extends ApplicationFactory<Medication>
 */
class MedicationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'name' => 'Prescribed medication',
            'active_ingredient' => null,
            'form' => 'tablet',
            'concentration' => null,
            'dose' => '1 tablet',
            'route' => 'By mouth with food',
            'schedule_type' => 'fixed',
            'schedule_text' => 'Every 12 hours',
            'starts_on' => now()->subDays(3)->toDateString(),
            'ends_on' => now()->addDays(7)->toDateString(),
            'next_dose_at' => now()->addHours(2),
            'timezone' => 'Europe/Vilnius',
            'status' => MedicationStatus::Active,
            'reason' => 'Current treatment plan',
            'prescribed_by_name' => 'Dr. Ema Petrauskė',
            'clinic_name' => 'Paws 24 Veterinary Center',
            'instructions' => 'Follow the signed clinic instructions. Do not double a missed dose.',
            'is_high_risk' => false,
            'remaining_quantity' => 14,
            'remaining_unit' => 'tablets',
            'expires_on' => now()->addYear()->toDateString(),
            'verification_status' => MedicalVerificationStatus::ProfessionalConfirmed,
            'created_by_key' => 'paws-24-vet',
        ];
    }
}

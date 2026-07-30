<?php

namespace Database\Factories;

use App\Enums\MedicationDoseStatus;
use App\Models\MedicalRecord;
use App\Models\Medication;
use App\Models\MedicationDose;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MedicationDose>
 */
class MedicationDoseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'medication_id' => fn (array $attributes): int => Medication::factory()->create([
                'medical_record_id' => $attributes['medical_record_id'],
            ])->id,
            'idempotency_key' => (string) Str::uuid(),
            'scheduled_for' => now()->subHours(10)->startOfMinute(),
            'administered_at' => now()->subHours(10)->startOfMinute(),
            'timezone' => 'Europe/Vilnius',
            'status' => MedicationDoseStatus::Given,
            'dose_given' => '1 tablet',
            'administered_by_key' => 'mia-carter',
            'administered_by_name' => 'Mia Carter',
            'notes' => null,
        ];
    }
}

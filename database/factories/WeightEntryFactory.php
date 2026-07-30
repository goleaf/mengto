<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use App\Models\MedicalRecord;
use App\Models\WeightEntry;

/**
 * @extends ApplicationFactory<WeightEntry>
 */
class WeightEntryFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'measured_at' => now()->subDays(fake()->numberBetween(1, 60)),
            'timezone' => 'Europe/Vilnius',
            'weight_grams' => fake()->numberBetween(17500, 19000),
            'tare_grams' => null,
            'source_type' => MedicalSourceType::Owner,
            'source_name' => 'Home scale',
            'measurement_context' => 'Morning before breakfast',
            'notes' => null,
            'verification_status' => MedicalVerificationStatus::OwnerReported,
            'created_by_key' => 'mia-carter',
        ];
    }
}

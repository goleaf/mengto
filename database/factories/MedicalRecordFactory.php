<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MedicalRecord;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<MedicalRecord>
 */
class MedicalRecordFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $ownerKey = fake()->unique()->userName();
        $petKey = Str::slug(fake()->unique()->firstName());
        $petName = Str::headline($petKey);

        return [
            'owner_key' => $ownerKey,
            'slug' => $petKey.'-health-'.Str::lower(Str::random(5)),
            'pet_profile_key' => $petKey,
            'pet_name' => $petName,
            'species' => 'dog',
            'breed' => 'Mixed breed',
            'birth_date' => now()->subYears(4)->toDateString(),
            'birth_date_estimated' => false,
            'sex' => 'male',
            'reproductive_status' => 'neutered',
            'current_weight_grams' => 18400,
            'status' => 'active',
            'privacy' => 'private',
            'timezone' => 'Europe/Vilnius',
            'microchip_status' => 'registered',
            'microchip_number' => '900'.fake()->numerify('############'),
            'microchip_checked_on' => now()->subMonths(2)->toDateString(),
            'blood_group' => null,
            'critical_allergies' => ['Chicken protein'],
            'chronic_conditions' => [],
            'emergency_notes' => 'Use calm handling and call the primary clinic.',
            'primary_clinic_name' => 'Paws 24 Veterinary Center',
            'primary_clinic_contact' => '+370 5 555 0142',
            'emergency_contact' => [
                'name' => 'Mia Carter',
                'phone' => '+370 600 00000',
                'relationship' => 'owner',
            ],
            'last_visit_at' => now()->subWeeks(3),
            'next_appointment_at' => now()->addWeeks(4),
        ];
    }
}

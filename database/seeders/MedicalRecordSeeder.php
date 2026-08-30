<?php

namespace Database\Seeders;

use App\Enums\MedicalEventType;
use App\Enums\MedicalReminderStatus;
use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use App\Enums\MedicationDoseStatus;
use App\Enums\MedicationStatus;
use App\Enums\VaccinationStatus;
use App\Models\MedicalRecord;
use App\Models\PetProfile;
use Database\Seeders\Concerns\GuardsDemoSeeding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MedicalRecordSeeder extends Seeder
{
    use GuardsDemoSeeding;

    public function run(): void
    {
        $this->assertDemoSeedingIsAllowed();

        $profiles = PetProfile::query()
            ->select(['id', 'user_id', 'slug'])
            ->whereIn('slug', ['scout', 'nori'])
            ->get()
            ->keyBy('slug');
        $scoutProfile = $profiles->get('scout');
        $noriProfile = $profiles->get('nori');

        $existingRecords = MedicalRecord::query()
            ->select(['id', 'slug'])
            ->where('owner_key', 'mia-carter')
            ->whereIn('slug', ['scout-health', 'nori-health'])
            ->get()
            ->keyBy('slug');

        if ($existingRecords->isNotEmpty()) {
            if ($existingRecords->count() !== 2) {
                throw new \LogicException('The deterministic medical demo graph is partially present.');
            }

            $scout = $existingRecords->get('scout-health');

            if (! $scout instanceof MedicalRecord || ! $this->scoutChildrenAreComplete($scout)) {
                throw new \LogicException('The deterministic medical demo graph is partially present.');
            }

            $scout->update([
                'owner_id' => $scoutProfile?->user_id,
                'pet_profile_id' => $scoutProfile?->id,
                'allergy_knowledge_status' => 'known',
                'medication_knowledge_status' => 'known',
            ]);
            $existingRecords->get('nori-health')?->update([
                'owner_id' => $noriProfile?->user_id,
                'pet_profile_id' => $noriProfile?->id,
                'allergy_knowledge_status' => 'none-known',
                'medication_knowledge_status' => 'none-known',
            ]);

            return;
        }

        $scout = MedicalRecord::query()->create([
            'owner_id' => $scoutProfile?->user_id,
            'pet_profile_id' => $scoutProfile?->id,
            'owner_key' => 'mia-carter',
            'slug' => 'scout-health',
            'pet_profile_key' => 'scout',
            'pet_name' => 'Scout',
            'species' => 'dog',
            'breed' => 'Border Collie mix',
            'birth_date' => now()->subYears(4)->subMonths(3)->toDateString(),
            'sex' => 'male',
            'reproductive_status' => 'neutered',
            'current_weight_grams' => 19050,
            'image_url' => asset('images/places/veterinary-primary-md.jpg'),
            'status' => 'active',
            'privacy' => 'private',
            'timezone' => 'Europe/Vilnius',
            'microchip_status' => 'registered',
            'microchip_number' => '981020001234567',
            'microchip_checked_on' => now()->subMonths(2)->toDateString(),
            'blood_group' => 'DEA 1 negative',
            'allergy_knowledge_status' => 'known',
            'critical_allergies' => ['Severe reaction to chicken-based treats'],
            'medication_knowledge_status' => 'known',
            'chronic_conditions' => ['Seasonal skin sensitivity'],
            'emergency_notes' => 'Approach calmly from the side. Scout may pull away when frightened.',
            'primary_clinic_name' => 'Paws 24 Veterinary Clinic',
            'primary_clinic_contact' => '+370 600 00001',
            'emergency_contact' => [
                'name' => 'Mia Carter',
                'phone' => '+370 600 00002',
                'relationship' => 'Owner',
            ],
            'last_visit_at' => now()->subDays(12),
            'next_appointment_at' => now()->addDays(18)->setTime(10, 30),
        ]);

        foreach ([
            [19200, now()->subMonths(3), 'Routine clinic visit'],
            [19150, now()->subMonths(2), 'Home scale before breakfast'],
            [19080, now()->subMonth(), 'Routine clinic visit'],
            [19050, now()->subDays(5), 'Home scale before breakfast'],
        ] as [$grams, $measuredAt, $context]) {
            $scout->weightEntries()->create([
                'measured_at' => $measuredAt,
                'timezone' => 'Europe/Vilnius',
                'weight_grams' => $grams,
                'source_type' => str_contains($context, 'clinic')
                    ? MedicalSourceType::Clinic
                    : MedicalSourceType::Owner,
                'source_name' => str_contains($context, 'clinic')
                    ? 'Paws 24 Veterinary Clinic'
                    : 'Mia Carter',
                'measurement_context' => $context,
                'verification_status' => str_contains($context, 'clinic')
                    ? MedicalVerificationStatus::OrganizationIssued
                    : MedicalVerificationStatus::OwnerReported,
                'created_by_key' => 'mia-carter',
            ]);
        }

        $scout->events()->createMany([
            [
                'type' => MedicalEventType::Visit,
                'title' => 'Annual wellness examination',
                'occurred_at' => now()->subDays(12),
                'timezone' => 'Europe/Vilnius',
                'status' => 'completed',
                'source_type' => MedicalSourceType::Clinic,
                'source_name' => 'Paws 24 Veterinary Clinic',
                'verification_status' => MedicalVerificationStatus::OrganizationIssued,
                'summary' => 'Physical examination completed. Weight stable and skin plan reviewed.',
                'details' => 'Continue the current skin-care routine and return for vaccination review.',
                'created_by_key' => 'paws-24',
                'created_by_name' => 'Paws 24 Veterinary Clinic',
                'confirmed_by_name' => 'Dr. Emilia Vaitke',
                'confirmed_at' => now()->subDays(12),
                'follow_up_at' => now()->addDays(18)->setTime(10, 30),
                'is_critical' => false,
            ],
            [
                'type' => MedicalEventType::Allergy,
                'title' => 'Food reaction documented',
                'occurred_at' => now()->subMonths(8),
                'timezone' => 'Europe/Vilnius',
                'status' => 'active',
                'source_type' => MedicalSourceType::Veterinarian,
                'source_name' => 'Dr. Emilia Vaitke',
                'verification_status' => MedicalVerificationStatus::ProfessionalConfirmed,
                'summary' => 'Chicken-based treats caused facial itching and swelling.',
                'details' => 'Avoid chicken ingredients and contact the clinic if swelling returns.',
                'created_by_key' => 'dr-emilia',
                'created_by_name' => 'Dr. Emilia Vaitke',
                'confirmed_by_name' => 'Dr. Emilia Vaitke',
                'confirmed_at' => now()->subMonths(8),
                'is_critical' => true,
            ],
            [
                'type' => MedicalEventType::LabResult,
                'title' => 'Routine blood panel',
                'occurred_at' => now()->subMonths(5),
                'timezone' => 'Europe/Vilnius',
                'status' => 'completed',
                'source_type' => MedicalSourceType::Laboratory,
                'source_name' => 'VetLab Vilnius',
                'verification_status' => MedicalVerificationStatus::OrganizationIssued,
                'summary' => 'Result received and reviewed by the attending veterinarian.',
                'details' => 'The original report remains the source of record for interpretation.',
                'created_by_key' => 'vetlab-vilnius',
                'created_by_name' => 'VetLab Vilnius',
                'confirmed_by_name' => 'Dr. Emilia Vaitke',
                'confirmed_at' => now()->subMonths(5)->addDay(),
                'is_critical' => false,
            ],
        ]);

        $scout->vaccinations()->createMany([
            [
                'name' => 'Rabies vaccination',
                'manufacturer' => 'DemoVet',
                'lot_number' => 'RV-2026-041',
                'product_expires_on' => now()->addMonths(9)->toDateString(),
                'administered_on' => now()->subMonths(11)->toDateString(),
                'next_due_on' => now()->addMonth()->toDateString(),
                'status' => VaccinationStatus::DueSoon,
                'dose' => '1 ml',
                'route' => 'Subcutaneous',
                'clinic_name' => 'Paws 24 Veterinary Clinic',
                'veterinarian_name' => 'Dr. Emilia Vaitke',
                'reaction' => 'No reaction recorded',
                'verification_status' => MedicalVerificationStatus::OrganizationIssued,
                'created_by_key' => 'paws-24',
            ],
            [
                'name' => 'Core combination booster',
                'manufacturer' => 'DemoVet',
                'lot_number' => 'CB-2026-118',
                'administered_on' => now()->subMonths(5)->toDateString(),
                'next_due_on' => now()->addMonths(7)->toDateString(),
                'status' => VaccinationStatus::Completed,
                'dose' => '1 ml',
                'route' => 'Subcutaneous',
                'clinic_name' => 'Paws 24 Veterinary Clinic',
                'veterinarian_name' => 'Dr. Emilia Vaitke',
                'reaction' => 'No reaction recorded',
                'verification_status' => MedicalVerificationStatus::OrganizationIssued,
                'created_by_key' => 'paws-24',
            ],
        ]);

        $medication = $scout->medications()->create([
            'name' => 'Cetirizine',
            'active_ingredient' => 'Cetirizine hydrochloride',
            'form' => 'tablet',
            'concentration' => '10 mg',
            'dose' => '1 tablet',
            'route' => 'by mouth with food',
            'schedule_type' => 'fixed',
            'schedule_text' => 'Once daily with the evening meal',
            'starts_on' => now()->subDays(6)->toDateString(),
            'ends_on' => now()->addDays(8)->toDateString(),
            'next_dose_at' => now()->setTime(19, 0),
            'timezone' => 'Europe/Vilnius',
            'status' => MedicationStatus::Active,
            'reason' => 'Seasonal skin flare',
            'prescribed_by_name' => 'Dr. Emilia Vaitke',
            'clinic_name' => 'Paws 24 Veterinary Clinic',
            'instructions' => 'Use only according to the current clinic instruction. Contact the clinic if symptoms worsen.',
            'is_high_risk' => false,
            'remaining_quantity' => 8,
            'remaining_unit' => 'tablets',
            'expires_on' => now()->addYear()->toDateString(),
            'verification_status' => MedicalVerificationStatus::ProfessionalConfirmed,
            'created_by_key' => 'paws-24',
        ]);

        $medication->doses()->create([
            'medical_record_id' => $scout->id,
            'idempotency_key' => (string) Str::uuid(),
            'scheduled_for' => now()->subDay()->setTime(19, 0),
            'administered_at' => now()->subDay()->setTime(19, 4),
            'timezone' => 'Europe/Vilnius',
            'status' => MedicationDoseStatus::Given,
            'dose_given' => '1 tablet',
            'administered_by_key' => 'mia-carter',
            'administered_by_name' => 'Mia Carter',
            'notes' => 'Given with the evening meal.',
        ]);

        $scout->reminders()->createMany([
            [
                'type' => 'appointment',
                'title' => 'Skin follow-up appointment',
                'due_at' => now()->addDays(18)->setTime(10, 30),
                'timezone' => 'Europe/Vilnius',
                'priority' => 'normal',
                'status' => MedicalReminderStatus::Scheduled,
                'recipients' => ['mia-carter'],
                'instructions' => 'Bring the symptom diary and current medication packaging.',
                'created_by_key' => 'mia-carter',
            ],
            [
                'type' => 'vaccination',
                'title' => 'Review rabies vaccination',
                'due_at' => now()->addWeeks(3)->setTime(9, 0),
                'timezone' => 'Europe/Vilnius',
                'priority' => 'important',
                'status' => MedicalReminderStatus::Scheduled,
                'recipients' => ['mia-carter'],
                'instructions' => 'Confirm timing with the veterinarian before scheduling.',
                'created_by_key' => 'mia-carter',
            ],
        ]);

        MedicalRecord::query()->create([
            'owner_id' => $noriProfile?->user_id,
            'pet_profile_id' => $noriProfile?->id,
            'owner_key' => 'mia-carter',
            'slug' => 'nori-health',
            'pet_profile_key' => 'nori',
            'pet_name' => 'Nori',
            'species' => 'cat',
            'breed' => 'Tabby',
            'birth_date' => now()->subYears(2)->subMonths(2)->toDateString(),
            'birth_date_estimated' => true,
            'sex' => 'female',
            'reproductive_status' => 'spayed',
            'current_weight_grams' => 4120,
            'image_url' => asset('images/places/veterinary-secondary-md.jpg'),
            'status' => 'active',
            'privacy' => 'private',
            'timezone' => 'Europe/Vilnius',
            'microchip_status' => 'registered',
            'microchip_number' => '981020007654321',
            'microchip_checked_on' => now()->subMonths(4)->toDateString(),
            'allergy_knowledge_status' => 'none-known',
            'critical_allergies' => [],
            'medication_knowledge_status' => 'none-known',
            'chronic_conditions' => [],
            'emergency_notes' => 'Indoor cat. Keep the carrier covered in noisy environments.',
            'primary_clinic_name' => 'Paws 24 Veterinary Clinic',
            'primary_clinic_contact' => '+370 600 00001',
            'emergency_contact' => [
                'name' => 'Mia Carter',
                'phone' => '+370 600 00002',
                'relationship' => 'Owner',
            ],
            'last_visit_at' => now()->subMonths(4),
            'next_appointment_at' => now()->addMonths(2)->setTime(11, 0),
        ]);
    }

    private function scoutChildrenAreComplete(MedicalRecord $scout): bool
    {
        $weightsComplete = collect([
            [19200, 'Routine clinic visit'],
            [19150, 'Home scale before breakfast'],
            [19080, 'Routine clinic visit'],
            [19050, 'Home scale before breakfast'],
        ])->every(
            static fn (array $weight): bool => $scout->weightEntries()
                ->where('weight_grams', $weight[0])
                ->where('measurement_context', $weight[1])
                ->exists(),
        );
        $eventsComplete = collect([
            'Annual wellness examination',
            'Food reaction documented',
            'Routine blood panel',
        ])->every(
            static fn (string $title): bool => $scout->events()
                ->where('title', $title)
                ->exists(),
        );
        $vaccinationsComplete = collect([
            'Rabies vaccination',
            'Core combination booster',
        ])->every(
            static fn (string $name): bool => $scout->vaccinations()
                ->where('name', $name)
                ->exists(),
        );
        $remindersComplete = collect([
            'Skin follow-up appointment',
            'Review rabies vaccination',
        ])->every(
            static fn (string $title): bool => $scout->reminders()
                ->where('title', $title)
                ->exists(),
        );

        return $weightsComplete
            && $eventsComplete
            && $vaccinationsComplete
            && $remindersComplete
            && $scout->medications()
                ->where('name', 'Cetirizine')
                ->whereHas('doses', static fn ($query) => $query
                    ->where('dose_given', '1 tablet')
                    ->where('administered_by_key', 'mia-carter'))
                ->exists();
    }
}

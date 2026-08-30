<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MedicalKnowledgeStatus;
use App\Models\MedicalRecord;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Support\Str;
use LogicException;

/**
 * @extends ApplicationFactory<MedicalRecord>
 */
class MedicalRecordFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $profiles = [];
        $profileFor = static function (array $attributes) use (&$profiles): PetProfile {
            $profileId = $attributes['pet_profile_id'] ?? null;

            if (! is_int($profileId)) {
                throw new LogicException(
                    'Canonical medical record factories require a persisted pet profile.',
                );
            }

            return $profiles[$profileId] ??= PetProfile::query()
                ->with('user:id,actor_key')
                ->findOrFail($profileId);
        };

        return [
            'pet_profile_id' => static function (array $attributes): mixed {
                $ownerId = $attributes['owner_id'] ?? null;
                $ownerKey = $attributes['owner_key'] ?? null;
                $owner = is_int($ownerId)
                    ? User::query()->findOrFail($ownerId)
                    : null;

                if (! $owner instanceof User && is_string($ownerKey)) {
                    $owner = User::query()->where('actor_key', $ownerKey)->first()
                        ?? User::factory()->state(['actor_key' => $ownerKey])->create();
                }

                return $owner instanceof User
                    ? PetProfile::factory()->for($owner, 'user')
                    : PetProfile::factory();
            },
            'owner_id' => static fn (array $attributes): int => $profileFor($attributes)->user_id,
            'owner_key' => static fn (array $attributes): string => $profileFor($attributes)->user->actor_key,
            'slug' => static fn (array $attributes): string => $profileFor($attributes)->slug
                .'-health-'.Str::lower(Str::random(5)),
            'pet_profile_key' => static fn (array $attributes): string => $profileFor($attributes)->slug,
            'pet_name' => static fn (array $attributes): string => $profileFor($attributes)->name,
            'species' => static fn (array $attributes): string => Str::lower(
                $profileFor($attributes)->species,
            ),
            'breed' => static fn (array $attributes): ?string => $profileFor($attributes)->breed,
            'birth_date' => static fn (array $attributes): mixed => $profileFor($attributes)->birth_date,
            'birth_date_estimated' => false,
            'sex' => static fn (array $attributes): string => $profileFor($attributes)->sex,
            'reproductive_status' => static fn (array $attributes): string => $profileFor($attributes)->reproductive_status,
            'current_weight_grams' => 18400,
            'image_url' => static fn (array $attributes): ?string => $profileFor($attributes)->profile_data['profile_image']
                ?? $profileFor($attributes)->profile_data['avatar']
                ?? null,
            'status' => 'active',
            'privacy' => 'private',
            'timezone' => 'Europe/Vilnius',
            'microchip_status' => 'registered',
            'microchip_number' => '900'.fake()->numerify('############'),
            'microchip_checked_on' => now()->subMonths(2)->toDateString(),
            'blood_group' => null,
            'allergy_knowledge_status' => MedicalKnowledgeStatus::Known,
            'critical_allergies' => ['Chicken protein'],
            'medication_knowledge_status' => MedicalKnowledgeStatus::NoneKnown,
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

    public function configure(): static
    {
        return $this->afterMaking(function (MedicalRecord $record): void {
            if ($record->pet_profile_id === null) {
                return;
            }

            $profile = PetProfile::query()
                ->with('user:id,actor_key')
                ->findOrFail($record->pet_profile_id);

            if (
                $record->owner_id !== $profile->user_id
                || ! hash_equals($profile->user->actor_key, $record->owner_key)
            ) {
                throw new LogicException(
                    'Canonical medical record factories require the pet profile owner identity.',
                );
            }

            $record->forceFill([
                'owner_id' => $profile->user_id,
                'owner_key' => $profile->user->actor_key,
                'pet_profile_key' => $profile->slug,
                'pet_name' => $profile->name,
                'species' => Str::lower($profile->species),
                'breed' => $profile->breed,
                'birth_date' => $profile->birth_date,
                'sex' => $profile->sex,
                'reproductive_status' => $profile->reproductive_status,
                'image_url' => $profile->profile_data['profile_image']
                    ?? $profile->profile_data['avatar']
                    ?? null,
            ]);
        });
    }

    public function forPetProfile(PetProfile $profile): static
    {
        return $this->state(fn (): array => [
            'owner_id' => $profile->user_id,
            'pet_profile_id' => $profile->id,
            'owner_key' => $profile->user->actor_key,
            'pet_profile_key' => $profile->slug,
            'pet_name' => $profile->name,
            'species' => Str::lower($profile->species),
            'breed' => $profile->breed,
            'birth_date' => $profile->birth_date,
            'sex' => $profile->sex,
            'reproductive_status' => $profile->reproductive_status,
            'image_url' => $profile->profile_data['profile_image']
                ?? $profile->profile_data['avatar']
                ?? null,
        ]);
    }

    public function legacy(?User $owner = null): static
    {
        return $this->state(function () use ($owner): array {
            $petKey = 'legacy-pet-'.Str::lower((string) Str::ulid());

            return [
                'owner_id' => $owner instanceof User ? $owner->id : null,
                'pet_profile_id' => null,
                'owner_key' => $owner instanceof User
                    ? $owner->actor_key
                    : 'legacy-owner-'.Str::lower((string) Str::ulid()),
                'slug' => $petKey.'-health',
                'pet_profile_key' => $petKey,
                'pet_name' => 'Legacy pet',
                'species' => 'dog',
                'breed' => 'Mixed breed',
                'birth_date' => now()->subYears(4)->toDateString(),
                'sex' => 'unknown',
                'reproductive_status' => 'unknown',
                'image_url' => null,
            ];
        });
    }
}

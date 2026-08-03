<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetManagerRole;
use App\Enums\PetProfileVisibility;
use App\Enums\PetSpeciesConfidence;
use App\Models\PetProfile;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PetProfileForm extends Form
{
    public string $name = '';

    public string $species = 'unknown';

    public string $speciesConfidence = 'unidentified';

    /** @var list<int> */
    public array $taxonIds = [];

    public string $breed = '';

    public string $birthDate = '';

    public string $birthDatePrecision = 'unknown';

    public string $sex = 'unknown';

    public string $reproductiveStatus = 'unknown';

    public string $relationshipRole = 'primary-owner';

    public string $visibility = 'private';

    public string $bio = '';

    public string $appearanceSummary = '';

    public string $identifyingMarks = '';

    public string $temperamentSummary = '';

    public string $socialPreferences = '';

    public string $meetingPreferences = '';

    public string $locationLabel = '';

    public string $locationPrecision = 'hidden';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'species' => [
                'required',
                Rule::in(config('pet_profiles.species_options', [])),
            ],
            'speciesConfidence' => ['required', Rule::enum(PetSpeciesConfidence::class)],
            'taxonIds' => ['array', 'max:1'],
            'taxonIds.*' => [
                'integer',
                Rule::exists('taxa', 'id')->where('is_active', true),
            ],
            'breed' => ['nullable', 'string', 'max:120'],
            'birthDate' => ['nullable', 'date', 'before_or_equal:today'],
            'birthDatePrecision' => [
                'required',
                Rule::in(['exact', 'estimated', 'month', 'year', 'age-estimate', 'unknown']),
            ],
            'sex' => [
                'required',
                Rule::in(['male', 'female', 'unknown', 'undetermined', 'other-confirmed']),
            ],
            'reproductiveStatus' => [
                'required',
                Rule::in([
                    'intact',
                    'spayed',
                    'neutered',
                    'unknown',
                    'planned',
                    'medical-exception',
                    'not-applicable',
                ]),
            ],
            'relationshipRole' => ['required', Rule::enum(PetManagerRole::class)],
            'visibility' => ['required', Rule::enum(PetProfileVisibility::class)],
            'bio' => ['nullable', 'string', 'max:3000'],
            'appearanceSummary' => ['nullable', 'string', 'max:1500'],
            'identifyingMarks' => ['nullable', 'string', 'max:1500'],
            'temperamentSummary' => ['nullable', 'string', 'max:1500'],
            'socialPreferences' => ['nullable', 'string', 'max:1500'],
            'meetingPreferences' => ['nullable', 'string', 'max:1500'],
            'locationLabel' => ['nullable', 'string', 'max:120'],
            'locationPrecision' => [
                'required',
                Rule::in(['hidden', 'country', 'region', 'city', 'district']),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function creationData(string $idempotencyKey): array
    {
        $validated = $this->validate();

        return $this->actionData($validated) + [
            'relationship_role' => (string) $validated['relationshipRole'],
            'visibility' => (string) $validated['visibility'],
            'idempotency_key' => $idempotencyKey,
        ];
    }

    /** @return array<string, mixed> */
    public function updateData(int $lockVersion, string $idempotencyKey): array
    {
        $validated = $this->validate();

        return $this->actionData($validated) + [
            'lock_version' => $lockVersion,
            'idempotency_key' => $idempotencyKey,
        ];
    }

    /** @return array{name: string, species: string, species_confidence: string} */
    public function basicsData(): array
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'species' => [
                'required',
                Rule::in(config('pet_profiles.species_options', [])),
            ],
            'speciesConfidence' => ['required', Rule::enum(PetSpeciesConfidence::class)],
        ]);
        $species = (string) $validated['species'];

        return [
            'name' => trim((string) $validated['name']),
            'species' => $species,
            'species_confidence' => PetSpeciesConfidence::normalize(
                $species,
                (string) $validated['speciesConfidence'],
            )->value,
        ];
    }

    /** @return array<string, string|null> */
    public function ageAndSexData(): array
    {
        $validated = $this->validate([
            'birthDate' => ['nullable', 'date', 'before_or_equal:today'],
            'birthDatePrecision' => [
                'required',
                Rule::in(['exact', 'estimated', 'month', 'year', 'age-estimate', 'unknown']),
            ],
            'sex' => [
                'required',
                Rule::in(['male', 'female', 'unknown', 'undetermined', 'other-confirmed']),
            ],
            'reproductiveStatus' => [
                'required',
                Rule::in([
                    'intact',
                    'spayed',
                    'neutered',
                    'unknown',
                    'planned',
                    'medical-exception',
                    'not-applicable',
                ]),
            ],
        ]);

        return [
            'birth_date' => filled($validated['birthDate'] ?? null)
                ? (string) $validated['birthDate']
                : null,
            'birth_date_precision' => (string) $validated['birthDatePrecision'],
            'sex' => (string) $validated['sex'],
            'reproductive_status' => (string) $validated['reproductiveStatus'],
        ];
    }

    /** @return array{taxon_id: int|null, breed: string} */
    public function breedAndOriginData(): array
    {
        $validated = $this->validate([
            'taxonIds' => ['array', 'max:1'],
            'taxonIds.*' => [
                'integer',
                Rule::exists('taxa', 'id')->where('is_active', true),
            ],
            'breed' => ['nullable', 'string', 'max:120'],
        ]);

        return [
            'taxon_id' => isset($validated['taxonIds'][0])
                ? (int) $validated['taxonIds'][0]
                : null,
            'breed' => trim((string) ($validated['breed'] ?? '')),
        ];
    }

    /** @return array{appearance_summary: string, identifying_marks: string} */
    public function appearanceData(): array
    {
        $validated = $this->validate([
            'appearanceSummary' => ['nullable', 'string', 'max:1500'],
            'identifyingMarks' => ['nullable', 'string', 'max:1500'],
        ]);

        return [
            'appearance_summary' => trim((string) ($validated['appearanceSummary'] ?? '')),
            'identifying_marks' => trim((string) ($validated['identifyingMarks'] ?? '')),
        ];
    }

    /** @return array{story: string, temperament_summary: string} */
    public function characterData(): array
    {
        $validated = $this->validate([
            'bio' => ['nullable', 'string', 'max:3000'],
            'temperamentSummary' => ['nullable', 'string', 'max:1500'],
        ]);

        return [
            'story' => trim((string) ($validated['bio'] ?? '')),
            'temperament_summary' => trim((string) ($validated['temperamentSummary'] ?? '')),
        ];
    }

    /** @return array{social_preferences: string, meeting_preferences: string} */
    public function socialPreferencesData(): array
    {
        $validated = $this->validate([
            'socialPreferences' => ['nullable', 'string', 'max:1500'],
            'meetingPreferences' => ['nullable', 'string', 'max:1500'],
        ]);

        return [
            'social_preferences' => trim((string) ($validated['socialPreferences'] ?? '')),
            'meeting_preferences' => trim((string) ($validated['meetingPreferences'] ?? '')),
        ];
    }

    /** @return array{location_label: string, location_precision: string} */
    public function locationData(): array
    {
        $validated = $this->validate([
            'locationLabel' => ['nullable', 'string', 'max:120'],
            'locationPrecision' => [
                'required',
                Rule::in(['hidden', 'country', 'region', 'city', 'district']),
            ],
        ]);

        return [
            'location_label' => trim((string) ($validated['locationLabel'] ?? '')),
            'location_precision' => (string) $validated['locationPrecision'],
        ];
    }

    public function fillFromProfile(PetProfile $profile): void
    {
        $profileData = $profile->profile_data ?? [];
        $this->name = $profile->name;
        $this->species = $profile->species;
        $this->speciesConfidence = $profile->species_confidence->value;
        $this->taxonIds = $profile->taxon_id === null ? [] : [$profile->taxon_id];
        $this->breed = $profile->breed ?? '';
        $this->birthDate = $profile->birth_date?->format('Y-m-d') ?? '';
        $this->birthDatePrecision = $profile->birth_date_precision;
        $this->sex = $profile->sex;
        $this->reproductiveStatus = $profile->reproductive_status;
        $this->relationshipRole = $profile->creator_relationship ?? 'primary-owner';
        $this->visibility = $profile->visibility;
        $this->bio = (string) ($profileData['story'] ?? '');
        $this->appearanceSummary = (string) ($profileData['appearance_summary'] ?? '');
        $this->identifyingMarks = (string) ($profileData['identifying_marks'] ?? '');
        $this->temperamentSummary = (string) ($profileData['temperament_summary'] ?? '');
        $this->socialPreferences = (string) ($profileData['social_preferences'] ?? '');
        $this->meetingPreferences = (string) ($profileData['meeting_preferences'] ?? '');
        $this->locationLabel = (string) ($profileData['location_label'] ?? '');
        $this->locationPrecision = is_string($profileData['location_precision'] ?? null)
            ? $profileData['location_precision']
            : 'hidden';
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function actionData(array $validated): array
    {
        $species = (string) $validated['species'];

        return [
            'title' => trim((string) $validated['name']),
            'category' => $species,
            'species' => $species,
            'species_confidence' => PetSpeciesConfidence::normalize(
                $species,
                (string) $validated['speciesConfidence'],
            )->value,
            'taxon_id' => $validated['taxonIds'][0] ?? null,
            'breed' => trim((string) ($validated['breed'] ?? '')),
            'body' => trim((string) ($validated['bio'] ?? '')),
            'birth_date' => filled($validated['birthDate'] ?? null)
                ? (string) $validated['birthDate']
                : null,
            'birth_date_precision' => (string) $validated['birthDatePrecision'],
            'sex' => (string) $validated['sex'],
            'reproductive_status' => (string) $validated['reproductiveStatus'],
        ];
    }
}

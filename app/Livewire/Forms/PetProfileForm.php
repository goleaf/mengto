<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetBirthDatePrecision;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileVisibility;
use App\Enums\PetSpeciesConfidence;
use App\Models\PetProfile;
use App\Rules\ValidPetProfileName;
use App\Services\PetBirthDetailsNormalizer;
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

    public string $birthMonth = '';

    public string $birthYear = '';

    public string $estimatedAgeYears = '';

    public string $estimatedAgeMonths = '0';

    public string $celebrationMonth = '';

    public string $celebrationDay = '';

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
            'name' => ['required', 'string', 'min:1', 'max:120', app(ValidPetProfileName::class)],
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
            ...$this->birthRules(),
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
            'name' => ['required', 'string', 'min:1', 'max:120', app(ValidPetProfileName::class)],
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

    /** @return array<string, mixed> */
    public function ageAndSexData(
        PetBirthDetailsNormalizer $birthDetails,
        PetProfile $current,
    ): array {
        $validated = $this->validate([
            ...$this->birthRules(),
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

        $normalized = $birthDetails->normalize(
            $this->birthInput($validated),
            $current,
            [
                'birth_date' => 'form.birthDate',
                'birth_date_precision' => 'form.birthDatePrecision',
                'birth_month' => 'form.birthMonth',
                'birth_year' => 'form.birthYear',
                'estimated_age_years' => 'form.estimatedAgeYears',
                'estimated_age_month_remainder' => 'form.estimatedAgeMonths',
                'estimated_age_months' => 'form.estimatedAgeYears',
                'birthday_celebration_month' => 'form.celebrationMonth',
                'birthday_celebration_day' => 'form.celebrationDay',
            ],
        );

        return [
            ...$normalized,
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
        $this->birthDate = '';
        $this->birthMonth = '';
        $this->birthYear = '';
        $this->estimatedAgeYears = '';
        $this->estimatedAgeMonths = '0';
        $this->birthDatePrecision = $profile->birth_date_precision->value;
        $this->celebrationMonth = $profile->birthday_celebration_month === null
            ? ''
            : (string) $profile->birthday_celebration_month;
        $this->celebrationDay = $profile->birthday_celebration_day === null
            ? ''
            : (string) $profile->birthday_celebration_day;

        match ($profile->birth_date_precision) {
            PetBirthDatePrecision::Exact,
            PetBirthDatePrecision::Estimated => $this->birthDate = $profile->birth_date?->format('Y-m-d') ?? '',
            PetBirthDatePrecision::Month => $this->birthMonth = $profile->birth_date?->format('Y-m') ?? '',
            PetBirthDatePrecision::Year => $this->birthYear = $profile->birth_date?->format('Y') ?? '',
            PetBirthDatePrecision::AgeEstimate => $this->fillEstimatedAge($profile->estimated_age_months),
            PetBirthDatePrecision::Unknown => null,
        };
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
            ...$this->birthInput($validated),
            'sex' => (string) $validated['sex'],
            'reproductive_status' => (string) $validated['reproductiveStatus'],
        ];
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function birthInput(array $validated): array
    {
        return [
            'birth_date' => filled($validated['birthDate'] ?? null)
                ? (string) $validated['birthDate']
                : null,
            'birth_date_precision' => (string) $validated['birthDatePrecision'],
            'birth_month' => (string) ($validated['birthMonth'] ?? ''),
            'birth_year' => (string) ($validated['birthYear'] ?? ''),
            'estimated_age_years' => (string) ($validated['estimatedAgeYears'] ?? ''),
            'estimated_age_month_remainder' => (string) ($validated['estimatedAgeMonths'] ?? ''),
            'birthday_celebration_month' => (string) ($validated['celebrationMonth'] ?? ''),
            'birthday_celebration_day' => (string) ($validated['celebrationDay'] ?? ''),
        ];
    }

    private function fillEstimatedAge(?int $months): void
    {
        if ($months === null) {
            return;
        }

        $this->estimatedAgeYears = (string) intdiv($months, 12);
        $this->estimatedAgeMonths = (string) ($months % 12);
    }

    /** @return array<string, list<mixed>> */
    private function birthRules(): array
    {
        return [
            'birthDate' => [
                Rule::requiredIf(fn (): bool => in_array(
                    $this->birthDatePrecision,
                    [PetBirthDatePrecision::Exact->value, PetBirthDatePrecision::Estimated->value],
                    true,
                )),
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'birthDatePrecision' => [
                'required',
                Rule::enum(PetBirthDatePrecision::class),
            ],
            'birthMonth' => [
                Rule::requiredIf(fn (): bool => $this->birthDatePrecision === PetBirthDatePrecision::Month->value),
                'nullable',
                'date_format:Y-m',
            ],
            'birthYear' => [
                Rule::requiredIf(fn (): bool => $this->birthDatePrecision === PetBirthDatePrecision::Year->value),
                'nullable',
                'integer',
                'min:'.(now()->year - PetBirthDetailsNormalizer::MAX_AGE_YEARS),
                'max:'.now()->year,
            ],
            'estimatedAgeYears' => [
                Rule::requiredIf(fn (): bool => $this->birthDatePrecision === PetBirthDatePrecision::AgeEstimate->value),
                'nullable',
                'integer',
                'min:0',
                'max:'.PetBirthDetailsNormalizer::MAX_AGE_YEARS,
            ],
            'estimatedAgeMonths' => [
                Rule::requiredIf(fn (): bool => $this->birthDatePrecision === PetBirthDatePrecision::AgeEstimate->value),
                'nullable',
                'integer',
                'min:0',
                'max:11',
            ],
            'celebrationMonth' => ['nullable', 'integer', 'between:1,12', 'required_with:celebrationDay'],
            'celebrationDay' => ['nullable', 'integer', 'between:1,31', 'required_with:celebrationMonth'],
        ];
    }
}

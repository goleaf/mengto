<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetAppearanceColor;
use App\Enums\PetAppearancePattern;
use App\Enums\PetBirthDatePrecision;
use App\Enums\PetBreedConfidence;
use App\Enums\PetBreedOriginType;
use App\Enums\PetBreedSource;
use App\Enums\PetLifeStage;
use App\Enums\PetManagerRole;
use App\Enums\PetProfileVisibility;
use App\Enums\PetSpeciesConfidence;
use App\Models\PetProfile;
use App\Models\PetProfileBreedOrigin;
use App\Rules\ValidPetProfileName;
use App\Services\PetBirthDetailsNormalizer;
use Illuminate\Support\Str;
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

    public string $breedOriginType = 'unknown';

    /**
     * @var list<array{
     *     originKey: string,
     *     classificationId: string,
     *     name: string,
     *     confidence: string,
     *     source: string,
     *     approximateShare: string
     * }>
     */
    public array $breedOrigins = [];

    public string $birthDate = '';

    public string $birthDatePrecision = 'unknown';

    public string $birthMonth = '';

    public string $birthYear = '';

    public string $estimatedAgeYears = '';

    public string $estimatedAgeMonths = '0';

    public string $celebrationMonth = '';

    public string $celebrationDay = '';

    public string $lifeStageOverride = 'auto';

    public string $sex = 'unknown';

    public string $reproductiveStatus = 'unknown';

    public string $relationshipRole = 'primary-owner';

    public string $visibility = 'private';

    public string $bio = '';

    public string $appearanceSummary = '';

    public string $identifyingMarks = '';

    public string $appearancePrimaryColor = '';

    /** @var list<string> */
    public array $appearanceAdditionalColors = [];

    /** @var list<string> */
    public array $appearancePatterns = [];

    public string $appearanceColorDetails = '';

    public string $appearanceFeatherColorDetails = '';

    public string $appearanceScaleColorDetails = '';

    public string $appearanceSeasonalColorChanges = '';

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
            'lifeStageOverride' => $this->lifeStageRule(),
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
            ...$this->appearanceRules(),
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
            'lifeStageOverride' => $this->lifeStageRule(),
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
            'life_stage_override' => $validated['lifeStageOverride'] === 'auto'
                ? null
                : (string) $validated['lifeStageOverride'],
            'sex' => (string) $validated['sex'],
            'reproductive_status' => (string) $validated['reproductiveStatus'],
        ];
    }

    /** @return array<string, mixed> */
    public function breedAndOriginData(): array
    {
        $validated = $this->validate([
            'taxonIds' => ['array', 'max:1'],
            'taxonIds.*' => [
                'integer',
                Rule::exists('taxa', 'id')->where('is_active', true),
            ],
            'breedOriginType' => ['required', Rule::enum(PetBreedOriginType::class)],
            'breed' => ['nullable', 'string', 'max:120'],
            'breedOrigins' => ['array', 'max:4'],
            'breedOrigins.*.originKey' => ['nullable', 'string', 'max:26'],
            'breedOrigins.*.classificationId' => [
                'nullable',
                'integer',
                Rule::exists('domestic_classifications', 'id')
                    ->where('classification_type', 'breed')
                    ->where('is_active', true),
            ],
            'breedOrigins.*.name' => ['nullable', 'string', 'max:220'],
            'breedOrigins.*.confidence' => ['required', Rule::enum(PetBreedConfidence::class)],
            'breedOrigins.*.source' => ['required', Rule::enum(PetBreedSource::class)],
            'breedOrigins.*.approximateShare' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $origins = array_map(static fn (array $origin): array => [
            'origin_key' => $origin['originKey'] ?? null,
            'domestic_classification_id' => filled($origin['classificationId'] ?? null)
                ? (int) $origin['classificationId']
                : null,
            'name' => trim((string) ($origin['name'] ?? '')),
            'confidence' => (string) ($origin['confidence'] ?? ''),
            'source' => (string) ($origin['source'] ?? ''),
            'approximate_share_percent' => filled($origin['approximateShare'] ?? null)
                ? (int) $origin['approximateShare']
                : null,
        ], array_values($validated['breedOrigins'] ?? []));
        $originType = (string) $validated['breedOriginType'];
        $legacyBreed = trim((string) ($validated['breed'] ?? ''));

        if ($origins === []
            && $originType === PetBreedOriginType::Unknown->value
            && $legacyBreed !== '') {
            $originType = PetBreedOriginType::Single->value;
            $origins[] = [
                'origin_key' => null,
                'domestic_classification_id' => null,
                'name' => $legacyBreed,
                'confidence' => PetBreedConfidence::OwnerReported->value,
                'source' => PetBreedSource::OwnerAssumption->value,
                'approximate_share_percent' => null,
            ];
        }

        return [
            'taxon_id' => isset($validated['taxonIds'][0])
                ? (int) $validated['taxonIds'][0]
                : null,
            'breed_origin_type' => $originType,
            'breed_origins' => $origins,
        ];
    }

    public function addBreedOrigin(): void
    {
        if (count($this->breedOrigins) >= 4) {
            return;
        }

        $this->breedOrigins[] = $this->blankBreedOrigin();
    }

    public function removeBreedOrigin(int $index): void
    {
        if (! array_key_exists($index, $this->breedOrigins)) {
            return;
        }

        unset($this->breedOrigins[$index]);
        $this->breedOrigins = array_values($this->breedOrigins);
    }

    /** @return array<string, mixed> */
    public function appearanceData(): array
    {
        $validated = $this->validate([
            'appearanceSummary' => ['nullable', 'string', 'max:1500'],
            'identifyingMarks' => ['nullable', 'string', 'max:1500'],
            ...$this->appearanceRules(),
        ]);

        return [
            'primary_color' => (string) ($validated['appearancePrimaryColor'] ?? ''),
            'additional_colors' => array_values($validated['appearanceAdditionalColors'] ?? []),
            'patterns' => array_values($validated['appearancePatterns'] ?? []),
            'color_details' => trim((string) ($validated['appearanceColorDetails'] ?? '')),
            'feather_color_details' => trim((string) ($validated['appearanceFeatherColorDetails'] ?? '')),
            'scale_color_details' => trim((string) ($validated['appearanceScaleColorDetails'] ?? '')),
            'seasonal_color_changes' => trim((string) ($validated['appearanceSeasonalColorChanges'] ?? '')),
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

        if ($profile->relationLoaded('breedOrigins')) {
            $this->fillBreedOrigins($profile);
        }
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
        $this->lifeStageOverride = $profile->getRawOriginal('life_stage_override') === null
            ? 'auto'
            : $profile->life_stage_override->value;

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
        $appearance = is_array($profileData['appearance'] ?? null)
            ? $profileData['appearance']
            : [];
        $this->appearancePrimaryColor = is_string($appearance['primary_color'] ?? null)
            ? $appearance['primary_color']
            : '';
        $this->appearanceAdditionalColors = $this->stringList($appearance['additional_colors'] ?? null);
        $this->appearancePatterns = $this->stringList($appearance['patterns'] ?? null);
        $this->appearanceColorDetails = $this->profileText($appearance, 'color_details');
        $this->appearanceFeatherColorDetails = $this->profileText($appearance, 'feather_color_details');
        $this->appearanceScaleColorDetails = $this->profileText($appearance, 'scale_color_details');
        $this->appearanceSeasonalColorChanges = $this->profileText($appearance, 'seasonal_color_changes');
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
            'life_stage_override' => $validated['lifeStageOverride'] === 'auto'
                ? null
                : (string) $validated['lifeStageOverride'],
            'sex' => (string) $validated['sex'],
            'reproductive_status' => (string) $validated['reproductiveStatus'],
        ];
    }

    /** @return array<string, list<mixed>> */
    private function appearanceRules(): array
    {
        return [
            'appearancePrimaryColor' => ['nullable', Rule::enum(PetAppearanceColor::class)],
            'appearanceAdditionalColors' => ['array', 'max:4'],
            'appearanceAdditionalColors.*' => [
                'string',
                'distinct:strict',
                Rule::enum(PetAppearanceColor::class),
            ],
            'appearancePatterns' => ['array', 'max:3'],
            'appearancePatterns.*' => [
                'string',
                'distinct:strict',
                Rule::enum(PetAppearancePattern::class),
            ],
            'appearanceColorDetails' => ['nullable', 'string', 'max:1000'],
            'appearanceFeatherColorDetails' => ['nullable', 'string', 'max:1000'],
            'appearanceScaleColorDetails' => ['nullable', 'string', 'max:1000'],
            'appearanceSeasonalColorChanges' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return list<string> */
    private function stringList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter($values, 'is_string'));
    }

    /** @param array<string, mixed> $appearance */
    private function profileText(array $appearance, string $key): string
    {
        return is_string($appearance[$key] ?? null) ? $appearance[$key] : '';
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

    private function fillBreedOrigins(PetProfile $profile): void
    {
        $originType = $profile->breed_origin_type;
        $this->breedOriginType = $originType instanceof PetBreedOriginType
            ? $originType->value
            : ($profile->breed === null ? 'unknown' : 'single');
        $this->breedOrigins = $profile->breedOrigins
            ->map(static fn (PetProfileBreedOrigin $origin): array => [
                'originKey' => $origin->origin_key,
                'classificationId' => $origin->domestic_classification_id === null
                    ? ''
                    : (string) $origin->domestic_classification_id,
                'name' => $origin->breed_name,
                'confidence' => $origin->confidence->value,
                'source' => $origin->source->value,
                'approximateShare' => $origin->approximate_share_percent === null
                    ? ''
                    : (string) $origin->approximate_share_percent,
            ])
            ->values()
            ->all();

        if ($this->breedOrigins === [] && $profile->breed !== null) {
            $this->breedOrigins[] = [
                ...$this->blankBreedOrigin(),
                'name' => $profile->breed,
            ];
        }
    }

    /**
     * @return array{
     *     originKey: string,
     *     classificationId: string,
     *     name: string,
     *     confidence: string,
     *     source: string,
     *     approximateShare: string
     * }
     */
    private function blankBreedOrigin(): array
    {
        return [
            'originKey' => Str::lower((string) Str::ulid()),
            'classificationId' => '',
            'name' => '',
            'confidence' => PetBreedConfidence::OwnerReported->value,
            'source' => PetBreedSource::Unknown->value,
            'approximateShare' => '',
        ];
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

    /** @return list<mixed> */
    private function lifeStageRule(): array
    {
        return [
            'required',
            Rule::in([
                'auto',
                ...array_map(
                    static fn (PetLifeStage $stage): string => $stage->value,
                    PetLifeStage::cases(),
                ),
            ]),
        ];
    }
}

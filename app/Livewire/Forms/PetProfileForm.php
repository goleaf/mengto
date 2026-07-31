<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetManagerRole;
use App\Enums\PetProfileVisibility;
use App\Models\PetProfile;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PetProfileForm extends Form
{
    public string $name = '';

    public string $species = 'unknown';

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

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'species' => [
                'required',
                Rule::in(config('pet_profiles.species_options', [])),
            ],
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

    public function fillFromProfile(PetProfile $profile): void
    {
        $profileData = $profile->profile_data ?? [];
        $this->name = $profile->name;
        $this->species = $profile->species;
        $this->taxonIds = $profile->taxon_id === null ? [] : [$profile->taxon_id];
        $this->breed = $profile->breed ?? '';
        $this->birthDate = $profile->birth_date?->format('Y-m-d') ?? '';
        $this->birthDatePrecision = $profile->birth_date_precision;
        $this->sex = $profile->sex;
        $this->reproductiveStatus = $profile->reproductive_status;
        $this->relationshipRole = $profile->creator_relationship ?? 'primary-owner';
        $this->visibility = $profile->visibility;
        $this->bio = (string) ($profileData['story'] ?? '');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function actionData(array $validated): array
    {
        return [
            'title' => trim((string) $validated['name']),
            'category' => (string) $validated['species'],
            'species' => (string) $validated['species'],
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

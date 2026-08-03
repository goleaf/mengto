<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetManagerRole;
use App\Enums\PetProfileVisibility;
use App\Enums\PetSpeciesConfidence;
use App\Rules\ValidPetProfileName;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PetProfileCreateForm extends Form
{
    public string $name = '';

    public string $species = 'unknown';

    public string $speciesConfidence = 'unidentified';

    public string $relationshipRole = 'primary-owner';

    public string $visibility = 'private';

    /** @return list<PetManagerRole> */
    public static function relationshipRoles(): array
    {
        return [
            PetManagerRole::PrimaryOwner,
            PetManagerRole::CoOwner,
            PetManagerRole::FamilyMember,
            PetManagerRole::Shelter,
            PetManagerRole::Volunteer,
            PetManagerRole::Finder,
            PetManagerRole::FosterCarer,
            PetManagerRole::Specialist,
            PetManagerRole::Other,
        ];
    }

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
            'relationshipRole' => [
                'required',
                Rule::in(array_map(
                    static fn (PetManagerRole $role): string => $role->value,
                    self::relationshipRoles(),
                )),
            ],
            'visibility' => ['required', Rule::enum(PetProfileVisibility::class)],
        ];
    }

    /** @return array<string, string> */
    public function creationData(string $idempotencyKey): array
    {
        $validated = $this->validate();
        $species = (string) $validated['species'];
        $confidence = PetSpeciesConfidence::normalize(
            $species,
            (string) $validated['speciesConfidence'],
        );

        return [
            'title' => trim((string) $validated['name']),
            'category' => $species,
            'species' => $species,
            'species_confidence' => $confidence->value,
            'relationship_role' => (string) $validated['relationshipRole'],
            'visibility' => (string) $validated['visibility'],
            'idempotency_key' => $idempotencyKey,
        ];
    }
}

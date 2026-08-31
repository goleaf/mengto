<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetManagerRole;
use App\Enums\PetProfileVisibility;
use App\Enums\PetSpeciesConfidence;
use App\Rules\ValidPetProfileName;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final readonly class PetProfileCreationInput
{
    public function __construct(
        private ValidationFactory $validation,
        private ValidPetProfileName $validName,
    ) {}

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
    public function formRules(): array
    {
        return [
            'name' => $this->nameRules(),
            'species' => $this->speciesRules(),
            'speciesConfidence' => ['required', Rule::enum(PetSpeciesConfidence::class)],
            'relationshipRole' => $this->relationshipRules(),
            'visibility' => $this->visibilityRules(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{name: string, species: string, species_confidence: PetSpeciesConfidence, relationship: PetManagerRole, visibility: PetProfileVisibility, idempotency_key: string, duplicate_review_token: string, duplicate_review_decision_token: string}
     */
    public function validateAction(array $data): array
    {
        $input = [
            'name' => $data['title'] ?? '',
            'species' => Str::lower(trim((string) ($data['species'] ?? $data['category'] ?? ''))),
            'species_confidence' => $data['species_confidence'] ?? null,
            'relationship_role' => $data['relationship_role'] ?? PetManagerRole::PrimaryOwner->value,
            'visibility' => $data['visibility'] ?? PetProfileVisibility::Private->value,
            'idempotency_key' => $data['idempotency_key'] ?? (string) Str::uuid(),
            'duplicate_review_token' => $data['duplicate_review_token'] ?? '',
            'duplicate_review_decision_token' => $data['duplicate_review_decision_token'] ?? '',
        ];

        /** @var array{name: string, species: string, species_confidence: string|null, relationship_role: string, visibility: string, idempotency_key: string, duplicate_review_token: string, duplicate_review_decision_token: string} $validated */
        $validated = $this->validation->make($input, [
            'name' => $this->nameRules(),
            'species' => $this->speciesRules(),
            'species_confidence' => ['nullable', Rule::enum(PetSpeciesConfidence::class)],
            'relationship_role' => $this->relationshipRules(),
            'visibility' => $this->visibilityRules(),
            'idempotency_key' => ['required', 'string', 'max:255'],
            'duplicate_review_token' => ['nullable', 'string', 'max:4096'],
            'duplicate_review_decision_token' => ['nullable', 'string', 'max:4096'],
        ])->validate();

        return [
            'name' => trim($validated['name']),
            'species' => $validated['species'],
            'species_confidence' => PetSpeciesConfidence::normalize(
                $validated['species'],
                $validated['species_confidence'],
            ),
            'relationship' => PetManagerRole::from($validated['relationship_role']),
            'visibility' => PetProfileVisibility::from($validated['visibility']),
            'idempotency_key' => $validated['idempotency_key'],
            'duplicate_review_token' => $validated['duplicate_review_token'],
            'duplicate_review_decision_token' => $validated['duplicate_review_decision_token'],
        ];
    }

    /** @return list<mixed> */
    private function nameRules(): array
    {
        return ['required', 'string', 'min:1', 'max:120', $this->validName];
    }

    /** @return list<mixed> */
    private function speciesRules(): array
    {
        return ['required', Rule::in(config('pet_profiles.species_options', []))];
    }

    /** @return list<mixed> */
    private function relationshipRules(): array
    {
        return [
            'required',
            Rule::in(array_map(
                static fn (PetManagerRole $role): string => $role->value,
                self::relationshipRoles(),
            )),
        ];
    }

    /** @return list<mixed> */
    private function visibilityRules(): array
    {
        return ['required', Rule::enum(PetProfileVisibility::class)];
    }
}

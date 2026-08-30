<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetManagerRole;
use App\Enums\PetSpeciesConfidence;
use App\Services\PetProfileCreationInput;
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
        return PetProfileCreationInput::relationshipRoles();
    }

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return app(PetProfileCreationInput::class)->formRules();
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

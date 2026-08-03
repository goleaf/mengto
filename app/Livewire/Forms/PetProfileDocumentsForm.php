<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\PetProfileFact;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PetProfileDocumentsForm extends Form
{
    public string $microchipStatus = 'unknown';

    public string $microchipIdentifier = '';

    public string $documentsState = 'add-later';

    /** @return array<string, mixed> */
    public function data(bool $identifierRequired): array
    {
        $validated = $this->validate([
            'microchipStatus' => [
                'required',
                Rule::in(['unknown', 'not-chipped', 'chipped-identifier-unknown', 'chipped']),
            ],
            'microchipIdentifier' => [
                'nullable',
                'string',
                'max:80',
                Rule::requiredIf(
                    $identifierRequired && $this->microchipStatus === 'chipped',
                ),
            ],
            'documentsState' => [
                'required',
                Rule::in(['none', 'available', 'add-later', 'not-applicable']),
            ],
        ]);

        $identifier = $validated['microchipStatus'] === 'chipped'
            ? trim((string) ($validated['microchipIdentifier'] ?? ''))
            : '';

        return [
            'status' => (string) $validated['microchipStatus'],
            'identifier' => $identifier === '' ? null : $identifier,
            'documents_state' => (string) $validated['documentsState'],
        ];
    }

    public function fillFromFact(?PetProfileFact $fact): void
    {
        if (! $fact instanceof PetProfileFact) {
            return;
        }

        $value = $fact->value;
        $this->microchipStatus = is_string($value['status'] ?? null)
            ? $value['status']
            : 'unknown';
        $this->microchipIdentifier = '';
        $this->documentsState = is_string($value['documents_state'] ?? null)
            ? $value['documents_state']
            : 'add-later';
    }
}

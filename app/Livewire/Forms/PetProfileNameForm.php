<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetProfileNameType;
use App\Enums\PetProfileNameVisibility;
use App\Rules\ValidPetProfileName;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PetProfileNameForm extends Form
{
    public string $name = '';

    public string $type = 'nickname';

    public string $visibility = 'private';

    public string $locale = '';

    /** @return array{name: string, type: string, visibility: string, locale: string|null} */
    public function data(): array
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120', app(ValidPetProfileName::class)],
            'type' => ['required', Rule::enum(PetProfileNameType::class)],
            'visibility' => ['required', Rule::enum(PetProfileNameVisibility::class)],
            'locale' => [
                'nullable',
                'required_if:type,'.PetProfileNameType::Localized->value,
                'string',
                'max:16',
                'regex:/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*$/',
            ],
        ]);

        return [
            'name' => trim((string) $validated['name']),
            'type' => (string) $validated['type'],
            'visibility' => (string) $validated['visibility'],
            'locale' => filled($validated['locale'] ?? null)
                ? (string) $validated['locale']
                : null,
        ];
    }
}

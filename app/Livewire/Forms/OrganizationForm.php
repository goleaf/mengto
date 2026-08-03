<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateOrganizationData;
use App\Enums\OrganizationType;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class OrganizationForm extends Form
{
    public string $name = '';

    public string $summary = '';

    public string $type = 'community';

    public string $defaultLocale = 'en';

    public string $publicRegion = '';

    public string $idempotencyKey = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:180'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'type' => ['required', Rule::enum(OrganizationType::class)],
            'defaultLocale' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'publicRegion' => ['nullable', 'string', 'max:160'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ];
    }

    public function data(): CreateOrganizationData
    {
        $validated = $this->validate();

        return new CreateOrganizationData(
            name: trim((string) $validated['name']),
            type: OrganizationType::from((string) $validated['type']),
            defaultLocale: (string) $validated['defaultLocale'],
            idempotencyKey: (string) $validated['idempotencyKey'],
            summary: filled($validated['summary'] ?? null)
                ? trim((string) $validated['summary'])
                : null,
            publicRegion: filled($validated['publicRegion'] ?? null)
                ? trim((string) $validated['publicRegion'])
                : null,
        );
    }
}

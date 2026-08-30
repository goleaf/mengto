<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\SubmitPlaceData;
use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceType;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PlaceSubmissionForm extends Form
{
    public string $name = '';

    public string $catalogCategory = 'park';

    public string $source = 'personal_visit';

    public string $sourceReference = '';

    public string $relationshipToPlace = 'visitor';

    public string $locationPrecision = 'public_region';

    public string $publicRegion = '';

    public string $publicAddress = '';

    public string $publicLatitude = '';

    public string $publicLongitude = '';

    public string $exactAddress = '';

    public string $exactLatitude = '';

    public string $exactLongitude = '';

    public string $publicPhone = '';

    public string $publicEmail = '';

    public string $publicWebsite = '';

    public string $summary = '';

    public string $hours = '';

    public string $services = '';

    public string $rulesText = '';

    public string $features = '';

    public string $observedAt = '';

    public bool $consentGranted = false;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:180'],
            'catalogCategory' => ['required', Rule::in(array_keys(self::categoryTypes()))],
            'source' => ['required', Rule::enum(PlaceSubmissionSource::class)],
            'sourceReference' => ['nullable', 'string', 'max:2048'],
            'relationshipToPlace' => ['required', Rule::in(['visitor', 'customer', 'employee', 'owner', 'organization', 'public-observer'])],
            'locationPrecision' => ['required', Rule::enum(PlaceLocationPrecision::class)],
            'publicRegion' => ['required', 'string', 'max:160'],
            'publicAddress' => ['nullable', 'string', 'max:500'],
            'publicLatitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:publicLongitude'],
            'publicLongitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:publicLatitude'],
            'exactAddress' => ['nullable', 'string', 'max:2000'],
            'exactLatitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:exactLongitude'],
            'exactLongitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:exactLatitude'],
            'publicPhone' => ['nullable', 'string', 'max:40'],
            'publicEmail' => ['nullable', 'email:rfc', 'max:255'],
            'publicWebsite' => ['nullable', 'url:http,https', 'max:2048'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'hours' => ['nullable', 'string', 'max:1000'],
            'services' => ['nullable', 'string', 'max:1000'],
            'rulesText' => ['nullable', 'string', 'max:3000'],
            'features' => ['nullable', 'string', 'max:1000'],
            'observedAt' => ['nullable', 'date', 'before_or_equal:tomorrow'],
            'consentGranted' => ['accepted'],
        ];
    }

    public function data(string $idempotencyKey, string $locale): SubmitPlaceData
    {
        $validated = $this->validate();
        $category = (string) $validated['catalogCategory'];

        return new SubmitPlaceData(
            name: trim((string) $validated['name']),
            type: self::categoryTypes()[$category],
            catalogCategory: $category,
            source: PlaceSubmissionSource::from((string) $validated['source']),
            sourceReference: $this->nullable((string) ($validated['sourceReference'] ?? '')),
            relationshipToPlace: (string) $validated['relationshipToPlace'],
            locationPrecision: PlaceLocationPrecision::from((string) $validated['locationPrecision']),
            locale: $locale,
            publicRegion: trim((string) $validated['publicRegion']),
            publicAddress: $this->nullable((string) ($validated['publicAddress'] ?? '')),
            publicLatitude: $this->nullable((string) ($validated['publicLatitude'] ?? '')),
            publicLongitude: $this->nullable((string) ($validated['publicLongitude'] ?? '')),
            exactAddress: $this->nullable((string) ($validated['exactAddress'] ?? '')),
            exactLatitude: $this->nullable((string) ($validated['exactLatitude'] ?? '')),
            exactLongitude: $this->nullable((string) ($validated['exactLongitude'] ?? '')),
            publicPhone: $this->nullable((string) ($validated['publicPhone'] ?? '')),
            publicEmail: $this->nullable((string) ($validated['publicEmail'] ?? '')),
            publicWebsite: $this->nullable((string) ($validated['publicWebsite'] ?? '')),
            summary: $this->nullable((string) ($validated['summary'] ?? '')),
            facts: array_filter([
                'hours' => filled($validated['hours'] ?? null)
                    ? ['description' => trim((string) $validated['hours'])]
                    : [],
                'services' => $this->list((string) ($validated['services'] ?? '')),
                'rules' => $this->nullable((string) ($validated['rulesText'] ?? '')),
                'features' => $this->list((string) ($validated['features'] ?? '')),
            ], static fn (mixed $value): bool => $value !== null && $value !== []),
            canonicalOrganizationId: null,
            observedAt: filled($validated['observedAt'] ?? null)
                ? CarbonImmutable::parse((string) $validated['observedAt'])
                : null,
            consentVersion: 'places-submission-v1',
            consentGranted: (bool) $validated['consentGranted'],
            idempotencyKey: $idempotencyKey,
        );
    }

    /** @return array<string, PlaceType> */
    public static function categoryTypes(): array
    {
        return [
            'park' => PlaceType::Park,
            'dog-park' => PlaceType::Park,
            'route' => PlaceType::WalkingRoute,
            'vet' => PlaceType::VeterinaryClinic,
            'emergency-vet' => PlaceType::VeterinaryClinic,
            'pet-store' => PlaceType::PublicSpace,
            'grooming' => PlaceType::PublicSpace,
            'shelter' => PlaceType::Shelter,
            'pet-cafe' => PlaceType::PublicSpace,
        ];
    }

    private function nullable(string $value): ?string
    {
        return filled($value) ? trim($value) : null;
    }

    /** @return list<string> */
    private function list(string $value): array
    {
        return collect(explode(',', $value))
            ->map(static fn (string $item): string => trim($item))
            ->filter()
            ->unique()
            ->take(30)
            ->values()
            ->all();
    }
}

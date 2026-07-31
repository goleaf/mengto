<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Taxon;
use App\Models\TaxonName;
use App\Models\TaxonVersion;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Collection;

final class LocalizedTaxonName
{
    private const COMMON_NAME_TYPES = [
        'preferred common',
        'common',
        'alternate common',
        'local name',
    ];

    public function __construct(
        private readonly Translator $translator,
    ) {}

    /**
     * @return array{name: string, scientific_name: string|null, name_locale: string|null}
     */
    public function present(
        Taxon $taxon,
        string $locale,
        string $fallbackLocale,
    ): array {
        $version = $taxon->activeVersion;
        $scientificName = $version instanceof TaxonVersion
            ? $version->scientific_name
            : null;
        $names = $taxon->names;
        $localized = $this->preferredCommonName($names, $locale)
            ?? $this->preferredCommonName($names, $fallbackLocale);

        if ($localized instanceof TaxonName) {
            return [
                'name' => $localized->name,
                'scientific_name' => $scientificName,
                'name_locale' => $localized->locale,
            ];
        }

        return [
            'name' => $scientificName
                ?? $this->translator->get('taxonomy.unidentified', locale: $locale),
            'scientific_name' => $scientificName,
            'name_locale' => null,
        ];
    }

    /**
     * @param  Collection<int, TaxonName>  $names
     */
    private function preferredCommonName(
        Collection $names,
        string $locale,
    ): ?TaxonName {
        $eligible = $names
            ->filter(static fn (TaxonName $name): bool => $name->is_active
                && $name->is_verified
                && $name->locale === $locale
                && in_array($name->name_type, self::COMMON_NAME_TYPES, true));

        return $eligible->first(
            static fn (TaxonName $name): bool => $name->is_preferred
                && $name->name_type === 'preferred common',
        )
            ?? $eligible->first(
                static fn (TaxonName $name): bool => $name->is_preferred,
            )
            ?? $eligible->first();
    }
}

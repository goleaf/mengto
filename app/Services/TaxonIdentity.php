<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TaxonSource;
use Normalizer;

final class TaxonIdentity
{
    public function stableKey(TaxonSource $source, string $sourceRecordId): string
    {
        return sprintf(
            'taxon.%s.%s',
            substr(hash('sha256', $source->stable_key), 0, 16),
            substr(hash('sha256', $sourceRecordId), 0, 48),
        );
    }

    public function normalizeName(string $name): string
    {
        $normalized = class_exists(Normalizer::class)
            ? Normalizer::normalize($name, Normalizer::FORM_C)
            : $name;
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $normalized));

        return mb_strtolower((string) $normalized);
    }

    public function importedNameKey(
        string $sourceRecordId,
        string $nameType,
        string $normalizedName,
        ?string $locale,
    ): string {
        return hash(
            'sha256',
            implode('|', [$sourceRecordId, $nameType, $normalizedName, $locale ?? '']),
        );
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Taxon;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<Taxon>
 */
final class TaxonFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'stable_key' => 'taxon.'.Str::lower(Str::random(20)),
            'resolution_status' => 'accepted',
            'requires_review' => false,
            'is_active' => true,
            'metadata' => [],
        ];
    }

    public function unresolved(): static
    {
        return $this->state(fn (): array => [
            'resolution_status' => 'unresolved',
            'requires_review' => true,
        ]);
    }
}

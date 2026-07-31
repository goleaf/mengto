<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TaxonSource;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<TaxonSource>
 */
final class TaxonSourceFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::slug(fake()->unique()->words(2, true));

        return [
            'stable_key' => $key,
            'name' => fake()->company(),
            'source_type' => 'darwin-core-archive',
            'version' => 'test-1',
            'release_date' => now()->toDateString(),
            'downloaded_at' => now(),
            'checksum' => hash('sha256', $key),
            'license' => 'CC BY 4.0',
            'attribution' => fake()->company(),
            'source_url' => 'https://example.test/taxonomy/'.$key,
            'import_priority' => 100,
            'is_active' => true,
            'metadata' => [],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BreedRegistry;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<BreedRegistry>
 */
final class BreedRegistryFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'stable_key' => Str::slug($name),
            'name' => $name,
            'jurisdiction' => fake()->countryCode(),
            'source_url' => 'https://example.test/registry/'.Str::slug($name),
            'is_active' => true,
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }
}

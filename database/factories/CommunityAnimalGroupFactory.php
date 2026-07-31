<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CommunityAnimalGroup;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<CommunityAnimalGroup>
 */
final class CommunityAnimalGroupFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::slug(fake()->unique()->words(2, true));

        return [
            'stable_key' => $key,
            'name_translation_key' => 'animal_groups.'.$key.'.name',
            'description_translation_key' => 'animal_groups.'.$key.'.description',
            'position' => fake()->numberBetween(1, 200),
            'is_system_managed' => false,
            'is_active' => true,
            'metadata' => [],
        ];
    }
}

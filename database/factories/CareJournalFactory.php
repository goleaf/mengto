<?php

namespace Database\Factories;

use App\Models\CareJournal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CareJournal>
 */
class CareJournalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $petName = fake()->firstName();

        return [
            'owner_key' => 'mia-carter',
            'slug' => Str::slug($petName.'-'.fake()->unique()->numerify('care-####')),
            'pet_profile_key' => Str::slug($petName.'-'.fake()->unique()->numerify('pet-####')),
            'pet_name' => $petName,
            'species' => fake()->randomElement(['dog', 'cat', 'bird', 'rabbit']),
            'breed' => fake()->randomElement(['Mixed breed', 'Tabby', 'Rescue']),
            'privacy' => 'private',
            'timezone' => 'Europe/Vilnius',
            'current_caregiver_key' => 'mia-carter',
            'current_caregiver_name' => 'Mia Carter',
            'status' => 'active',
            'lock_version' => 1,
        ];
    }
}

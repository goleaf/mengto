<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CareJournal;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<CareJournal>
 */
class CareJournalFactory extends ApplicationFactory
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
            'owner_id' => static fn (array $attributes): mixed => User::query()
                ->where('actor_key', (string) $attributes['owner_key'])
                ->value('id') ?? User::factory(),
            'owner_key' => fake()->unique()->userName(),
            'slug' => Str::slug($petName.'-'.fake()->unique()->numerify('care-####')),
            'pet_profile_key' => Str::slug($petName.'-'.fake()->unique()->numerify('pet-####')),
            'pet_name' => $petName,
            'species' => fake()->randomElement(['dog', 'cat', 'bird', 'rabbit']),
            'breed' => fake()->randomElement(['Mixed breed', 'Tabby', 'Rescue']),
            'privacy' => 'private',
            'timezone' => 'Europe/Vilnius',
            'current_caregiver_key' => null,
            'current_caregiver_name' => null,
            'status' => 'active',
            'lock_version' => 1,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (CareJournal $journal): void {
            if ($journal->owner_id === null) {
                return;
            }

            $owner = User::query()->where('actor_key', $journal->owner_key)->first()
                ?? User::query()->findOrFail($journal->owner_id);

            $journal->owner_id = $owner->id;
            $journal->owner_key = $owner->actor_key;
            $journal->current_caregiver_key = $owner->actor_key;
            $journal->current_caregiver_name = $owner->name;
        });
    }
}

<?php

namespace Database\Factories;

use App\Models\SearchCase;
use App\Models\SearchUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchUpdate>
 */
class SearchUpdateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'search_case_id' => SearchCase::factory(),
            'author_key' => 'mia-carter',
            'author_name' => 'Mia Carter',
            'type' => 'case-update',
            'visibility' => 'public',
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(),
            'public_area' => 'Vilnius',
            'occurred_at' => now(),
        ];
    }
}

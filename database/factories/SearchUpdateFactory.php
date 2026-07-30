<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SearchCase;
use App\Models\SearchUpdate;

/**
 * @extends ApplicationFactory<SearchUpdate>
 */
class SearchUpdateFactory extends ApplicationFactory
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

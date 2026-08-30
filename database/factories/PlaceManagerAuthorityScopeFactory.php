<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceManagementScope;
use App\Models\PlaceManagerAuthority;
use App\Models\PlaceManagerAuthorityScope;

/** @extends ApplicationFactory<PlaceManagerAuthorityScope> */
final class PlaceManagerAuthorityScopeFactory extends ApplicationFactory
{
    protected $model = PlaceManagerAuthorityScope::class;

    public function definition(): array
    {
        return [
            'place_manager_authority_id' => PlaceManagerAuthority::factory(),
            'scope' => fake()->randomElement(PlaceManagementScope::cases()),
            'created_at' => now(),
        ];
    }
}

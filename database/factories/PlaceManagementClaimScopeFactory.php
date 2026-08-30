<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceManagementScope;
use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagementClaimScope;

/** @extends ApplicationFactory<PlaceManagementClaimScope> */
final class PlaceManagementClaimScopeFactory extends ApplicationFactory
{
    protected $model = PlaceManagementClaimScope::class;

    public function definition(): array
    {
        return [
            'place_management_claim_id' => PlaceManagementClaim::factory(),
            'scope' => fake()->randomElement(PlaceManagementScope::cases()),
            'created_at' => now(),
        ];
    }
}

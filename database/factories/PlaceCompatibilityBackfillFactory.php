<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceCompatibilityBackfill;
use App\Models\User;
use App\Models\UserDomainState;

/** @extends ApplicationFactory<PlaceCompatibilityBackfill> */
final class PlaceCompatibilityBackfillFactory extends ApplicationFactory
{
    protected $model = PlaceCompatibilityBackfill::class;

    public function definition(): array
    {
        return [
            'user_domain_state_id' => UserDomainState::factory(),
            'user_id' => User::factory(),
            'contribution_type' => 'review',
            'legacy_key' => $this->faker->uuid(),
            'payload_checksum' => hash('sha256', $this->faker->uuid()),
            'target_type' => null,
            'target_id' => null,
            'status' => 'skipped',
            'error_code' => 'fixture',
        ];
    }
}

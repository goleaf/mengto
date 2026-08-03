<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrganizationRestrictionCapability;
use App\Models\Organization;
use App\Models\OrganizationRestriction;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<OrganizationRestriction> */
final class OrganizationRestrictionFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'applied_by_user_id' => null,
            'capability' => OrganizationRestrictionCapability::CreateEvents,
            'reason_code' => 'factory-safety-review',
            'idempotency_key' => 'factory:organization-restriction:'.Str::lower((string) Str::ulid()),
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addWeek(),
            'revoked_by_user_id' => null,
            'revoked_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addWeek(),
            'revoked_by_user_id' => null,
            'revoked_at' => null,
        ]);
    }
}

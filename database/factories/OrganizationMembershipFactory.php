<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;

/** @extends ApplicationFactory<OrganizationMembership> */
final class OrganizationMembershipFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'user_id' => User::factory(),
            'invited_by_user_id' => null,
            'role' => OrganizationRole::Member,
            'status' => OrganizationMembershipStatus::Active,
            'joined_at' => now(),
            'expires_at' => null,
            'lock_version' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => OrganizationMembershipStatus::Active,
            'joined_at' => now(),
            'expires_at' => null,
            'removed_at' => null,
            'removed_by_user_id' => null,
            'removal_reason_code' => null,
        ]);
    }

    public function eventManager(): static
    {
        return $this->state(fn (): array => ['role' => OrganizationRole::EventManager]);
    }

    public function financeManager(): static
    {
        return $this->state(fn (): array => ['role' => OrganizationRole::FinanceManager]);
    }

    public function safetyLead(): static
    {
        return $this->state(fn (): array => ['role' => OrganizationRole::SafetyLead]);
    }

    public function marketplaceManager(): static
    {
        return $this->state(fn (): array => ['role' => OrganizationRole::MarketplaceManager]);
    }

    public function shelterCoordinator(): static
    {
        return $this->state(fn (): array => ['role' => OrganizationRole::ShelterCoordinator]);
    }

    public function auditor(): static
    {
        return $this->state(fn (): array => ['role' => OrganizationRole::Auditor]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrganizationMembershipStatus;
use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\OrganizationType;
use App\Enums\OrganizationVerificationStatus;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<Organization> */
final class OrganizationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());

        return [
            'owner_user_id' => User::factory(),
            'stable_key' => "organization-{$key}",
            'slug' => "organization-{$key}",
            'creation_idempotency_key' => "factory:organization:{$key}",
            'name' => fake()->unique()->company(),
            'summary' => fake()->sentence(),
            'type' => OrganizationType::Community,
            'status' => OrganizationStatus::Active,
            'verification_status' => OrganizationVerificationStatus::NotAssessed,
            'default_locale' => 'en',
            'public_region' => 'lt-vilnius',
            'lock_version' => 0,
            'metadata' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Organization $organization): void {
            OrganizationMembership::query()->firstOrCreate(
                [
                    'organization_id' => $organization->id,
                    'user_id' => $organization->owner_user_id,
                ],
                [
                    'role' => OrganizationRole::Owner,
                    'status' => OrganizationMembershipStatus::Active,
                    'joined_at' => now(),
                    'lock_version' => 0,
                ],
            );
        });
    }

    public function forOwner(User $owner): static
    {
        return $this->state(fn (): array => ['owner_user_id' => $owner->id]);
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'verification_status' => OrganizationVerificationStatus::Verified,
            'verification_source' => 'factory-review',
            'verified_at' => now(),
            'verification_expires_at' => now()->addYear(),
        ]);
    }
}

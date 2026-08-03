<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrganizationInvitationStatus;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<OrganizationInvitation> */
final class OrganizationInvitationFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());

        return [
            'organization_id' => Organization::factory(),
            'invited_user_id' => User::factory(),
            'invited_by_user_id' => null,
            'stable_key' => "organization-invitation-{$key}",
            'idempotency_key' => "factory:organization-invitation:{$key}",
            'token_hash' => hash('sha256', "organization-invitation-token-{$key}"),
            'role' => OrganizationRole::Member,
            'status' => OrganizationInvitationStatus::Pending,
            'expires_at' => now()->addDays(7),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => OrganizationInvitationStatus::Pending,
            'expires_at' => now()->addDays(7),
            'responded_at' => null,
            'revoked_at' => null,
            'revoked_by_user_id' => null,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceManagerAuthorityStatus;
use App\Enums\PlaceManagementClaimStatus;
use App\Enums\PlaceManagementRole;
use App\Models\Place;
use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagerAuthority;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceManagerAuthority> */
final class PlaceManagerAuthorityFactory extends ApplicationFactory
{
    protected $model = PlaceManagerAuthority::class;

    public function definition(): array
    {
        return [
            'stable_key' => 'place-authority-'.Str::lower((string) Str::ulid()),
            'place_id' => Place::factory()->public(),
            'source_claim_id' => PlaceManagementClaim::factory()->state([
                'status' => PlaceManagementClaimStatus::Approved,
                'active_conflict_key' => null,
            ]),
            'granted_to_user_id' => User::factory(),
            'represented_organization_id' => null,
            'granted_by_user_id' => User::factory()->administrator(),
            'role' => PlaceManagementRole::Owner,
            'status' => PlaceManagerAuthorityStatus::Active,
            'starts_at' => now(),
            'expires_at' => now()->addYear(),
            'ended_by_user_id' => null,
            'ended_at' => null,
            'end_reason_code' => null,
            'superseded_by_authority_id' => null,
            'active_authority_key' => fn (array $attributes): string => hash('sha256', implode('|', [
                (string) $attributes['place_id'],
                (string) ($attributes['granted_to_user_id'] ?? 'organization'),
                (string) ($attributes['represented_organization_id'] ?? 'personal'),
                $attributes['role'] instanceof PlaceManagementRole
                    ? $attributes['role']->value
                    : (string) $attributes['role'],
            ])),
            'lock_version' => 0,
        ];
    }
}

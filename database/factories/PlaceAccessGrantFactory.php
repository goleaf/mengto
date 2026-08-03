<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceAccessGrantStatus;
use App\Enums\PlaceAccessPurpose;
use App\Models\Place;
use App\Models\PlaceAccessGrant;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceAccessGrant> */
final class PlaceAccessGrantFactory extends ApplicationFactory
{
    protected $model = PlaceAccessGrant::class;

    public function definition(): array
    {
        return [
            'place_id' => Place::factory()->private(),
            'user_id' => User::factory(),
            'event_id' => null,
            'issued_by_user_id' => User::factory(),
            'purpose' => PlaceAccessPurpose::ProfessionalVisit,
            'status' => PlaceAccessGrantStatus::Active,
            'may_view_exact_location' => true,
            'valid_from' => now()->subMinute(),
            'valid_until' => now()->addDay(),
            'revoked_at' => null,
            'revocation_reason_code' => null,
            'idempotency_key' => 'place-grant-'.Str::lower((string) Str::ulid()),
            'metadata' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => PlaceAccessGrantStatus::Active,
            'valid_from' => now()->subMinute(),
            'valid_until' => now()->addDay(),
            'revoked_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => PlaceAccessGrantStatus::Expired,
            'valid_from' => now()->subDays(2),
            'valid_until' => now()->subDay(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagementReviewerRecusal;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceManagementReviewerRecusal> */
final class PlaceManagementReviewerRecusalFactory extends ApplicationFactory
{
    protected $model = PlaceManagementReviewerRecusal::class;

    public function definition(): array
    {
        return [
            'place_management_claim_id' => PlaceManagementClaim::factory(),
            'reviewer_user_id' => User::factory()->administrator(),
            'reason_code' => 'personal-conflict',
            'private_note' => null,
            'idempotency_key' => (string) Str::uuid(),
            'created_at' => now(),
        ];
    }
}

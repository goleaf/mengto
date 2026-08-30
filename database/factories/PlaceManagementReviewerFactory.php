<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceManagementReviewerRole;
use App\Models\PlaceManagementReviewer;
use App\Models\User;

/** @extends ApplicationFactory<PlaceManagementReviewer> */
final class PlaceManagementReviewerFactory extends ApplicationFactory
{
    protected $model = PlaceManagementReviewer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->administrator(),
            'appointed_by_user_id' => User::factory()->administrator(),
            'role' => PlaceManagementReviewerRole::Reviewer,
            'is_active' => true,
            'appointed_at' => now(),
            'expires_at' => null,
            'revoked_at' => null,
        ];
    }
}

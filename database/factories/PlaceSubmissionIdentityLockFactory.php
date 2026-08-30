<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceSubmissionIdentityLock;

/** @extends ApplicationFactory<PlaceSubmissionIdentityLock> */
final class PlaceSubmissionIdentityLockFactory extends ApplicationFactory
{
    protected $model = PlaceSubmissionIdentityLock::class;

    public function definition(): array
    {
        return [
            'identity_hash' => hash('sha256', fake()->unique()->uuid()),
            'first_submission_id' => null,
            'lock_version' => 0,
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionRevision;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceSubmissionRevision> */
final class PlaceSubmissionRevisionFactory extends ApplicationFactory
{
    protected $model = PlaceSubmissionRevision::class;

    public function definition(): array
    {
        return [
            'place_submission_id' => PlaceSubmission::factory(),
            'submitted_by_user_id' => User::factory(),
            'stable_key' => 'place-submission-revision-'.Str::lower((string) Str::ulid()),
            'revision_number' => 1,
            'kind' => 'initial',
            'summary' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}

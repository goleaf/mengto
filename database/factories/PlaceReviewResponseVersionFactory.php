<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceReviewResponse;
use App\Models\PlaceReviewResponseVersion;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceReviewResponseVersion> */
final class PlaceReviewResponseVersionFactory extends ApplicationFactory
{
    protected $model = PlaceReviewResponseVersion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_review_response_id' => PlaceReviewResponse::factory(),
            'editor_user_id' => User::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'version' => 1,
            'body' => fake()->paragraph(),
            'reason' => null,
            'created_at' => now(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceReview;
use App\Models\PlaceReviewResponse;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceReviewResponse> */
final class PlaceReviewResponseFactory extends ApplicationFactory
{
    protected $model = PlaceReviewResponse::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_review_id' => PlaceReview::factory(),
            'author_user_id' => User::factory(),
            'stable_key' => 'place-review-response-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'body' => fake()->paragraph(),
            'current_version' => 1,
        ];
    }
}

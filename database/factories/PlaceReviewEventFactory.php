<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaceReview;
use App\Models\PlaceReviewEvent;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceReviewEvent> */
final class PlaceReviewEventFactory extends ApplicationFactory
{
    protected $model = PlaceReviewEvent::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'place_review_id' => PlaceReview::factory(),
            'actor_user_id' => User::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'event_type' => 'submitted',
            'from_status' => null,
            'to_status' => 'published',
            'public_summary_key' => 'places.reviews.events.submitted',
            'private_note' => null,
            'created_at' => now(),
        ];
    }
}

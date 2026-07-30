<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ExpertProfile;
use App\Models\Review;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReview
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(ExpertProfile $profile, array $data): Review
    {
        return DB::transaction(function () use ($profile, $data): Review {
            $booking = Booking::query()
                ->select(['id', 'expert_profile_id', 'service_id', 'client_key', 'status'])
                ->where('expert_profile_id', $profile->id)
                ->where('client_key', $this->actor->key())
                ->whereKey($data['booking_id'])
                ->firstOrFail();

            if ($booking->status !== BookingStatus::Completed) {
                throw ValidationException::withMessages([
                    'booking_id' => 'A verified review is available after the consultation is completed.',
                ]);
            }

            if (Review::query()->where('booking_id', $booking->id)->exists()) {
                throw ValidationException::withMessages(['booking_id' => 'This booking already has a review.']);
            }

            $review = Review::query()->create([
                'expert_profile_id' => $profile->id,
                'service_id' => $booking->service_id,
                'booking_id' => $booking->id,
                'reviewer_key' => $this->actor->key(),
                'reviewer_name' => $this->actor->identity()['name'],
                'is_verified_client' => true,
                'is_anonymous' => (bool) ($data['is_anonymous'] ?? false),
                'rating' => $data['rating'],
                'communication_rating' => $data['communication_rating'],
                'clarity_rating' => $data['clarity_rating'],
                'organization_rating' => $data['organization_rating'],
                'price_transparency_rating' => $data['price_transparency_rating'],
                'body' => $data['body'],
                'status' => 'published',
            ]);

            $reviews = Review::query()
                ->published()
                ->where('expert_profile_id', $profile->id);

            $profile->update([
                'review_count' => (clone $reviews)->count(),
                'verified_review_count' => (clone $reviews)->where('is_verified_client', true)->count(),
                'review_average' => round((float) (clone $reviews)->avg('rating'), 2),
            ]);

            AuditLog::query()->create([
                'expert_profile_id' => $profile->id,
                'booking_id' => $booking->id,
                'actor_key' => $this->actor->key(),
                'actor_role' => 'verified-client',
                'action' => 'review.created',
                'target_type' => Review::class,
                'target_id' => (string) $review->id,
                'metadata' => ['rating' => $review->rating],
            ]);

            return $review;
        });
    }
}

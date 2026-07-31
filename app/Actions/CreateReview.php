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
                    'booking_id' => __('messages.a_verified_review_is_available_after_the_consultation_is_0fb47fcedc'),
                ]);
            }

            if (Review::query()->where('booking_id', $booking->id)->exists()) {
                throw ValidationException::withMessages(['booking_id' => __('messages.this_booking_already_has_a_review_8406ae4f0e')]);
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

            $reviewSummary = ExpertProfile::query()
                ->whereKey($profile->getKey())
                ->withPublishedReviewSummary()
                ->firstOrFail();

            $profile->update([
                'review_count' => (int) $reviewSummary->getAttribute('published_reviews_count'),
                'verified_review_count' => (int) $reviewSummary->getAttribute('published_verified_reviews_count'),
                'review_average' => round((float) $reviewSummary->getAttribute('published_review_average'), 2),
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

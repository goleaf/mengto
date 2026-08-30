<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Models\Booking;
use App\Models\ExpertProfile;
use App\Models\Review;
use App\Models\Service;
use LogicException;

/** @extends ApplicationFactory<Review> */
class ReviewFactory extends ApplicationFactory
{
    public function definition(): array
    {
        return [
            'expert_profile_id' => null,
            'service_id' => null,
            'booking_id' => null,
            'reviewer_key' => fake()->unique()->userName(),
            'reviewer_name' => fake()->name(),
            'is_verified_client' => true,
            'is_anonymous' => false,
            'rating' => 5,
            'communication_rating' => 5,
            'clarity_rating' => 5,
            'organization_rating' => 5,
            'price_transparency_rating' => 5,
            'body' => fake()->paragraphs(2, true),
            'status' => ReviewStatus::Published,
        ];
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(function (Review $review): void {
                $booking = $this->completedBookingFor($review);

                $review->expert_profile_id = $booking->expert_profile_id;
                $review->service_id = $booking->service_id;
                $review->booking_id = $booking->id;
                $review->reviewer_key = $booking->client_key;
                $review->reviewer_name = $booking->client_name;
            })
            ->afterCreating(static function (Review $review): void {
                self::refreshReviewSummary($review->expert_profile_id);
            });
    }

    private function completedBookingFor(Review $review): Booking
    {
        if ($review->booking_id !== null) {
            $booking = Booking::query()->findOrFail($review->booking_id);
            $service = Service::query()->findOrFail($booking->service_id);

            if (
                $booking->status !== BookingStatus::Completed
                || $booking->completed_at === null
                || $booking->expert_profile_id !== $service->expert_profile_id
                || ($review->service_id !== null && $review->service_id !== $service->id)
                || ($review->expert_profile_id !== null
                    && $review->expert_profile_id !== $service->expert_profile_id)
            ) {
                throw new LogicException('Review factories require a coherent completed booking.');
            }

            return $booking;
        }

        $service = $review->service_id !== null
            ? Service::query()->findOrFail($review->service_id)
            : Service::factory()->create([
                'expert_profile_id' => $review->expert_profile_id ?? ExpertProfile::factory(),
            ]);

        return Booking::factory()->completed()->create([
            'expert_profile_id' => $service->expert_profile_id,
            'service_id' => $service->id,
        ]);
    }

    private static function refreshReviewSummary(int $profileId): void
    {
        $summary = ExpertProfile::query()
            ->whereKey($profileId)
            ->withPublishedReviewSummary()
            ->firstOrFail();

        ExpertProfile::query()->whereKey($profileId)->update([
            'review_count' => (int) $summary->getAttribute('published_reviews_count'),
            'verified_review_count' => (int) $summary->getAttribute('published_verified_reviews_count'),
            'review_average' => round((float) $summary->getAttribute('published_review_average'), 2),
        ]);
    }
}

<?php

declare(strict_types=1);

use App\Enums\BookingStatus;
use App\Enums\PublicationStatus;
use App\Enums\ReviewStatus;
use App\Models\Booking;
use App\Models\ExpertProfile;
use App\Models\Publication;
use App\Models\Review;
use App\Models\Service;

test('verified review factory creates one coherent completed booking aggregate', function () {
    $review = Review::factory()->create();

    expect($review->booking_id)->not->toBeNull();

    $booking = $review->booking()->firstOrFail();
    $service = $review->service()->firstOrFail();
    $profile = $review->expertProfile()->firstOrFail();
    $client = $booking->client()->firstOrFail();

    expect($booking->status)->toBe(BookingStatus::Completed)
        ->and($booking->completed_at)->not->toBeNull()
        ->and($booking->service_id)->toBe($service->id)
        ->and($booking->expert_profile_id)->toBe($profile->id)
        ->and($service->expert_profile_id)->toBe($profile->id)
        ->and($review->reviewer_key)->toBe($client->actor_key)
        ->and($review->reviewer_name)->toBe($client->name)
        ->and($review->is_verified_client)->toBeTrue()
        ->and($profile->review_count)->toBe(1)
        ->and($profile->verified_review_count)->toBe(1)
        ->and($profile->review_average)->toBe('5.00')
        ->and(Review::query()->count())->toBe(1)
        ->and(Booking::query()->count())->toBe(1)
        ->and(Service::query()->count())->toBe(1)
        ->and(ExpertProfile::query()->count())->toBe(1);
});

test('repeated review creation maintains published aggregates without duplicate services or profiles', function () {
    $profile = ExpertProfile::factory()->create();
    $service = Service::factory()->create(['expert_profile_id' => $profile->id]);

    Review::factory()
        ->count(3)
        ->sequence(
            ['rating' => 5],
            ['rating' => 4],
            ['rating' => 3],
        )
        ->create([
            'expert_profile_id' => $profile->id,
            'service_id' => $service->id,
        ]);
    Review::factory()->create([
        'expert_profile_id' => $profile->id,
        'service_id' => $service->id,
        'rating' => 1,
        'is_verified_client' => false,
    ]);
    Review::factory()->create([
        'expert_profile_id' => $profile->id,
        'service_id' => $service->id,
        'rating' => 1,
        'status' => ReviewStatus::Hidden,
    ]);

    $profile->refresh();
    $reviews = Review::query()->where('expert_profile_id', $profile->id)->get();

    expect($reviews)->toHaveCount(5)
        ->and($reviews->every(
            static fn (Review $review): bool => $review->booking_id !== null
                && $review->service_id === $service->id
                && $review->expert_profile_id === $profile->id,
        ))->toBeTrue()
        ->and($profile->review_count)->toBe(4)
        ->and($profile->verified_review_count)->toBe(3)
        ->and($profile->review_average)->toBe('3.25')
        ->and(Booking::query()->count())->toBe(5)
        ->and(Service::query()->count())->toBe(1)
        ->and(ExpertProfile::query()->count())->toBe(1);
});

test('repeated publication creation maintains the published profile count', function () {
    $profile = ExpertProfile::factory()->create();

    Publication::factory()->count(3)->create([
        'expert_profile_id' => $profile->id,
    ]);
    Publication::factory()->create([
        'expert_profile_id' => $profile->id,
        'status' => PublicationStatus::Draft,
        'published_at' => null,
    ]);

    expect($profile->refresh()->publication_count)->toBe(3)
        ->and(Publication::query()->count())->toBe(4)
        ->and(ExpertProfile::query()->count())->toBe(1);
});

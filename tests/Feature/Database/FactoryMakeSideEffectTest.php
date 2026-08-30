<?php

declare(strict_types=1);

use App\Models\Booking;
use App\Models\ForumCategory;
use App\Models\ForumMentorScope;
use App\Models\ForumMentorship;
use App\Models\ForumTopic;
use App\Models\ForumTopicMove;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('making child factories does not write to explicitly supplied aggregates', function (): void {
    $service = Service::factory()->create();
    $booking = Booking::factory()->completed()->create([
        'expert_profile_id' => $service->expert_profile_id,
        'service_id' => $service->id,
    ]);

    $destination = ForumCategory::factory()->create();
    $source = ForumCategory::factory()->create();
    $topic = ForumTopic::factory()->create(['forum_category_id' => $destination->id]);
    $actor = User::factory()->administrator()->create();

    $scope = ForumMentorScope::factory()->create();
    $scope->loadMissing('profile');
    $mentee = User::factory()->create();

    $connection = DB::connection();
    $connection->flushQueryLog();
    $connection->enableQueryLog();

    try {
        $review = Review::factory()->make([
            'expert_profile_id' => $service->expert_profile_id,
            'service_id' => $service->id,
            'booking_id' => $booking->id,
        ]);
        $move = ForumTopicMove::factory()->make([
            'forum_topic_id' => $topic->id,
            'from_forum_category_id' => $source->id,
            'to_forum_category_id' => $destination->id,
            'actor_user_id' => $actor->id,
        ]);
        $mentorship = ForumMentorship::factory()->make([
            'forum_mentor_scope_id' => $scope->id,
            'mentor_user_id' => $scope->profile->user_id,
            'mentee_user_id' => $mentee->id,
            'mentorship_type' => $scope->mentorship_type,
        ]);

        $writes = array_values(array_filter(
            $connection->getQueryLog(),
            static fn (array $query): bool => preg_match(
                '/^\s*(insert|update|delete)\b/i',
                $query['query'],
            ) === 1,
        ));
    } finally {
        $connection->disableQueryLog();
        $connection->flushQueryLog();
    }

    expect($review->exists)->toBeFalse()
        ->and($move->exists)->toBeFalse()
        ->and($mentorship->exists)->toBeFalse()
        ->and($writes)->toBe([]);
});

test('review factory rejects an incoherent explicit booking without changing it', function (): void {
    $service = Service::factory()->create();
    $booking = Booking::factory()->create([
        'expert_profile_id' => $service->expert_profile_id,
        'service_id' => $service->id,
    ]);

    expect(fn () => Review::factory()->make([
        'booking_id' => $booking->id,
    ]))->toThrow(LogicException::class, 'Review factories require a coherent completed booking.');

    expect($booking->refresh()->status->value)->toBe('confirmed')
        ->and($booking->completed_at)->toBeNull();
});

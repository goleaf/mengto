<?php

declare(strict_types=1);

use App\Actions\DeletePlaceReview;
use App\Actions\ModeratePlaceReview;
use App\Actions\RestorePlaceReview;
use App\Actions\SubmitPlaceReview;
use App\Actions\UpdatePlaceReview;
use App\Actions\UpsertPlaceReviewResponse;
use App\Enums\PlaceManagementScope;
use App\Enums\PlaceReviewAnonymityMode;
use App\Enums\PlaceReviewModerationStatus;
use App\Models\PetProfile;
use App\Models\Place;
use App\Models\PlaceManagementClaim;
use App\Models\PlaceManagerAuthority;
use App\Models\PlaceManagerAuthorityScope;
use App\Models\PlaceReview;
use App\Models\PlaceReviewResponse;
use App\Models\User;
use App\Models\UserDomainState;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

test('a review keeps the authenticated author accountable while using anonymous presentation and compatibility visit evidence', function (): void {
    $author = User::factory()->create(['name' => 'Zoe Example']);
    $place = Place::factory()->public()->create();
    $pet = PetProfile::factory()->for($author)->create();
    UserDomainState::factory()->for($author)->create([
        'namespace' => 'places.state.v1',
        'payload' => [
            'visited' => [$place->stable_key => ['visited_at' => now()->toAtomString(), 'pet' => 'zoe-pet']],
        ],
    ]);

    $review = app(SubmitPlaceReview::class)->handle(
        actor: $author,
        place: $place,
        ratingOverall: 5,
        ratingService: 4,
        ratingAccessibility: 5,
        ratingPetFriendliness: 5,
        body: 'The staff made the accessible entrance and water station easy to find.',
        anonymityMode: PlaceReviewAnonymityMode::Anonymous,
        petProfileId: $pet->id,
        idempotencyKey: (string) Str::uuid(),
    );

    expect($review->author_user_id)->toBe($author->id)
        ->and($review->author->name)->toBe('Zoe Example')
        ->and($review->pet_profile_id)->toBe($pet->id)
        ->and($review->verified_visit)->toBeTrue()
        ->and($review->anonymity_mode)->toBe(PlaceReviewAnonymityMode::Anonymous)
        ->and($review->versions)->toHaveCount(1)
        ->and($review->events)->toHaveCount(1);
});

test('a review submission is idempotent and cannot be duplicated for the same place author', function (): void {
    $place = Place::factory()->public()->create();
    $key = (string) Str::uuid();
    $payload = [
        'actor' => $this->authenticatedUser,
        'place' => $place,
        'ratingOverall' => 4,
        'ratingService' => 4,
        'ratingAccessibility' => null,
        'ratingPetFriendliness' => 5,
        'body' => 'The calm outdoor area gave us enough room to settle before the appointment.',
        'anonymityMode' => PlaceReviewAnonymityMode::Named,
        'petProfileId' => null,
        'idempotencyKey' => $key,
    ];

    $first = app(SubmitPlaceReview::class)->handle(...$payload);
    $second = app(SubmitPlaceReview::class)->handle(...$payload);

    expect($second->is($first))->toBeTrue()
        ->and(PlaceReview::query()->count())->toBe(1)
        ->and($first->versions()->count())->toBe(1);

    $duplicatePayload = [
        ...$payload,
        'idempotencyKey' => (string) Str::uuid(),
        'body' => 'A second review cannot replace or stack over the existing author review.',
    ];

    app(SubmitPlaceReview::class)->handle(...$duplicatePayload);
})->throws(ValidationException::class);

test('a review cannot attach a pet profile the author does not currently manage', function (): void {
    $place = Place::factory()->public()->create();
    $unrelatedPet = PetProfile::factory()->for(User::factory())->create();

    app(SubmitPlaceReview::class)->handle(
        actor: $this->authenticatedUser,
        place: $place,
        ratingOverall: 4,
        ratingService: null,
        ratingAccessibility: null,
        ratingPetFriendliness: 4,
        body: 'The outdoor water bowl was clean and the staff greeted us at the entrance.',
        anonymityMode: PlaceReviewAnonymityMode::Named,
        petProfileId: $unrelatedPet->id,
        idempotencyKey: (string) Str::uuid(),
    );
})->throws(ValidationException::class);

test('review edits moderation deletion and restoration preserve immutable history', function (): void {
    $place = Place::factory()->public()->create();
    $review = app(SubmitPlaceReview::class)->handle(
        actor: $this->authenticatedUser,
        place: $place,
        ratingOverall: 3,
        ratingService: null,
        ratingAccessibility: null,
        ratingPetFriendliness: null,
        body: 'The place was welcoming, although the entrance directions were difficult to follow.',
        anonymityMode: PlaceReviewAnonymityMode::Named,
        petProfileId: null,
        idempotencyKey: (string) Str::uuid(),
    );

    $updated = app(UpdatePlaceReview::class)->handle(
        actor: $this->authenticatedUser,
        review: $review,
        ratingOverall: 4,
        ratingService: 4,
        ratingAccessibility: 3,
        ratingPetFriendliness: null,
        body: 'The place was welcoming and staff clarified the accessible entrance directions quickly.',
        anonymityMode: PlaceReviewAnonymityMode::Named,
        reason: 'Returning visit clarified the entrance experience.',
        idempotencyKey: (string) Str::uuid(),
    );

    $moderator = User::factory()->administrator()->create();
    $hidden = app(ModeratePlaceReview::class)->handle(
        actor: $moderator,
        review: $updated,
        status: PlaceReviewModerationStatus::Hidden,
        reason: 'Pending evidence review.',
        idempotencyKey: (string) Str::uuid(),
    );
    $deleted = app(DeletePlaceReview::class)->handle(
        actor: $this->authenticatedUser,
        review: $hidden,
        reason: 'The author requested a temporary removal while supporting detail is checked.',
        idempotencyKey: (string) Str::uuid(),
    );
    $restored = app(RestorePlaceReview::class)->handle(
        actor: $this->authenticatedUser,
        review: $deleted,
        idempotencyKey: (string) Str::uuid(),
    );

    expect($restored->current_version)->toBe(2)
        ->and($restored->moderation_status)->toBe(PlaceReviewModerationStatus::Hidden)
        ->and($restored->deleted_at)->toBeNull()
        ->and($restored->versions()->count())->toBe(2)
        ->and($restored->events()->pluck('event_type')->all())
        ->toBe(['submitted', 'updated', 'moderated', 'deleted', 'restored']);
});

test('only a current place manager can create or edit the single review response', function (): void {
    $manager = User::factory()->create();
    $otherManager = User::factory()->create();
    $place = Place::factory()->public()->create();
    $claim = PlaceManagementClaim::factory()->for($place)->for($manager, 'claimant')->create();
    $authority = PlaceManagerAuthority::factory()
        ->for($place)
        ->for($claim, 'sourceClaim')
        ->for($manager, 'grantedTo')
        ->create();
    PlaceManagerAuthorityScope::factory()->for($authority, 'authority')->create([
        'scope' => PlaceManagementScope::OfficialResponses,
    ]);
    $review = app(SubmitPlaceReview::class)->handle(
        actor: $this->authenticatedUser,
        place: $place,
        ratingOverall: 4,
        ratingService: 4,
        ratingAccessibility: null,
        ratingPetFriendliness: null,
        body: 'The team welcomed us and explained the pet entry process clearly.',
        anonymityMode: PlaceReviewAnonymityMode::Named,
        petProfileId: null,
        idempotencyKey: (string) Str::uuid(),
    );

    $response = app(UpsertPlaceReviewResponse::class)->handle(
        actor: $manager,
        review: $review,
        body: 'Thank you for visiting. We have also added clearer entrance signs this week.',
        idempotencyKey: (string) Str::uuid(),
        reason: null,
    );

    expect($response->place_review_id)->toBe($review->id)
        ->and($response->author_user_id)->toBe($manager->id)
        ->and($response->versions)->toHaveCount(1)
        ->and(PlaceReviewResponse::query()->count())->toBe(1);

    $editedResponse = app(UpsertPlaceReviewResponse::class)->handle(
        actor: $manager,
        review: $review,
        body: 'Thank you for visiting. We added clearer entrance signs and a step-free route map.',
        idempotencyKey: (string) Str::uuid(),
        reason: 'Updated after the route map was installed.',
    );

    expect($editedResponse->id)->toBe($response->id)
        ->and($editedResponse->current_version)->toBe(2)
        ->and($editedResponse->versions)->toHaveCount(2);

    app(UpsertPlaceReviewResponse::class)->handle(
        actor: $otherManager,
        review: $review,
        body: 'A different manager must not overwrite the accountable response author.',
        idempotencyKey: (string) Str::uuid(),
        reason: 'Not allowed.',
    );
})->throws(AuthorizationException::class);

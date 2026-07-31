<?php

declare(strict_types=1);

use App\Enums\ExpertProfileStatus;
use App\Enums\KnowledgeStatus;
use App\Models\Booking;
use App\Models\CareJournal;
use App\Models\Consultation;
use App\Models\ExpertProfile;
use App\Models\ForumAnswer;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;
use App\Models\Listing;
use App\Models\MedicalRecord;
use App\Models\Order;
use App\Models\PetProfile;
use App\Models\Reservation;
use App\Models\SearchCase;
use App\Models\Service;
use App\Models\SmartDevice;
use App\Models\User;
use App\Policies\BookingPolicy;
use App\Policies\CareJournalPolicy;
use App\Policies\ConsultationPolicy;
use App\Policies\ExpertProfilePolicy;
use App\Policies\ForumAnswerPolicy;
use App\Policies\ForumTopicPolicy;
use App\Policies\KnowledgeArticlePolicy;
use App\Policies\ListingPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Policies\OrderPolicy;
use App\Policies\PetProfilePolicy;
use App\Policies\SearchCasePolicy;
use App\Policies\SmartDevicePolicy;
use App\Policies\UserPolicy;

test('booking policy covers clients experts outsiders blocked users and destructive states', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $expert = User::factory()->create(['actor_key' => 'booking-expert']);
    $profile = ExpertProfile::factory()->create(['owner_key' => $expert->actor_key]);
    $service = Service::factory()->for($profile)->create();
    $booking = Booking::factory()
        ->for($profile)
        ->for($service)
        ->create(['client_key' => $owner->actor_key]);
    $policy = app(BookingPolicy::class);

    expect([
        $policy->viewAny($owner),
        $policy->viewAny($blocked),
        $policy->viewAny(null),
        $policy->view($owner, $booking),
        $policy->view($expert, $booking),
        $policy->view($other, $booking),
        $policy->view($administrator, $booking),
        $policy->view($blocked, $booking),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $booking),
        $policy->update($expert, $booking),
        $policy->delete($owner, $booking),
        $policy->restore($owner, $booking),
        $policy->forceDelete($owner, $booking),
    ])->toBe([
        true, false, false, true, true, false, false, false,
        true, false, true, true, false, false, false,
    ]);
});

test('care journal policy denies every non-owner and every destructive action', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $journal = CareJournal::factory()->create(['owner_key' => $owner->actor_key]);
    $policy = app(CareJournalPolicy::class);

    expect([
        $policy->viewAny($owner),
        $policy->viewAny($blocked),
        $policy->viewAny(null),
        $policy->view($owner, $journal),
        $policy->view($other, $journal),
        $policy->view($administrator, $journal),
        $policy->view($blocked, $journal),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $journal),
        $policy->share($owner, $journal),
        $policy->export($owner, $journal),
        $policy->delete($owner, $journal),
        $policy->restore($owner, $journal),
        $policy->forceDelete($owner, $journal),
    ])->toBe([
        true, false, false, true, false, false, false, true,
        false, true, true, true, false, false, false,
    ]);
});

test('consultation policy separates client visibility from professional updates', function () {
    [$client, $other, $administrator, $blocked] = policyActors();
    $expert = User::factory()->create(['actor_key' => 'consultation-expert']);
    $profile = ExpertProfile::factory()->create(['owner_key' => $expert->actor_key]);
    $service = Service::factory()->for($profile)->create();
    $booking = Booking::factory()
        ->for($profile)
        ->for($service)
        ->create(['client_key' => $client->actor_key]);
    $consultation = Consultation::factory()
        ->for($booking)
        ->for($profile, 'expertProfile')
        ->create();
    $policy = app(ConsultationPolicy::class);

    expect([
        $policy->viewAny($client),
        $policy->viewAny($blocked),
        $policy->view($client, $consultation),
        $policy->view($expert, $consultation),
        $policy->view($other, $consultation),
        $policy->view($administrator, $consultation),
        $policy->update($client, $consultation),
        $policy->update($expert, $consultation),
        $policy->update($blocked, $consultation),
        $policy->create($client),
        $policy->delete($expert, $consultation),
        $policy->restore($expert, $consultation),
        $policy->forceDelete($expert, $consultation),
    ])->toBe([
        true, false, true, true, false, false, false,
        true, false, false, false, false, false,
    ]);
});

test('expert profile policy preserves public visibility and owner-only management', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $published = ExpertProfile::factory()->create(['owner_key' => $owner->actor_key]);
    $draft = ExpertProfile::factory()->create([
        'owner_key' => $owner->actor_key,
        'status' => ExpertProfileStatus::Draft,
    ]);
    $policy = app(ExpertProfilePolicy::class);

    expect([
        $policy->viewAny(null),
        $policy->view(null, $published),
        $policy->view($other, $draft),
        $policy->view($owner, $draft),
        $policy->view($blocked, $draft),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $draft),
        $policy->update($administrator, $draft),
        $policy->delete($owner, $draft),
        $policy->restore($owner, $draft),
        $policy->forceDelete($owner, $draft),
    ])->toBe([
        true, true, false, true, false, true,
        false, true, false, true, false, false,
    ]);
});

test('forum answer policy covers visibility ownership blocked actors and destructive states', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $published = ForumAnswer::factory()->create([
        'author_key' => $owner->actor_key,
        'status' => 'published',
    ]);
    $hidden = ForumAnswer::factory()->create([
        'author_key' => $owner->actor_key,
        'status' => 'hidden',
    ]);
    $policy = app(ForumAnswerPolicy::class);

    expect([
        $policy->viewAny(null),
        $policy->view(null, $published),
        $policy->view($owner, $hidden),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $published),
        $policy->update($other, $published),
        $policy->update($administrator, $published),
        $policy->update($blocked, $published),
        $policy->delete($owner, $published),
        $policy->restore($owner, $published),
        $policy->forceDelete($owner, $published),
    ])->toBe([
        true, true, false, true, false, true,
        false, false, false, true, false, false,
    ]);
});

test('forum topic policy enforces private ownership and locked interaction states', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $private = ForumTopic::factory()->draft()->create([
        'author_key' => $owner->actor_key,
    ]);
    $public = ForumTopic::factory()->create(['author_key' => $owner->actor_key]);
    $locked = ForumTopic::factory()->create([
        'author_key' => $owner->actor_key,
        'is_locked' => true,
    ]);
    $closedComments = ForumTopic::factory()->create([
        'author_key' => $owner->actor_key,
        'comment_policy' => 'closed',
    ]);
    $policy = app(ForumTopicPolicy::class);

    expect([
        $policy->viewAny(null),
        $policy->view(null, $public),
        $policy->view($owner, $private),
        $policy->view($other, $private),
        $policy->view($administrator, $private),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $public),
        $policy->update($other, $public),
        $policy->answer($other, $public),
        $policy->answer($blocked, $public),
        $policy->answer($owner, $locked),
        $policy->comment($owner, $closedComments),
        $policy->delete($owner, $public),
        $policy->restore($owner, $public),
        $policy->forceDelete($owner, $public),
    ])->toBe([
        true, true, true, false, false, true, false, true,
        false, true, false, false, false, true, false, false,
    ]);
});

test('knowledge policy limits editorial mutation to active administrators', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $published = KnowledgeArticle::factory()->create();
    $draft = KnowledgeArticle::factory()->create(['status' => KnowledgeStatus::Draft]);
    $policy = app(KnowledgeArticlePolicy::class);

    expect([
        $policy->viewAny(null),
        $policy->view(null, $published),
        $policy->view($owner, $draft),
        $policy->proposeCorrection($owner, $published),
        $policy->proposeCorrection($blocked, $published),
        $policy->proposeCorrection($owner, $draft),
        $policy->create($owner),
        $policy->create($administrator),
        $policy->create($blocked),
        $policy->update($other, $published),
        $policy->update($administrator, $published),
        $policy->delete($administrator, $published),
        $policy->restore($administrator, $published),
        $policy->forceDelete($administrator, $published),
    ])->toBe([
        true, true, false, true, false, false, false,
        true, false, false, true, false, false, false,
    ]);
});

test('listing policy separates public viewing owner management and requester reservation', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $published = Listing::factory()->create(['owner_key' => $owner->actor_key]);
    $draft = Listing::factory()->draft()->create(['owner_key' => $owner->actor_key]);
    $reservation = Reservation::factory()->for($published)->create([
        'requester_key' => $other->actor_key,
    ]);
    $wrongReservation = Reservation::factory()->create([
        'requester_key' => $other->actor_key,
    ]);
    $policy = app(ListingPolicy::class);

    expect([
        $policy->viewAny(null),
        $policy->view(null, $published),
        $policy->view($owner, $draft),
        $policy->view($other, $draft),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $published),
        $policy->update($administrator, $published),
        $policy->delete($owner, $published),
        $policy->reserve($owner, $published),
        $policy->reserve($other, $published),
        $policy->reserve($blocked, $published),
        $policy->reserve($other, $draft),
        $policy->cancelReservation($other, $published, $reservation),
        $policy->cancelReservation($owner, $published, $reservation),
        $policy->cancelReservation($other, $published, $wrongReservation),
        $policy->restore($owner, $published),
        $policy->forceDelete($owner, $published),
    ])->toBe([
        true, true, true, false, true, false, true, false, true,
        false, true, false, false, true, false, false, false, false,
    ]);
});

test('medical record policy denies every non-owner and every destructive action', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $record = MedicalRecord::factory()->create(['owner_key' => $owner->actor_key]);
    $policy = app(MedicalRecordPolicy::class);

    expect([
        $policy->viewAny($owner),
        $policy->viewAny($blocked),
        $policy->viewAny(null),
        $policy->view($owner, $record),
        $policy->view($other, $record),
        $policy->view($administrator, $record),
        $policy->view($blocked, $record),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $record),
        $policy->share($owner, $record),
        $policy->export($owner, $record),
        $policy->delete($owner, $record),
        $policy->restore($owner, $record),
        $policy->forceDelete($owner, $record),
    ])->toBe([
        true, false, false, true, false, false, false, true,
        false, true, true, true, false, false, false,
    ]);
});

test('order policy permits only active participants and buyer reviews', function () {
    [$buyer, $other, $administrator, $blocked] = policyActors();
    $seller = User::factory()->create(['actor_key' => 'order-seller']);
    $listing = Listing::factory()->create(['owner_key' => $seller->actor_key]);
    $reservation = Reservation::factory()->for($listing)->create([
        'requester_key' => $buyer->actor_key,
    ]);
    $order = Order::factory()
        ->for($listing)
        ->for($reservation)
        ->create([
            'seller_key' => $seller->actor_key,
        ]);
    $policy = app(OrderPolicy::class);

    expect([
        $policy->viewAny($buyer),
        $policy->viewAny($blocked),
        $policy->view($buyer, $order),
        $policy->view($seller, $order),
        $policy->view($other, $order),
        $policy->view($administrator, $order),
        $policy->view($blocked, $order),
        $policy->dispute($buyer, $order),
        $policy->review($buyer, $order),
        $policy->review($seller, $order),
        $policy->create($buyer),
        $policy->update($buyer, $order),
        $policy->delete($buyer, $order),
    ])->toBe([
        true, false, true, true, false, false, false,
        true, true, false, false, false, false,
    ]);
});

test('search case policy separates public safety actions from owner coordination', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $active = SearchCase::factory()->create([
        'owner_key' => $owner->actor_key,
        'coordinator_key' => $owner->actor_key,
    ]);
    $private = SearchCase::factory()->create([
        'owner_key' => $owner->actor_key,
        'coordinator_key' => $owner->actor_key,
        'visibility' => 'private',
    ]);
    $closed = SearchCase::factory()->returned()->create([
        'owner_key' => $owner->actor_key,
        'coordinator_key' => $owner->actor_key,
    ]);
    $policy = app(SearchCasePolicy::class);

    expect([
        $policy->viewAny(null),
        $policy->view(null, $active),
        $policy->view($owner, $private),
        $policy->view($other, $private),
        $policy->view($administrator, $private),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $active),
        $policy->update($other, $active),
        $policy->coordinate($owner, $active),
        $policy->submitSighting(null, $active),
        $policy->submitSighting($other, $closed),
        $policy->volunteer($other, $active),
        $policy->volunteer($other, $closed),
        $policy->report($other, $active),
        $policy->viewPoster(null, $active),
        $policy->delete($owner, $active),
        $policy->restore($owner, $active),
        $policy->forceDelete($owner, $active),
    ])->toBe([
        true, true, true, false, false, true, false, true, false,
        true, true, false, true, false, true, true, false, false, false,
    ]);
});

test('smart device policy denies non-owners and blocks unsafe control states', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $device = SmartDevice::factory()->create(['owner_key' => $owner->actor_key]);
    $blockedDevice = SmartDevice::factory()->create([
        'owner_key' => $owner->actor_key,
        'is_blocked' => true,
    ]);
    $stolenDevice = SmartDevice::factory()->create([
        'owner_key' => $owner->actor_key,
        'is_reported_stolen' => true,
    ]);
    $policy = app(SmartDevicePolicy::class);

    expect([
        $policy->viewAny($owner),
        $policy->viewAny($blocked),
        $policy->viewAny(null),
        $policy->view($owner, $device),
        $policy->view($other, $device),
        $policy->view($administrator, $device),
        $policy->view($blocked, $device),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $device),
        $policy->control($owner, $device),
        $policy->control($owner, $blockedDevice),
        $policy->control($owner, $stolenDevice),
        $policy->controlCommand($owner, $stolenDevice, 'enable-lost-mode'),
        $policy->controlCommand($other, $stolenDevice, 'enable-lost-mode'),
        $policy->share($owner, $device),
        $policy->delete($owner, $device),
        $policy->restore($owner, $device),
        $policy->forceDelete($owner, $device),
    ])->toBe([
        true, false, false, true, false, false, false, true, false,
        true, true, false, false, true, false, true, false, false, false,
    ]);
});

test('pet profile policy separates public visibility from owner mutations', function () {
    [$owner, $other, $administrator, $blocked] = policyActors();
    $profile = PetProfile::factory()->for($owner)->create();
    $private = PetProfile::factory()->for($owner)->privateProfile()->create();
    $policy = app(PetProfilePolicy::class);

    expect([
        $policy->viewAny(null),
        $policy->view($other, $profile),
        $policy->view(null, $private),
        $policy->view($owner, $private),
        $policy->create($owner),
        $policy->create($blocked),
        $policy->update($owner, $profile),
        $policy->update($other, $profile),
        $policy->delete($owner, $profile),
        $policy->restore($owner, $profile),
        $policy->forceDelete($owner, $profile),
        $policy->forceDelete($administrator, $profile),
    ])->toBe([
        true, true, false, true, true, false, true, false,
        true, true, false, false,
    ]);
});

test('user policy limits profile preference updates to the active account owner', function () {
    [$owner, $other, , $blocked] = policyActors();
    $policy = app(UserPolicy::class);

    expect([
        $policy->update($owner, $owner),
        $policy->update($other, $owner),
        $policy->update($blocked, $blocked),
    ])->toBe([
        true, false, false,
    ]);
});

/**
 * @return array{User, User, User, User}
 */
function policyActors(): array
{
    return [
        User::factory()->create(['actor_key' => 'policy-owner']),
        User::factory()->create(['actor_key' => 'policy-other']),
        User::factory()->administrator()->create(['actor_key' => 'policy-administrator']),
        User::factory()->blocked()->create(['actor_key' => 'policy-blocked']),
    ];
}

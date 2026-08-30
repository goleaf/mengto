<?php

declare(strict_types=1);

use App\Models\AdoptionCase;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\ContentMediaAsset;
use App\Models\ContentPublication;
use App\Models\ContentPublicationEvent;
use App\Models\DeviceAutomationRun;
use App\Models\DocumentGrant;
use App\Models\ForumAnswer;
use App\Models\ForumComment;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventRegistrationPet;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionQuestion;
use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use App\Models\ForumGroupAnnouncement;
use App\Models\ForumGroupEvent;
use App\Models\ForumGroupFile;
use App\Models\ForumGroupInvitation;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipFeedback;
use App\Models\ForumMentorshipMessage;
use App\Models\ForumPoll;
use App\Models\ForumPollVote;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\ForumTopicMove;
use App\Models\ForumVote;
use App\Models\Listing;
use App\Models\ListingReview;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\PetProfileMedia;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\PlaceAccessGrant;
use App\Models\PlaceLocationVersion;
use App\Models\SearchCase;
use App\Models\Sighting;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Storage;

test('expert workflow factories preserve their aggregate expert', function () {
    $booking = Booking::factory()->create();
    $consultation = Consultation::factory()->create();
    $grant = DocumentGrant::factory()->create();

    expect($booking->expert_profile_id)->toBe($booking->service->expert_profile_id)
        ->and($consultation->expert_profile_id)->toBe($consultation->booking->expert_profile_id)
        ->and($grant->expert_profile_id)->toBe($grant->booking->expert_profile_id)
        ->and($grant->owner_key)->toBe($grant->booking->client_key);
});

test('device automation run factory uses its automation device', function () {
    $run = DeviceAutomationRun::factory()->create();

    expect($run->smart_device_id)->toBe($run->automation->smart_device_id);
});

test('event registration pet factory uses a pet managed by the participant', function () {
    $registrationPet = ForumEventRegistrationPet::factory()->create();

    expect($registrationPet->petProfile->user_id)
        ->toBe($registrationPet->registration->user_id);
});

test('content factories keep self-representation and media ownership coherent', function () {
    $publication = ContentPublication::factory()->create();
    $event = ContentPublicationEvent::factory()->create();
    $asset = ContentMediaAsset::factory()->create();
    $profileMedia = PetProfileMedia::factory()->create();

    expect($publication->publishingActor->user_id)->toBe($publication->real_author_user_id)
        ->and($event->actor_user_id)->toBe($event->publication->real_author_user_id)
        ->and($event->representedActor->user_id)->toBe($event->actor_user_id)
        ->and($asset->created_by_user_id)->toBe($asset->owner_user_id)
        ->and($profileMedia->asset->owner_user_id)->toBe($profileMedia->profile->user_id)
        ->and($profileMedia->attached_by_user_id)->toBe($profileMedia->profile->user_id);
});

test('place factories derive authority from the place owner', function () {
    $place = Place::factory()->create();
    $grant = PlaceAccessGrant::factory()->create();
    $audit = PlaceAccessAudit::factory()->create();
    $version = PlaceLocationVersion::factory()->create();

    expect($place->created_by_user_id)->toBe($place->owner_user_id)
        ->and($place->last_edited_by_user_id)->toBe($place->owner_user_id)
        ->and($grant->issued_by_user_id)->toBe($grant->place->owner_user_id)
        ->and($audit->place_id)->toBe($audit->grant->place_id)
        ->and($audit->user_id)->toBe($audit->grant->user_id)
        ->and($version->changed_by_user_id)->toBe($version->place->owner_user_id);
});

test('group content factories use an active group member', function () {
    Storage::fake('local');

    $activity = ForumGroupActivity::factory()->create();
    $announcement = ForumGroupAnnouncement::factory()->create();
    $file = ForumGroupFile::factory()->create();
    $event = ForumGroupEvent::factory()->create();
    $poll = ForumPoll::factory()->create();
    $vote = ForumPollVote::factory()->create();

    expect($activity->group->hasActiveMembership($activity->creator))->toBeTrue()
        ->and($announcement->group->hasActiveMembership($announcement->author))->toBeTrue()
        ->and($file->group->hasActiveMembership($file->uploader))->toBeTrue()
        ->and($event->group->hasActiveMembership($event->actor))->toBeTrue()
        ->and($poll->group->hasActiveMembership($poll->creator))->toBeTrue()
        ->and($vote->poll->group->hasActiveMembership($vote->user))->toBeTrue();
});

test('event and group invitation factories derive inviter authority', function () {
    $eventInvitation = ForumEventInvitation::factory()->create();
    $teamMembership = ForumEventTeamMembership::factory()->create();
    $groupInvitation = ForumGroupInvitation::factory()->create();

    expect($eventInvitation->invited_by_user_id)->toBe($eventInvitation->event->organizer_user_id)
        ->and($teamMembership->invited_by_user_id)->toBe($teamMembership->event->organizer_user_id)
        ->and($groupInvitation->invited_by_user_id)->toBe($groupInvitation->group->owner_user_id);
});

test('mentorship factories create only their two aggregate participants', function () {
    $initialUsers = User::query()->count();
    $mentorship = ForumMentorship::factory()->create();

    expect(User::query()->count() - $initialUsers)->toBe(2)
        ->and($mentorship->mentor_user_id)->toBe($mentorship->scope->profile->user_id);

    $initialUsers = User::query()->count();
    $message = ForumMentorshipMessage::factory()->create();

    expect(User::query()->count() - $initialUsers)->toBe(2)
        ->and($message->sender_user_id)->toBe($message->mentorship->mentor_user_id);

    $initialUsers = User::query()->count();
    $feedback = ForumMentorshipFeedback::factory()->create();

    expect(User::query()->count() - $initialUsers)->toBe(2)
        ->and($feedback->author_user_id)->toBe($feedback->mentorship->mentee_user_id)
        ->and($feedback->recipient_user_id)->toBe($feedback->mentorship->mentor_user_id);
});

test('marketplace child factories do not create discarded listings', function () {
    $initialListings = Listing::query()->count();
    $order = Order::factory()->create();

    expect(Listing::query()->count() - $initialListings)->toBe(1)
        ->and($order->listing_id)->toBe($order->reservation->listing_id)
        ->and($order->buyer_id)->toBe($order->reservation->requester_id)
        ->and($order->buyer_key)->toBe($order->reservation->requester_key)
        ->and($order->buyer_name)->toBe($order->reservation->requester_name)
        ->and($order->seller_id)->toBe($order->listing->owner_id)
        ->and($order->seller_key)->toBe($order->listing->owner_key)
        ->and($order->seller_name)->toBe($order->listing->owner_name);

    $initialListings = Listing::query()->count();
    $dispute = OrderDispute::factory()->create();

    expect(Listing::query()->count() - $initialListings)->toBe(1)
        ->and($dispute->listing_id)->toBe($dispute->order->listing_id);

    $initialListings = Listing::query()->count();
    $review = ListingReview::factory()->create();

    expect(Listing::query()->count() - $initialListings)->toBe(1)
        ->and($review->listing_id)->toBe($review->order->listing_id);
});

test('organization venue factory creates a distinct place for every venue', function () {
    $venues = Venue::factory()->forOrganization()->count(2)->create();

    expect($venues->pluck('organization_id')->unique())->toHaveCount(1)
        ->and($venues->pluck('place_id')->unique())->toHaveCount(2);
});

test('topic move factory keeps the topic in its destination category', function () {
    $move = ForumTopicMove::factory()->create();
    $initialPlacement = ForumTopicMove::factory()->initialPlacement()->create();

    expect($move->topic->forum_category_id)->toBe($move->to_forum_category_id)
        ->and($move->old_url)->toStartWith('https://forum.example.test/')
        ->and($initialPlacement->from_forum_category_id)->toBeNull()
        ->and($initialPlacement->old_url)->toBeNull()
        ->and($initialPlacement->topic->forum_category_id)->toBe($initialPlacement->to_forum_category_id);
});

test('report factory supports every application report subject', function () {
    $subjectClasses = [
        ForumTopic::class,
        ForumAnswer::class,
        ForumComment::class,
        Listing::class,
        AdoptionCase::class,
        SearchCase::class,
        Sighting::class,
        ForumMentorship::class,
        ForumGroup::class,
        ForumEvent::class,
        ForumExpertSession::class,
        ForumExpertSessionQuestion::class,
        ForumExpertSessionAnswer::class,
        SocialRelationshipRequest::class,
    ];

    foreach ($subjectClasses as $subjectClass) {
        $subject = $subjectClass::factory()->create();
        $report = ForumReport::factory()->forSubject($subject)->create();

        expect($report->subject_type)->toBe($subjectClass)
            ->and($report->subject_id)->toBe((string) $subject->getKey())
            ->and($report->topic_id)->toBe(match (true) {
                $subject instanceof ForumTopic => $subject->id,
                $subject instanceof ForumAnswer,
                $subject instanceof ForumComment => $subject->topic_id,
                default => null,
            })
            ->and($report->answer_id)->toBe(
                $subject instanceof ForumAnswer ? $subject->id : null,
            )
            ->and($report->comment_id)->toBe(
                $subject instanceof ForumComment ? $subject->id : null,
            )
            ->and($report->subject->is($subject))->toBeTrue();
    }
});

test('event registration pet pivot preserves encrypted casts and privacy', function () {
    $registrationPet = ForumEventRegistrationPet::factory()->create([
        'conditions' => 'Use the quieter entrance.',
    ]);

    $registrationPivot = $registrationPet->registration->pets()->firstOrFail()->pivot;
    $petPivot = $registrationPet->petProfile->forumEventRegistrations()->firstOrFail()->pivot;

    expect($registrationPivot)->toBeInstanceOf(ForumEventRegistrationPet::class)
        ->and($registrationPivot->conditions)->toBe('Use the quieter entrance.')
        ->and($registrationPivot->toArray())->not->toHaveKey('conditions')
        ->and($petPivot)->toBeInstanceOf(ForumEventRegistrationPet::class)
        ->and($petPivot->conditions)->toBe('Use the quieter entrance.')
        ->and($petPivot->toArray())->not->toHaveKey('conditions');
});

test('forum vote factories synchronize actor and aggregate counters', function () {
    $pollVote = ForumPollVote::factory()->create();
    $vote = ForumVote::factory()->create();
    $selectedOption = $pollVote->poll->options()->findOrFail($pollVote->choices[0]);

    expect($pollVote->poll->refresh()->total_vote_count)->toBe(1)
        ->and($selectedOption->refresh()->selection_count)->toBe(1)
        ->and($selectedOption->first_choice_count)->toBe(1)
        ->and($vote->user_key)->toBe($vote->user->actor_key)
        ->and($vote->answer->refresh()->helpful_count)->toBe(1);
});

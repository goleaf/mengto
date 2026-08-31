<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use App\Notifications\VerifyEmailNotification;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Throwable;

#[Fillable([
    'name',
    'email',
    'password',
    'locale',
    'timezone',
])]
#[Hidden(['password', 'remember_token'])]
/**
 * @property string $actor_key
 * @property Carbon|null $created_at
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property int $id
 * @property bool $is_admin
 * @property CarbonImmutable|null $last_login_at
 * @property string $locale
 * @property string $name
 * @property string $password
 * @property string|null $remember_token
 * @property UserStatus $status
 * @property string $timezone
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use MustVerifyEmail;
    use Notifiable;

    private bool $verificationNotificationDelivered = true;

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isAdministrator(): bool
    {
        return $this->is_admin && $this->isActive();
    }

    public function sendEmailVerificationNotification(): void
    {
        $notification = new VerifyEmailNotification;
        $notification->locale($this->locale);
        $this->verificationNotificationDelivered = false;

        try {
            $this->notify($notification);
        } catch (Throwable $exception) {
            report($exception);

            return;
        }

        $this->verificationNotificationDelivered = $notification->wasDelivered();
    }

    public function verificationNotificationWasDelivered(): bool
    {
        return $this->verificationNotificationDelivered;
    }

    public function hasCompletedOnboarding(): bool
    {
        $state = $this->relationLoaded('onboarding')
            ? $this->getRelation('onboarding')
            : $this->onboarding()->first();

        return ! $state instanceof UserOnboarding || $state->isComplete();
    }

    public function requiresOnboarding(): bool
    {
        return ! $this->hasCompletedOnboarding();
    }

    /** @return HasMany<PetProfile, $this> */
    public function petProfiles(): HasMany
    {
        return $this->hasMany(PetProfile::class);
    }

    /** @return HasMany<PetProfileManager, $this> */
    public function managedPetProfiles(): HasMany
    {
        return $this->hasMany(PetProfileManager::class);
    }

    /** @return HasMany<PetProfileAccessRequest, $this> */
    public function petProfileAccessRequests(): HasMany
    {
        return $this->hasMany(PetProfileAccessRequest::class, 'requester_user_id');
    }

    /** @return HasMany<SearchCase, $this> */
    public function searchCases(): HasMany
    {
        return $this->hasMany(SearchCase::class, 'owner_id');
    }

    /** @return HasMany<SearchContactRelay, $this> */
    public function sentSearchContactRelays(): HasMany
    {
        return $this->hasMany(SearchContactRelay::class, 'sender_user_id');
    }

    /** @return HasMany<SearchContactRelay, $this> */
    public function receivedSearchContactRelays(): HasMany
    {
        return $this->hasMany(SearchContactRelay::class, 'recipient_user_id');
    }

    /** @return HasMany<UserDomainState, $this> */
    public function domainStates(): HasMany
    {
        return $this->hasMany(UserDomainState::class);
    }

    /** @return HasMany<PlaceInvitation, $this> */
    public function sentPlaceInvitations(): HasMany
    {
        return $this->hasMany(PlaceInvitation::class, 'sender_user_id');
    }

    /** @return HasMany<PlaceInvitation, $this> */
    public function receivedPlaceInvitations(): HasMany
    {
        return $this->hasMany(PlaceInvitation::class, 'recipient_user_id');
    }

    /** @return HasOne<SocialActor, $this> */
    public function socialActor(): HasOne
    {
        return $this->hasOne(SocialActor::class);
    }

    /** @return HasOne<UserOnboarding, $this> */
    public function onboarding(): HasOne
    {
        return $this->hasOne(UserOnboarding::class);
    }

    /** @return HasMany<SocialAccountBlock, $this> */
    public function outgoingSocialAccountBlocks(): HasMany
    {
        return $this->hasMany(SocialAccountBlock::class, 'blocker_user_id');
    }

    /** @return HasMany<SocialAccountBlock, $this> */
    public function incomingSocialAccountBlocks(): HasMany
    {
        return $this->hasMany(SocialAccountBlock::class, 'blocked_user_id');
    }

    /** @return HasMany<ContentPublication, $this> */
    public function authoredContentPublications(): HasMany
    {
        return $this->hasMany(ContentPublication::class, 'real_author_user_id');
    }

    /** @return HasMany<ExpertProfile, $this> */
    public function expertProfiles(): HasMany
    {
        return $this->hasMany(ExpertProfile::class, 'owner_id');
    }

    /** @return HasMany<KnowledgeArticle, $this> */
    public function createdKnowledgeArticles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class, 'created_by_user_id');
    }

    /** @return HasMany<KnowledgeArticleCollaborator, $this> */
    public function knowledgeCollaborations(): HasMany
    {
        return $this->hasMany(KnowledgeArticleCollaborator::class);
    }

    /** @return HasMany<ForumCommunityNote, $this> */
    public function proposedCommunityNotes(): HasMany
    {
        return $this->hasMany(ForumCommunityNote::class, 'proposer_user_id');
    }

    /** @return HasMany<ForumReviewAssignment, $this> */
    public function forumReviewAssignments(): HasMany
    {
        return $this->hasMany(ForumReviewAssignment::class, 'reviewer_user_id');
    }

    /** @return HasMany<ForumTopicMove, $this> */
    public function forumTopicMoves(): HasMany
    {
        return $this->hasMany(ForumTopicMove::class, 'actor_user_id');
    }

    /** @return HasMany<ForumUserTrustLevel, $this> */
    public function forumTrustAssignments(): HasMany
    {
        return $this->hasMany(ForumUserTrustLevel::class);
    }

    /** @return HasOne<ForumMentorProfile, $this> */
    public function mentorProfile(): HasOne
    {
        return $this->hasOne(ForumMentorProfile::class);
    }

    /** @return HasMany<ForumMentorship, $this> */
    public function mentorshipsAsMentor(): HasMany
    {
        return $this->hasMany(ForumMentorship::class, 'mentor_user_id');
    }

    /** @return HasMany<ForumMentorship, $this> */
    public function mentorshipsAsMentee(): HasMany
    {
        return $this->hasMany(ForumMentorship::class, 'mentee_user_id');
    }

    /** @return HasMany<ForumMentorshipMessage, $this> */
    public function mentorshipMessages(): HasMany
    {
        return $this->hasMany(ForumMentorshipMessage::class, 'sender_user_id');
    }

    /** @return HasMany<ForumMentorshipFeedback, $this> */
    public function mentorshipFeedback(): HasMany
    {
        return $this->hasMany(ForumMentorshipFeedback::class, 'author_user_id');
    }

    /** @return HasMany<ForumGroup, $this> */
    public function ownedForumGroups(): HasMany
    {
        return $this->hasMany(ForumGroup::class, 'owner_user_id');
    }

    /** @return HasMany<ForumGroupMembership, $this> */
    public function forumGroupMemberships(): HasMany
    {
        return $this->hasMany(ForumGroupMembership::class);
    }

    /** @return HasMany<ForumGroupInvitation, $this> */
    public function forumGroupInvitations(): HasMany
    {
        return $this->hasMany(ForumGroupInvitation::class, 'invited_user_id');
    }

    /** @return HasMany<Organization, $this> */
    public function ownedOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_user_id');
    }

    /** @return HasMany<OrganizationMembership, $this> */
    public function organizationMemberships(): HasMany
    {
        return $this->hasMany(OrganizationMembership::class);
    }

    /** @return HasMany<OrganizationInvitation, $this> */
    public function organizationInvitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class, 'invited_user_id');
    }

    /** @return HasMany<Place, $this> */
    public function ownedPlaces(): HasMany
    {
        return $this->hasMany(Place::class, 'owner_user_id');
    }

    /** @return HasMany<PlaceSubmission, $this> */
    public function placeSubmissions(): HasMany
    {
        return $this->hasMany(PlaceSubmission::class, 'submitter_user_id');
    }

    /** @return HasMany<PlaceSubmission, $this> */
    public function reviewedPlaceSubmissions(): HasMany
    {
        return $this->hasMany(PlaceSubmission::class, 'reviewed_by_user_id');
    }

    /** @return HasMany<PlaceAccessGrant, $this> */
    public function placeAccessGrants(): HasMany
    {
        return $this->hasMany(PlaceAccessGrant::class);
    }

    /** @return HasMany<ForumEvent, $this> */
    public function organizedForumEvents(): HasMany
    {
        return $this->hasMany(ForumEvent::class, 'organizer_user_id');
    }

    /** @return HasMany<ForumEventRegistration, $this> */
    public function forumEventRegistrations(): HasMany
    {
        return $this->hasMany(ForumEventRegistration::class);
    }

    /** @return HasMany<ForumEventInvitation, $this> */
    public function forumEventInvitations(): HasMany
    {
        return $this->hasMany(ForumEventInvitation::class, 'invited_user_id');
    }

    /** @return HasMany<AdoptionApplication, $this> */
    public function adoptionApplications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class, 'applicant_user_id');
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    /** @return HasMany<CareJournal, $this> */
    public function careJournals(): HasMany
    {
        return $this->hasMany(CareJournal::class, 'owner_id');
    }

    /** @return HasMany<ContentMediaAsset, $this> */
    public function ownedContentMediaAssets(): HasMany
    {
        return $this->hasMany(ContentMediaAsset::class, 'owner_user_id');
    }

    /** @return HasMany<DiscoveryPreference, $this> */
    public function discoveryPreferences(): HasMany
    {
        return $this->hasMany(DiscoveryPreference::class);
    }

    /** @return HasMany<ForumAnswer, $this> */
    public function authoredForumAnswers(): HasMany
    {
        return $this->hasMany(ForumAnswer::class, 'author_id');
    }

    /** @return HasMany<ForumComment, $this> */
    public function authoredForumComments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'author_id');
    }

    /** @return HasMany<ForumConfirmation, $this> */
    public function requestedForumConfirmations(): HasMany
    {
        return $this->hasMany(ForumConfirmation::class, 'requester_user_id');
    }

    /** @return HasMany<ForumEventMessage, $this> */
    public function sentForumEventMessages(): HasMany
    {
        return $this->hasMany(ForumEventMessage::class, 'sender_user_id');
    }

    /** @return HasMany<ForumEventReview, $this> */
    public function authoredForumEventReviews(): HasMany
    {
        return $this->hasMany(ForumEventReview::class, 'reviewer_user_id');
    }

    /** @return HasMany<ForumEventSeries, $this> */
    public function ownedForumEventSeries(): HasMany
    {
        return $this->hasMany(ForumEventSeries::class, 'owner_user_id');
    }

    /** @return HasMany<ForumEventSessionStaff, $this> */
    public function forumEventSessionStaffAssignments(): HasMany
    {
        return $this->hasMany(ForumEventSessionStaff::class);
    }

    /** @return HasMany<ForumEventTeamMembership, $this> */
    public function forumEventTeamMemberships(): HasMany
    {
        return $this->hasMany(ForumEventTeamMembership::class);
    }

    /** @return HasMany<ForumEventUpdate, $this> */
    public function authoredForumEventUpdates(): HasMany
    {
        return $this->hasMany(ForumEventUpdate::class, 'author_user_id');
    }

    /** @return HasMany<ForumEvent, $this> */
    public function ownedForumEvents(): HasMany
    {
        return $this->hasMany(ForumEvent::class, 'owner_user_id');
    }

    /** @return HasMany<ForumExpertSessionAnswer, $this> */
    public function authoredForumExpertSessionAnswers(): HasMany
    {
        return $this->hasMany(ForumExpertSessionAnswer::class, 'author_user_id');
    }

    /** @return HasMany<ForumExpertSessionQuestion, $this> */
    public function authoredForumExpertSessionQuestions(): HasMany
    {
        return $this->hasMany(ForumExpertSessionQuestion::class, 'author_user_id');
    }

    /** @return HasMany<ForumGroupAnnouncement, $this> */
    public function authoredForumGroupAnnouncements(): HasMany
    {
        return $this->hasMany(ForumGroupAnnouncement::class, 'author_user_id');
    }

    /** @return HasMany<ForumJournalCollaborator, $this> */
    public function forumJournalCollaborations(): HasMany
    {
        return $this->hasMany(ForumJournalCollaborator::class);
    }

    /** @return HasMany<ForumJournalEntry, $this> */
    public function authoredForumJournalEntries(): HasMany
    {
        return $this->hasMany(ForumJournalEntry::class, 'author_user_id');
    }

    /** @return HasMany<ForumJournal, $this> */
    public function ownedForumJournals(): HasMany
    {
        return $this->hasMany(ForumJournal::class, 'owner_user_id');
    }

    /** @return HasMany<ForumMentorshipFeedback, $this> */
    public function receivedMentorshipFeedback(): HasMany
    {
        return $this->hasMany(ForumMentorshipFeedback::class, 'recipient_user_id');
    }

    /** @return HasMany<ForumModerationAction, $this> */
    public function moderationActionsTargetingUser(): HasMany
    {
        return $this->hasMany(ForumModerationAction::class, 'target_user_id');
    }

    /** @return HasMany<ForumModerationAppeal, $this> */
    public function filedModerationAppeals(): HasMany
    {
        return $this->hasMany(ForumModerationAppeal::class, 'appellant_user_id');
    }

    /** @return HasMany<ForumModerationCase, $this> */
    public function assignedModerationCases(): HasMany
    {
        return $this->hasMany(ForumModerationCase::class, 'assigned_to_user_id');
    }

    /** @return HasMany<ForumPollVote, $this> */
    public function forumPollVotes(): HasMany
    {
        return $this->hasMany(ForumPollVote::class);
    }

    /** @return HasMany<ForumReport, $this> */
    public function submittedForumReports(): HasMany
    {
        return $this->hasMany(ForumReport::class, 'reporter_id');
    }

    /** @return HasMany<ForumReport, $this> */
    public function affectedForumReports(): HasMany
    {
        return $this->hasMany(ForumReport::class, 'affected_user_id');
    }

    /** @return HasMany<ForumReputationAggregate, $this> */
    public function forumReputationAggregates(): HasMany
    {
        return $this->hasMany(ForumReputationAggregate::class);
    }

    /** @return HasMany<ForumReputationEvent, $this> */
    public function forumReputationEvents(): HasMany
    {
        return $this->hasMany(ForumReputationEvent::class);
    }

    /** @return HasMany<ForumTopic, $this> */
    public function authoredForumTopics(): HasMany
    {
        return $this->hasMany(ForumTopic::class, 'author_id');
    }

    /** @return HasMany<ForumTrustHistory, $this> */
    public function forumTrustHistory(): HasMany
    {
        return $this->hasMany(ForumTrustHistory::class);
    }

    /** @return HasMany<ForumUserBadge, $this> */
    public function forumUserBadges(): HasMany
    {
        return $this->hasMany(ForumUserBadge::class);
    }

    /** @return HasMany<ForumVote, $this> */
    public function forumVotes(): HasMany
    {
        return $this->hasMany(ForumVote::class);
    }

    /** @return HasMany<ListingReport, $this> */
    public function listingReports(): HasMany
    {
        return $this->hasMany(ListingReport::class, 'reporter_id');
    }

    /** @return HasMany<Listing, $this> */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'owner_id');
    }

    /** @return HasMany<MedicalRecord, $this> */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'owner_id');
    }

    /** @return HasMany<Order, $this> */
    public function purchasedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /** @return HasMany<Order, $this> */
    public function soldOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /** @return HasMany<PetProfileFact, $this> */
    public function authoredPetProfileFacts(): HasMany
    {
        return $this->hasMany(PetProfileFact::class, 'author_user_id');
    }

    /** @return HasMany<PhotoComment, $this> */
    public function photoComments(): HasMany
    {
        return $this->hasMany(PhotoComment::class);
    }

    /** @return HasMany<PhotoReaction, $this> */
    public function photoReactions(): HasMany
    {
        return $this->hasMany(PhotoReaction::class);
    }

    /** @return HasMany<PlaceQuestion, $this> */
    public function authoredPlaceQuestions(): HasMany
    {
        return $this->hasMany(PlaceQuestion::class, 'author_user_id');
    }

    /** @return HasMany<PlaceQuestionAnswer, $this> */
    public function authoredPlaceQuestionAnswers(): HasMany
    {
        return $this->hasMany(PlaceQuestionAnswer::class, 'author_user_id');
    }

    /** @return HasMany<Reservation, $this> */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'requester_id');
    }

    /** @return HasMany<SearchReport, $this> */
    public function searchReports(): HasMany
    {
        return $this->hasMany(SearchReport::class, 'reporter_id');
    }

    /** @return HasMany<Sighting, $this> */
    public function sightings(): HasMany
    {
        return $this->hasMany(Sighting::class, 'reporter_id');
    }

    /** @return HasMany<SmartDevice, $this> */
    public function smartDevices(): HasMany
    {
        return $this->hasMany(SmartDevice::class, 'owner_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'immutable_datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'is_admin' => 'bool',
            'last_login_at' => 'immutable_datetime',
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;
use App\Models\AuditLog;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\BreedRegistry;
use App\Models\CareAccessGrant;
use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\CareMedia;
use App\Models\CareRoutine;
use App\Models\CareTask;
use App\Models\CommunityAnimalGroup;
use App\Models\Consultation;
use App\Models\ContentAudienceActor;
use App\Models\ContentAudienceRule;
use App\Models\ContentDomainLink;
use App\Models\ContentInteractionSetting;
use App\Models\ContentMediaAsset;
use App\Models\ContentPublication;
use App\Models\ContentPublicationEvent;
use App\Models\Credential;
use App\Models\CredentialVerificationAppeal;
use App\Models\CredentialVerificationEvent;
use App\Models\DeviceAccessGrant;
use App\Models\DeviceAutomation;
use App\Models\DeviceAutomationRun;
use App\Models\DeviceCommand;
use App\Models\DeviceEvent;
use App\Models\DeviceLifecycleRecord;
use App\Models\DevicePetAssignment;
use App\Models\DeviceReading;
use App\Models\DeviceSafeZone;
use App\Models\DiscoveryPreference;
use App\Models\DocumentGrant;
use App\Models\DomesticClassification;
use App\Models\ExpertEngagement;
use App\Models\ExpertProfile;
use App\Models\ExpertReport;
use App\Models\ForumAnswer;
use App\Models\ForumBadge;
use App\Models\ForumBlock;
use App\Models\ForumCategory;
use App\Models\ForumCategoryAlias;
use App\Models\ForumCategoryLifecycleRule;
use App\Models\ForumCategoryRedirect;
use App\Models\ForumCategoryTranslation;
use App\Models\ForumComment;
use App\Models\ForumCommunityNote;
use App\Models\ForumCommunityNoteVersion;
use App\Models\ForumConfirmation;
use App\Models\ForumConfirmationEvidence;
use App\Models\ForumConfirmationVote;
use App\Models\ForumEngagement;
use App\Models\ForumEvent;
use App\Models\ForumEventHistory;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventMessage;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventRegistrationPet;
use App\Models\ForumEventReview;
use App\Models\ForumEventRoom;
use App\Models\ForumEventSeries;
use App\Models\ForumEventSession;
use App\Models\ForumEventSessionStaff;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumEventTrack;
use App\Models\ForumEventUpdate;
use App\Models\ForumEventVersion;
use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionCorrection;
use App\Models\ForumExpertSessionHistory;
use App\Models\ForumExpertSessionQuestion;
use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use App\Models\ForumGroupAnnouncement;
use App\Models\ForumGroupEvent;
use App\Models\ForumGroupFile;
use App\Models\ForumGroupInvitation;
use App\Models\ForumGroupMembership;
use App\Models\ForumJournal;
use App\Models\ForumJournalCollaborator;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalEntryVersion;
use App\Models\ForumJournalMeasurement;
use App\Models\ForumJournalMedia;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorScope;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipEvent;
use App\Models\ForumMentorshipFeedback;
use App\Models\ForumMentorshipMessage;
use App\Models\ForumModerationAction;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumModerationAppeal;
use App\Models\ForumModerationCase;
use App\Models\ForumModeratorRecusal;
use App\Models\ForumNotification;
use App\Models\ForumPoll;
use App\Models\ForumPollOption;
use App\Models\ForumPollVote;
use App\Models\ForumReport;
use App\Models\ForumReportAttachment;
use App\Models\ForumReportEvent;
use App\Models\ForumReportReason;
use App\Models\ForumReputationAggregate;
use App\Models\ForumReputationDimension;
use App\Models\ForumReputationEvent;
use App\Models\ForumReviewAssignment;
use App\Models\ForumReviewPanel;
use App\Models\ForumReviewPanelEvent;
use App\Models\ForumTopic;
use App\Models\ForumTopicAcceptance;
use App\Models\ForumTopicLegalHold;
use App\Models\ForumTopicLifecycleEvent;
use App\Models\ForumTopicMove;
use App\Models\ForumTopicType;
use App\Models\ForumTopicUpdateRequest;
use App\Models\ForumTrustHistory;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserBadge;
use App\Models\ForumUserTrustLevel;
use App\Models\ForumVote;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\KnowledgeCorrection;
use App\Models\KnowledgeVersion;
use App\Models\KnowledgeWorkflowEvent;
use App\Models\Listing;
use App\Models\ListingEngagement;
use App\Models\ListingReport;
use App\Models\ListingReview;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalDocument;
use App\Models\MedicalEvent;
use App\Models\MedicalRecord;
use App\Models\MedicalReminder;
use App\Models\Medication;
use App\Models\MedicationDose;
use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\Organization;
use App\Models\OrganizationAuditEvent;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMembership;
use App\Models\OrganizationRestriction;
use App\Models\PetProfile;
use App\Models\PetProfileAccessRequest;
use App\Models\PetProfileBreedOrigin;
use App\Models\PetProfileFact;
use App\Models\PetProfileIdentifyingMark;
use App\Models\PetProfileLifecycleEvent;
use App\Models\PetProfileManager;
use App\Models\PetProfileMedia;
use App\Models\PetProfileName;
use App\Models\PetProfilePrivacySetting;
use App\Models\PetProfileSlugAlias;
use App\Models\PhotoAsset;
use App\Models\PhotoComment;
use App\Models\PhotoReaction;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\PlaceAccessGrant;
use App\Models\PlaceDuplicateCandidate;
use App\Models\PlaceFact;
use App\Models\PlaceLocationVersion;
use App\Models\PlaceMergeRedirect;
use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswer;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionEvent;
use App\Models\PlaceSubmissionIdentityLock;
use App\Models\PlaceSubmissionRevision;
use App\Models\Publication;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchCaseEvent;
use App\Models\SearchContactRelay;
use App\Models\SearchReport;
use App\Models\SearchSector;
use App\Models\SearchTask;
use App\Models\SearchUpdate;
use App\Models\SearchVolunteer;
use App\Models\Service;
use App\Models\Sighting;
use App\Models\SmartDevice;
use App\Models\SocialAccountBlock;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use App\Models\Taxon;
use App\Models\TaxonChange;
use App\Models\TaxonExternalIdentifier;
use App\Models\TaxonImport;
use App\Models\TaxonImportIssue;
use App\Models\TaxonName;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Models\UserDomainState;
use App\Models\Vaccination;
use App\Models\Venue;
use App\Models\VenueArea;
use App\Models\WeightEntry;
use Illuminate\Database\Eloquent\Model;

final class RepresentativeModelManifest
{
    public const TARGET_COUNT = 10;

    /** @return list<class-string<Model>> */
    public static function classes(): array
    {
        return [
            AdoptionApplication::class,
            AdoptionCase::class,
            AdoptionEvent::class,
            AuditLog::class,
            AvailabilitySlot::class,
            Booking::class,
            BreedRegistry::class,
            CareAccessGrant::class,
            CareEntry::class,
            CareJournal::class,
            CareMedia::class,
            CareRoutine::class,
            CareTask::class,
            CommunityAnimalGroup::class,
            Consultation::class,
            ContentAudienceActor::class,
            ContentAudienceRule::class,
            ContentDomainLink::class,
            ContentInteractionSetting::class,
            ContentMediaAsset::class,
            ContentPublication::class,
            ContentPublicationEvent::class,
            Credential::class,
            CredentialVerificationAppeal::class,
            CredentialVerificationEvent::class,
            DeviceAccessGrant::class,
            DeviceAutomation::class,
            DeviceAutomationRun::class,
            DeviceCommand::class,
            DeviceEvent::class,
            DeviceLifecycleRecord::class,
            DevicePetAssignment::class,
            DeviceReading::class,
            DeviceSafeZone::class,
            DiscoveryPreference::class,
            DocumentGrant::class,
            DomesticClassification::class,
            ExpertEngagement::class,
            ExpertProfile::class,
            ExpertReport::class,
            ForumAnswer::class,
            ForumBadge::class,
            ForumBlock::class,
            ForumCategory::class,
            ForumCategoryAlias::class,
            ForumCategoryLifecycleRule::class,
            ForumCategoryRedirect::class,
            ForumCategoryTranslation::class,
            ForumComment::class,
            ForumCommunityNote::class,
            ForumCommunityNoteVersion::class,
            ForumConfirmation::class,
            ForumConfirmationEvidence::class,
            ForumConfirmationVote::class,
            ForumEngagement::class,
            ForumEvent::class,
            ForumEventHistory::class,
            ForumEventInvitation::class,
            ForumEventMessage::class,
            ForumEventOccurrence::class,
            ForumEventRegistration::class,
            ForumEventRegistrationPet::class,
            ForumEventReview::class,
            ForumEventRoom::class,
            ForumEventSeries::class,
            ForumEventSession::class,
            ForumEventSessionStaff::class,
            ForumEventTeamMembership::class,
            ForumEventTrack::class,
            ForumEventUpdate::class,
            ForumEventVersion::class,
            ForumExpertSession::class,
            ForumExpertSessionAnswer::class,
            ForumExpertSessionCorrection::class,
            ForumExpertSessionHistory::class,
            ForumExpertSessionQuestion::class,
            ForumGroup::class,
            ForumGroupActivity::class,
            ForumGroupAnnouncement::class,
            ForumGroupEvent::class,
            ForumGroupFile::class,
            ForumGroupInvitation::class,
            ForumGroupMembership::class,
            ForumJournal::class,
            ForumJournalCollaborator::class,
            ForumJournalEntry::class,
            ForumJournalEntryVersion::class,
            ForumJournalMeasurement::class,
            ForumJournalMedia::class,
            ForumMentorProfile::class,
            ForumMentorScope::class,
            ForumMentorship::class,
            ForumMentorshipEvent::class,
            ForumMentorshipFeedback::class,
            ForumMentorshipMessage::class,
            ForumModerationAction::class,
            ForumModerationActionDefinition::class,
            ForumModerationAppeal::class,
            ForumModerationCase::class,
            ForumModeratorRecusal::class,
            ForumNotification::class,
            ForumPoll::class,
            ForumPollOption::class,
            ForumPollVote::class,
            ForumReport::class,
            ForumReportAttachment::class,
            ForumReportEvent::class,
            ForumReportReason::class,
            ForumReputationAggregate::class,
            ForumReputationDimension::class,
            ForumReputationEvent::class,
            ForumReviewAssignment::class,
            ForumReviewPanel::class,
            ForumReviewPanelEvent::class,
            ForumTopic::class,
            ForumTopicAcceptance::class,
            ForumTopicLegalHold::class,
            ForumTopicLifecycleEvent::class,
            ForumTopicMove::class,
            ForumTopicType::class,
            ForumTopicUpdateRequest::class,
            ForumTrustHistory::class,
            ForumTrustLevel::class,
            ForumUserBadge::class,
            ForumUserTrustLevel::class,
            ForumVote::class,
            KnowledgeArticle::class,
            KnowledgeArticleCollaborator::class,
            KnowledgeCorrection::class,
            KnowledgeVersion::class,
            KnowledgeWorkflowEvent::class,
            Listing::class,
            ListingEngagement::class,
            ListingReport::class,
            ListingReview::class,
            MedicalAccessGrant::class,
            MedicalDocument::class,
            MedicalEvent::class,
            MedicalRecord::class,
            MedicalReminder::class,
            Medication::class,
            MedicationDose::class,
            Order::class,
            OrderDispute::class,
            Organization::class,
            OrganizationAuditEvent::class,
            OrganizationInvitation::class,
            OrganizationMembership::class,
            OrganizationRestriction::class,
            PetProfile::class,
            PetProfileAccessRequest::class,
            PetProfileBreedOrigin::class,
            PetProfileFact::class,
            PetProfileIdentifyingMark::class,
            PetProfileLifecycleEvent::class,
            PetProfileManager::class,
            PetProfileMedia::class,
            PetProfileName::class,
            PetProfilePrivacySetting::class,
            PetProfileSlugAlias::class,
            PhotoAsset::class,
            PhotoComment::class,
            PhotoReaction::class,
            Place::class,
            PlaceDuplicateCandidate::class,
            PlaceFact::class,
            PlaceMergeRedirect::class,
            PlaceAccessAudit::class,
            PlaceAccessGrant::class,
            PlaceLocationVersion::class,
            PlaceQuestion::class,
            PlaceQuestionAnswer::class,
            PlaceSubmission::class,
            PlaceSubmissionEvent::class,
            PlaceSubmissionIdentityLock::class,
            PlaceSubmissionRevision::class,
            Publication::class,
            Reservation::class,
            Review::class,
            SearchAlert::class,
            SearchCase::class,
            SearchCaseEvent::class,
            SearchContactRelay::class,
            SearchReport::class,
            SearchSector::class,
            SearchTask::class,
            SearchUpdate::class,
            SearchVolunteer::class,
            Service::class,
            Sighting::class,
            SmartDevice::class,
            SocialAccountBlock::class,
            SocialActor::class,
            SocialActorSetting::class,
            SocialRelationship::class,
            SocialRelationshipEvent::class,
            SocialRelationshipRequest::class,
            Taxon::class,
            TaxonChange::class,
            TaxonExternalIdentifier::class,
            TaxonImport::class,
            TaxonImportIssue::class,
            TaxonName::class,
            TaxonSource::class,
            TaxonVersion::class,
            User::class,
            UserDomainState::class,
            Vaccination::class,
            Venue::class,
            VenueArea::class,
            WeightEntry::class,
        ];
    }
}

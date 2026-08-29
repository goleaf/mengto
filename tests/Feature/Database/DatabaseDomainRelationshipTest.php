<?php

declare(strict_types=1);

use App\Models\CareJournal;
use App\Models\ForumComment;
use App\Models\ForumConfirmation;
use App\Models\ForumConfirmationEvidence;
use App\Models\ForumConfirmationVote;
use App\Models\ForumModerationAction;
use App\Models\ForumModeratorRecusal;
use App\Models\ForumReport;
use App\Models\ForumReportAttachment;
use App\Models\ForumReportEvent;
use App\Models\ForumReputationAggregate;
use App\Models\ForumReputationEvent;
use App\Models\ForumReviewPanel;
use App\Models\ForumTrustHistory;
use App\Models\ForumUserBadge;
use App\Models\ForumUserTrustLevel;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\MedicalRecord;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationRestriction;
use App\Models\PetProfile;
use App\Models\Place;
use App\Models\PlaceAccessAudit;
use App\Models\PlaceAccessGrant;
use App\Models\PlaceLocationVersion;
use App\Models\SearchReport;
use App\Models\Sighting;
use App\Models\SmartDevice;
use App\Models\SocialAccountBlock;
use App\Models\Taxon;
use App\Models\TaxonChange;
use App\Models\TaxonName;
use App\Models\TaxonVersion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

dataset('schema-backed belongs-to foreign keys', [
    'care journal owner' => [CareJournal::class, 'owner', 'owner_id'],
    'forum comment author' => [ForumComment::class, 'author', 'author_id'],
    'forum comment journal entry' => [
        ForumComment::class,
        'journalEntry',
        'forum_journal_entry_id',
    ],
    'forum confirmation moderator' => [
        ForumConfirmation::class,
        'moderator',
        'moderator_user_id',
    ],
    'forum confirmation evidence' => [
        ForumConfirmationEvidence::class,
        'confirmation',
        'forum_confirmation_id',
    ],
    'forum confirmation vote' => [
        ForumConfirmationVote::class,
        'confirmation',
        'forum_confirmation_id',
    ],
    'forum confirmation evidence submitter' => [
        ForumConfirmationEvidence::class,
        'submitter',
        'submitted_by_user_id',
    ],
    'forum moderation action reversed action' => [
        ForumModerationAction::class,
        'reversedAction',
        'reversal_of_action_id',
    ],
    'forum moderator recusal case' => [
        ForumModeratorRecusal::class,
        'moderationCase',
        'forum_moderation_case_id',
    ],
    'forum review panel case' => [
        ForumReviewPanel::class,
        'moderationCase',
        'forum_moderation_case_id',
    ],
    'forum review panel appeal' => [
        ForumReviewPanel::class,
        'moderationAppeal',
        'forum_moderation_appeal_id',
    ],
    'forum report affected user' => [ForumReport::class, 'affectedUser', 'affected_user_id'],
    'forum report affected pet' => [
        ForumReport::class,
        'affectedPetProfile',
        'affected_pet_profile_id',
    ],
    'forum report duplicate' => [ForumReport::class, 'duplicateOf', 'duplicate_of_report_id'],
    'forum report attachment uploader' => [
        ForumReportAttachment::class,
        'uploadedBy',
        'uploaded_by_user_id',
    ],
    'forum report event actor' => [ForumReportEvent::class, 'actor', 'actor_user_id'],
    'forum reputation aggregate category' => [
        ForumReputationAggregate::class,
        'category',
        'forum_category_id',
    ],
    'forum reputation aggregate taxon' => [ForumReputationAggregate::class, 'taxon', 'taxon_id'],
    'forum reputation event category' => [
        ForumReputationEvent::class,
        'category',
        'forum_category_id',
    ],
    'forum reputation event taxon' => [ForumReputationEvent::class, 'taxon', 'taxon_id'],
    'forum trust history previous level' => [
        ForumTrustHistory::class,
        'fromLevel',
        'from_forum_trust_level_id',
    ],
    'forum trust history new level' => [
        ForumTrustHistory::class,
        'toLevel',
        'to_forum_trust_level_id',
    ],
    'forum trust history actor' => [ForumTrustHistory::class, 'actor', 'actor_user_id'],
    'forum user badge grantor' => [ForumUserBadge::class, 'grantedBy', 'granted_by_user_id'],
    'forum user trust level grantor' => [
        ForumUserTrustLevel::class,
        'grantedBy',
        'granted_by_user_id',
    ],
    'knowledge collaborator adder' => [
        KnowledgeArticleCollaborator::class,
        'addedBy',
        'added_by_user_id',
    ],
    'knowledge collaborator revoker' => [
        KnowledgeArticleCollaborator::class,
        'revoker',
        'revoked_by_user_id',
    ],
    'medical record owner' => [MedicalRecord::class, 'owner', 'owner_id'],
    'organization invitation revoker' => [
        OrganizationInvitation::class,
        'revoker',
        'revoked_by_user_id',
    ],
    'organization restriction applier' => [
        OrganizationRestriction::class,
        'appliedBy',
        'applied_by_user_id',
    ],
    'organization restriction revoker' => [
        OrganizationRestriction::class,
        'revoker',
        'revoked_by_user_id',
    ],
    'pet life stage override actor' => [
        PetProfile::class,
        'lifeStageOverrideActor',
        'life_stage_override_by_user_id',
    ],
    'place creator' => [Place::class, 'createdBy', 'created_by_user_id'],
    'place last editor' => [Place::class, 'lastEditedBy', 'last_edited_by_user_id'],
    'place access audit event' => [PlaceAccessAudit::class, 'event', 'event_id'],
    'place access audit user' => [PlaceAccessAudit::class, 'user', 'user_id'],
    'place grant issuer' => [PlaceAccessGrant::class, 'issuedBy', 'issued_by_user_id'],
    'place grant revoker' => [PlaceAccessGrant::class, 'revoker', 'revoked_by_user_id'],
    'place location version actor' => [
        PlaceLocationVersion::class,
        'changedBy',
        'changed_by_user_id',
    ],
    'search report reporter' => [SearchReport::class, 'reporter', 'reporter_id'],
    'sighting reporter' => [Sighting::class, 'reporter', 'reporter_id'],
    'smart device owner' => [SmartDevice::class, 'owner', 'owner_id'],
    'social block creator' => [SocialAccountBlock::class, 'createdBy', 'created_by_user_id'],
    'social block revoker' => [SocialAccountBlock::class, 'revoker', 'revoked_by_user_id'],
    'taxon original record' => [Taxon::class, 'originalTaxon', 'original_taxon_id'],
    'taxon change actor' => [TaxonChange::class, 'actor', 'actor_user_id'],
    'taxon name import' => [TaxonName::class, 'import', 'taxon_import_id'],
    'taxon name source' => [TaxonName::class, 'source', 'taxon_source_id'],
    'taxon version source' => [TaxonVersion::class, 'source', 'taxon_source_id'],
]);

test('schema-backed belongs-to relationships use their declared foreign keys', function (
    string $modelClass,
    string $relationship,
    string $foreignKey,
) {
    $relation = (new $modelClass)->{$relationship}();

    expect($relation)
        ->toBeInstanceOf(BelongsTo::class)
        ->and($relation->getForeignKeyName())->toBe($foreignKey);
})->with('schema-backed belongs-to foreign keys');

test('the forum topic move audit table has a persistent application model', function () {
    expect(class_exists('App\\Models\\ForumTopicMove'))->toBeTrue();
});

test('domain parents expose the unambiguous inverse foreign-key relationships', function () {
    $expected = [
        'App\\Models\\AvailabilitySlot' => ['bookings' => ['App\\Models\\Booking', 'availability_slot_id']],
        'App\\Models\\Booking' => [
            'auditLogs' => ['App\\Models\\AuditLog', 'booking_id'],
            'expertReports' => ['App\\Models\\ExpertReport', 'booking_id'],
        ],
        'App\\Models\\CareEntry' => [
            'deviceEvents' => ['App\\Models\\DeviceEvent', 'care_entry_id'],
            'deviceReadings' => ['App\\Models\\DeviceReading', 'care_entry_id'],
        ],
        'App\\Models\\Credential' => [
            'adoptionCases' => ['App\\Models\\AdoptionCase', 'provider_credential_id'],
            'replacementCredentials' => ['App\\Models\\Credential', 'replaces_credential_id'],
        ],
        'App\\Models\\DeviceEvent' => ['automationRuns' => ['App\\Models\\DeviceAutomationRun', 'device_event_id']],
        'App\\Models\\DevicePetAssignment' => [
            'events' => ['App\\Models\\DeviceEvent', 'device_pet_assignment_id'],
            'readings' => ['App\\Models\\DeviceReading', 'device_pet_assignment_id'],
        ],
        'App\\Models\\DomesticClassification' => ['petProfiles' => ['App\\Models\\PetProfile', 'domestic_classification_id']],
        'App\\Models\\ExpertProfile' => [
            'adoptionCases' => ['App\\Models\\AdoptionCase', 'provider_expert_profile_id'],
            'auditLogs' => ['App\\Models\\AuditLog', 'expert_profile_id'],
            'documentGrants' => ['App\\Models\\DocumentGrant', 'expert_profile_id'],
            'forumExpertSessions' => ['App\\Models\\ForumExpertSession', 'expert_profile_id'],
        ],
        'App\\Models\\ForumAnswer' => [
            'reports' => ['App\\Models\\ForumReport', 'answer_id'],
            'acceptances' => ['App\\Models\\ForumTopicAcceptance', 'forum_answer_id'],
        ],
        'App\\Models\\ForumBadge' => ['userBadges' => ['App\\Models\\ForumUserBadge', 'forum_badge_id']],
        'App\\Models\\ForumCategory' => [
            'incomingRedirects' => ['App\\Models\\ForumCategoryRedirect', 'target_forum_category_id'],
            'mentorScopes' => ['App\\Models\\ForumMentorScope', 'forum_category_id'],
            'reputationAggregates' => ['App\\Models\\ForumReputationAggregate', 'forum_category_id'],
            'reputationEvents' => ['App\\Models\\ForumReputationEvent', 'forum_category_id'],
        ],
        'App\\Models\\ForumComment' => ['reports' => ['App\\Models\\ForumReport', 'comment_id']],
        'App\\Models\\ForumEvent' => [
            'groupActivities' => ['App\\Models\\ForumGroupActivity', 'forum_event_id'],
            'placeAccessAudits' => ['App\\Models\\PlaceAccessAudit', 'event_id'],
            'placeAccessGrants' => ['App\\Models\\PlaceAccessGrant', 'event_id'],
        ],
        'App\\Models\\ForumExpertSession' => ['corrections' => ['App\\Models\\ForumExpertSessionCorrection', 'forum_expert_session_id']],
        'App\\Models\\ForumExpertSessionAnswer' => ['history' => ['App\\Models\\ForumExpertSessionHistory', 'forum_expert_session_answer_id']],
        'App\\Models\\ForumModerationAction' => ['reversals' => ['App\\Models\\ForumModerationAction', 'reversal_of_action_id']],
        'App\\Models\\ForumModerationActionDefinition' => ['actions' => ['App\\Models\\ForumModerationAction', 'forum_moderation_action_definition_id']],
        'App\\Models\\ForumModerationAppeal' => ['reviewPanels' => ['App\\Models\\ForumReviewPanel', 'forum_moderation_appeal_id']],
        'App\\Models\\ForumModerationCase' => ['reviewPanels' => ['App\\Models\\ForumReviewPanel', 'forum_moderation_case_id']],
        'App\\Models\\ForumReport' => [
            'attachments' => ['App\\Models\\ForumReportAttachment', 'forum_report_id'],
            'duplicates' => ['App\\Models\\ForumReport', 'duplicate_of_report_id'],
            'searchReports' => ['App\\Models\\SearchReport', 'forum_report_id'],
        ],
        'App\\Models\\ForumReportReason' => ['reports' => ['App\\Models\\ForumReport', 'forum_report_reason_id']],
        'App\\Models\\ForumReputationDimension' => ['aggregates' => ['App\\Models\\ForumReputationAggregate', 'forum_reputation_dimension_id']],
        'App\\Models\\ForumReputationEvent' => ['votes' => ['App\\Models\\ForumVote', 'reputation_event_id']],
        'App\\Models\\ForumReviewAssignment' => ['replacements' => ['App\\Models\\ForumReviewAssignment', 'replacement_for_assignment_id']],
        'App\\Models\\ForumReviewPanel' => ['communityNotes' => ['App\\Models\\ForumCommunityNote', 'forum_review_panel_id']],
        'App\\Models\\ForumTopic' => [
            'notifications' => ['App\\Models\\ForumNotification', 'topic_id'],
            'redirectedTopics' => ['App\\Models\\ForumTopic', 'merged_into_topic_id'],
            'discussionKnowledgeArticles' => ['App\\Models\\KnowledgeArticle', 'discussion_topic_id'],
        ],
        'App\\Models\\ForumTrustLevel' => [
            'transitionsTo' => ['App\\Models\\ForumTrustHistory', 'to_forum_trust_level_id'],
            'transitionsFrom' => ['App\\Models\\ForumTrustHistory', 'from_forum_trust_level_id'],
            'userTrustAssignments' => ['App\\Models\\ForumUserTrustLevel', 'forum_trust_level_id'],
        ],
        'App\\Models\\KnowledgeArticle' => [
            'translations' => ['App\\Models\\KnowledgeArticle', 'translated_from_article_id'],
            'replacedArticles' => ['App\\Models\\KnowledgeArticle', 'replaced_by_article_id'],
        ],
        'App\\Models\\Listing' => ['orderDisputes' => ['App\\Models\\OrderDispute', 'listing_id']],
        'App\\Models\\MedicalEvent' => ['deviceReadings' => ['App\\Models\\DeviceReading', 'medical_event_id']],
        'App\\Models\\PetProfile' => [
            'eventRegistrationPets' => ['App\\Models\\ForumEventRegistrationPet', 'pet_profile_id'],
            'affectedForumReports' => ['App\\Models\\ForumReport', 'affected_pet_profile_id'],
            'duplicateProfiles' => ['App\\Models\\PetProfile', 'canonical_profile_id'],
        ],
        'App\\Models\\PetProfileFact' => ['replacementFacts' => ['App\\Models\\PetProfileFact', 'replaces_fact_id']],
        'App\\Models\\PetProfileManager' => [
            'grantedAccessRequests' => ['App\\Models\\PetProfileAccessRequest', 'granted_manager_id'],
            'lifecycleEvents' => ['App\\Models\\PetProfileLifecycleEvent', 'manager_id'],
        ],
        'App\\Models\\Place' => ['eventOccurrences' => ['App\\Models\\ForumEventOccurrence', 'place_id']],
        'App\\Models\\PlaceAccessGrant' => ['audits' => ['App\\Models\\PlaceAccessAudit', 'place_access_grant_id']],
        'App\\Models\\Review' => ['expertReports' => ['App\\Models\\ExpertReport', 'review_id']],
        'App\\Models\\SearchCase' => [
            'deviceEvents' => ['App\\Models\\DeviceEvent', 'search_case_id'],
            'duplicates' => ['App\\Models\\SearchCase', 'duplicate_of_search_case_id'],
        ],
        'App\\Models\\SmartDevice' => ['automationRuns' => ['App\\Models\\DeviceAutomationRun', 'smart_device_id']],
        'App\\Models\\SocialActor' => [
            'contentAudienceAssignments' => ['App\\Models\\ContentAudienceActor', 'social_actor_id'],
            'contextAudienceRules' => ['App\\Models\\ContentAudienceRule', 'context_actor_id'],
            'representedPublicationEvents' => ['App\\Models\\ContentPublicationEvent', 'represented_actor_id'],
            'sourceAccountBlocks' => ['App\\Models\\SocialAccountBlock', 'source_actor_id'],
            'targetAccountBlocks' => ['App\\Models\\SocialAccountBlock', 'target_actor_id'],
            'sourceRelationshipEvents' => ['App\\Models\\SocialRelationshipEvent', 'source_actor_id'],
            'targetRelationshipEvents' => ['App\\Models\\SocialRelationshipEvent', 'target_actor_id'],
            'representedRelationshipEvents' => ['App\\Models\\SocialRelationshipEvent', 'represented_actor_id'],
        ],
        'App\\Models\\SocialRelationshipRequest' => ['relationships' => ['App\\Models\\SocialRelationship', 'request_id']],
        'App\\Models\\Taxon' => [
            'mentorScopes' => ['App\\Models\\ForumMentorScope', 'taxon_id'],
            'reputationAggregates' => ['App\\Models\\ForumReputationAggregate', 'taxon_id'],
            'reputationEvents' => ['App\\Models\\ForumReputationEvent', 'taxon_id'],
            'knowledgeArticles' => ['App\\Models\\KnowledgeArticle', 'taxon_id'],
            'knowledgeVersions' => ['App\\Models\\KnowledgeVersion', 'taxon_id'],
            'petProfiles' => ['App\\Models\\PetProfile', 'taxon_id'],
            'synonyms' => ['App\\Models\\Taxon', 'accepted_taxon_id'],
            'changes' => ['App\\Models\\TaxonChange', 'taxon_id'],
            'externalIdentifiers' => ['App\\Models\\TaxonExternalIdentifier', 'taxon_id'],
            'childVersions' => ['App\\Models\\TaxonVersion', 'parent_taxon_id'],
        ],
        'App\\Models\\TaxonImport' => [
            'changes' => ['App\\Models\\TaxonChange', 'taxon_import_id'],
            'names' => ['App\\Models\\TaxonName', 'taxon_import_id'],
        ],
        'App\\Models\\TaxonSource' => [
            'externalIdentifiers' => ['App\\Models\\TaxonExternalIdentifier', 'taxon_source_id'],
            'names' => ['App\\Models\\TaxonName', 'taxon_source_id'],
            'versions' => ['App\\Models\\TaxonVersion', 'taxon_source_id'],
        ],
        'App\\Models\\Venue' => [
            'eventOccurrences' => ['App\\Models\\ForumEventOccurrence', 'venue_id'],
            'events' => ['App\\Models\\ForumEvent', 'venue_id'],
        ],
        'App\\Models\\VenueArea' => ['rooms' => ['App\\Models\\ForumEventRoom', 'venue_area_id']],
        'App\\Models\\WeightEntry' => ['deviceReadings' => ['App\\Models\\DeviceReading', 'weight_entry_id']],
        'App\\Models\\User' => [
            'adoptionApplications' => ['App\\Models\\AdoptionApplication', 'applicant_user_id'],
            'bookings' => ['App\\Models\\Booking', 'client_id'],
            'careJournals' => ['App\\Models\\CareJournal', 'owner_id'],
            'ownedContentMediaAssets' => ['App\\Models\\ContentMediaAsset', 'owner_user_id'],
            'discoveryPreferences' => ['App\\Models\\DiscoveryPreference', 'user_id'],
            'authoredForumAnswers' => ['App\\Models\\ForumAnswer', 'author_id'],
            'authoredForumComments' => ['App\\Models\\ForumComment', 'author_id'],
            'requestedForumConfirmations' => ['App\\Models\\ForumConfirmation', 'requester_user_id'],
            'sentForumEventMessages' => ['App\\Models\\ForumEventMessage', 'sender_user_id'],
            'authoredForumEventReviews' => ['App\\Models\\ForumEventReview', 'reviewer_user_id'],
            'ownedForumEventSeries' => ['App\\Models\\ForumEventSeries', 'owner_user_id'],
            'forumEventSessionStaffAssignments' => ['App\\Models\\ForumEventSessionStaff', 'user_id'],
            'forumEventTeamMemberships' => ['App\\Models\\ForumEventTeamMembership', 'user_id'],
            'authoredForumEventUpdates' => ['App\\Models\\ForumEventUpdate', 'author_user_id'],
            'ownedForumEvents' => ['App\\Models\\ForumEvent', 'owner_user_id'],
            'authoredForumExpertSessionAnswers' => ['App\\Models\\ForumExpertSessionAnswer', 'author_user_id'],
            'authoredForumExpertSessionQuestions' => ['App\\Models\\ForumExpertSessionQuestion', 'author_user_id'],
            'authoredForumGroupAnnouncements' => ['App\\Models\\ForumGroupAnnouncement', 'author_user_id'],
            'forumJournalCollaborations' => ['App\\Models\\ForumJournalCollaborator', 'user_id'],
            'authoredForumJournalEntries' => ['App\\Models\\ForumJournalEntry', 'author_user_id'],
            'ownedForumJournals' => ['App\\Models\\ForumJournal', 'owner_user_id'],
            'receivedMentorshipFeedback' => ['App\\Models\\ForumMentorshipFeedback', 'recipient_user_id'],
            'moderationActionsTargetingUser' => ['App\\Models\\ForumModerationAction', 'target_user_id'],
            'filedModerationAppeals' => ['App\\Models\\ForumModerationAppeal', 'appellant_user_id'],
            'assignedModerationCases' => ['App\\Models\\ForumModerationCase', 'assigned_to_user_id'],
            'forumPollVotes' => ['App\\Models\\ForumPollVote', 'user_id'],
            'submittedForumReports' => ['App\\Models\\ForumReport', 'reporter_id'],
            'affectedForumReports' => ['App\\Models\\ForumReport', 'affected_user_id'],
            'forumReputationAggregates' => ['App\\Models\\ForumReputationAggregate', 'user_id'],
            'forumReputationEvents' => ['App\\Models\\ForumReputationEvent', 'user_id'],
            'authoredForumTopics' => ['App\\Models\\ForumTopic', 'author_id'],
            'forumTrustHistory' => ['App\\Models\\ForumTrustHistory', 'user_id'],
            'forumUserBadges' => ['App\\Models\\ForumUserBadge', 'user_id'],
            'forumVotes' => ['App\\Models\\ForumVote', 'user_id'],
            'listingReports' => ['App\\Models\\ListingReport', 'reporter_id'],
            'listings' => ['App\\Models\\Listing', 'owner_id'],
            'medicalRecords' => ['App\\Models\\MedicalRecord', 'owner_id'],
            'purchasedOrders' => ['App\\Models\\Order', 'buyer_id'],
            'soldOrders' => ['App\\Models\\Order', 'seller_id'],
            'authoredPetProfileFacts' => ['App\\Models\\PetProfileFact', 'author_user_id'],
            'photoComments' => ['App\\Models\\PhotoComment', 'user_id'],
            'photoReactions' => ['App\\Models\\PhotoReaction', 'user_id'],
            'authoredPlaceQuestions' => ['App\\Models\\PlaceQuestion', 'author_user_id'],
            'authoredPlaceQuestionAnswers' => ['App\\Models\\PlaceQuestionAnswer', 'author_user_id'],
            'reservations' => ['App\\Models\\Reservation', 'requester_id'],
            'searchReports' => ['App\\Models\\SearchReport', 'reporter_id'],
            'sightings' => ['App\\Models\\Sighting', 'reporter_id'],
            'smartDevices' => ['App\\Models\\SmartDevice', 'owner_id'],
        ],
    ];

    $violations = [];

    foreach ($expected as $parentClass => $relationships) {
        $parent = new $parentClass;

        foreach ($relationships as $method => [$relatedClass, $foreignKey]) {
            if (! method_exists($parent, $method)) {
                $violations[] = "{$parentClass}::{$method} is missing";

                continue;
            }

            $relation = $parent->{$method}();

            if (! $relation instanceof HasMany) {
                $violations[] = "{$parentClass}::{$method} is not HasMany";

                continue;
            }

            if ($relation->getRelated()::class !== $relatedClass) {
                $violations[] = "{$parentClass}::{$method} has the wrong related model";
            }

            if ($relation->getForeignKeyName() !== $foreignKey) {
                $violations[] = "{$parentClass}::{$method} has the wrong foreign key";
            }
        }
    }

    expect($violations)->toBe([]);
});

test('pivot relationships expose both sides with their metadata', function () {
    $relationships = [
        [Taxon::class, 'communityAnimalGroups', 'community_animal_group_taxon', [
            'position', 'includes_descendants', 'created_at', 'updated_at',
        ]],
        [Taxon::class, 'forumEvents', 'forum_event_taxon', [
            'is_primary', 'created_at', 'updated_at',
        ]],
        [Taxon::class, 'forumGroups', 'forum_group_taxon', [
            'is_primary', 'created_at', 'updated_at',
        ]],
        [PetProfile::class, 'forumEventRegistrations', 'forum_event_registration_pets', [
            'eligibility_status', 'verification_source', 'conditions',
            'checked_in_at', 'checked_out_at', 'created_at', 'updated_at',
        ]],
    ];

    foreach ($relationships as [$modelClass, $method, $table, $pivotColumns]) {
        expect(method_exists($modelClass, $method))->toBeTrue();

        $relation = (new $modelClass)->{$method}();

        expect($relation)
            ->toBeInstanceOf(BelongsToMany::class)
            ->and($relation->getTable())->toBe($table)
            ->and($relation->getPivotColumns())->toEqualCanonicalizing($pivotColumns);
    }
});

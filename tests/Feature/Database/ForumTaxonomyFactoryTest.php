<?php

declare(strict_types=1);

use App\Models\BreedRegistry;
use App\Models\CommunityAnimalGroup;
use App\Models\DomesticClassification;
use App\Models\ForumBadge;
use App\Models\ForumCategory;
use App\Models\ForumCategoryAlias;
use App\Models\ForumCategoryRedirect;
use App\Models\ForumCategoryTranslation;
use App\Models\ForumConfirmation;
use App\Models\ForumConfirmationEvidence;
use App\Models\ForumConfirmationVote;
use App\Models\ForumModerationAction;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumModerationAppeal;
use App\Models\ForumModerationCase;
use App\Models\ForumModeratorRecusal;
use App\Models\ForumReportAttachment;
use App\Models\ForumReportEvent;
use App\Models\ForumReportReason;
use App\Models\ForumReputationAggregate;
use App\Models\ForumReputationDimension;
use App\Models\ForumReputationEvent;
use App\Models\ForumTopicAcceptance;
use App\Models\ForumTopicType;
use App\Models\ForumTrustHistory;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserBadge;
use App\Models\ForumUserTrustLevel;
use App\Models\Taxon;
use App\Models\TaxonChange;
use App\Models\TaxonExternalIdentifier;
use App\Models\TaxonImport;
use App\Models\TaxonImportIssue;
use App\Models\TaxonName;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;

test('forum and taxonomy factories create valid records', function (string $modelClass) {
    $record = $modelClass::factory()->create();

    expect($record)->toBeInstanceOf($modelClass)
        ->and($record->exists)->toBeTrue();
})->with([
    ForumCategory::class,
    ForumCategoryTranslation::class,
    ForumCategoryAlias::class,
    ForumCategoryRedirect::class,
    ForumTopicType::class,
    ForumTopicAcceptance::class,
    ForumReputationDimension::class,
    ForumReputationEvent::class,
    ForumReputationAggregate::class,
    ForumTrustLevel::class,
    ForumUserTrustLevel::class,
    ForumTrustHistory::class,
    ForumBadge::class,
    ForumUserBadge::class,
    ForumConfirmation::class,
    ForumConfirmationVote::class,
    ForumConfirmationEvidence::class,
    ForumReportReason::class,
    ForumReportEvent::class,
    ForumReportAttachment::class,
    ForumModerationCase::class,
    ForumModerationActionDefinition::class,
    ForumModerationAction::class,
    ForumModerationAppeal::class,
    ForumModeratorRecusal::class,
    TaxonSource::class,
    TaxonImport::class,
    Taxon::class,
    TaxonChange::class,
    TaxonExternalIdentifier::class,
    TaxonVersion::class,
    TaxonName::class,
    TaxonImportIssue::class,
    BreedRegistry::class,
    DomesticClassification::class,
    CommunityAnimalGroup::class,
]);

test('meaningful category and taxonomy factory states remain valid', function () {
    $archived = ForumCategory::factory()->archived()->create();
    $system = ForumCategory::factory()->systemManaged()->create();
    $unresolved = Taxon::factory()->unresolved()->create();
    $completed = TaxonImport::factory()->completed()->create();

    expect($archived->is_active)->toBeFalse()
        ->and($archived->archived_at)->not->toBeNull()
        ->and($system->is_system_managed)->toBeTrue()
        ->and($unresolved->requires_review)->toBeTrue()
        ->and($completed->completed_at)->not->toBeNull();
});

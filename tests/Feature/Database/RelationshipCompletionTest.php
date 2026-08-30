<?php

declare(strict_types=1);

use App\Models\AdoptionCase;
use App\Models\ForumAnswer;
use App\Models\ForumCategory;
use App\Models\ForumComment;
use App\Models\ForumEvent;
use App\Models\ForumEventRegistration;
use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionQuestion;
use App\Models\ForumGroup;
use App\Models\ForumMentorship;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\Listing;
use App\Models\PetProfile;
use App\Models\SearchCase;
use App\Models\Sighting;
use App\Models\SocialRelationshipRequest;
use App\Models\Taxon;
use App\Models\TaxonImport;
use App\Models\TaxonSource;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

test('pet profiles expose direct registrations separately from pivot registrations', function () {
    $pet = PetProfile::factory()->create();
    $directRegistration = ForumEventRegistration::factory()->forPet($pet)->create();
    $pivotRegistration = ForumEventRegistration::factory()->withPet($pet)->create();

    expect($pet->directForumEventRegistrations())
        ->toBeInstanceOf(HasMany::class)
        ->and($pet->directForumEventRegistrations()->getForeignKeyName())->toBe('pet_profile_id')
        ->and($pet->directForumEventRegistrations()->pluck('forum_event_registrations.id')->all())
        ->toBe([$directRegistration->id])
        ->and($pet->forumEventRegistrations()->pluck('forum_event_registrations.id')->all())
        ->toBe([$pivotRegistration->id]);
});

test('taxonomy records expose the original and active-source inverse relationships', function () {
    $original = Taxon::factory()->create();
    $derived = Taxon::factory()->create(['original_taxon_id' => $original->id]);
    $source = TaxonSource::factory()->create();
    $import = TaxonImport::factory()->for($source, 'source')->create();
    $source->update(['active_taxon_import_id' => $import->id]);

    expect($original->derivedTaxa())
        ->toBeInstanceOf(HasMany::class)
        ->and($original->derivedTaxa()->getForeignKeyName())->toBe('original_taxon_id')
        ->and($original->derivedTaxa()->firstOrFail()->is($derived))->toBeTrue()
        ->and($import->activeSources())
        ->toBeInstanceOf(HasMany::class)
        ->and($import->activeSources()->getForeignKeyName())->toBe('active_taxon_import_id')
        ->and($import->activeSources()->firstOrFail()->is($source))->toBeTrue();
});

test('forum categories expose incoming directed relations with pivot metadata', function () {
    $source = ForumCategory::factory()->create();
    $target = ForumCategory::factory()->create();

    $source->relatedCategories()->attach($target, [
        'relation_type' => 'complements',
        'position' => 7,
    ]);

    $incoming = $target->incomingRelatedCategories()->firstOrFail();

    expect($target->incomingRelatedCategories()->getTable())->toBe('forum_category_relations')
        ->and($target->incomingRelatedCategories()->getForeignPivotKeyName())
        ->toBe('related_forum_category_id')
        ->and($target->incomingRelatedCategories()->getRelatedPivotKeyName())
        ->toBe('forum_category_id')
        ->and($target->incomingRelatedCategories()->getPivotColumns())
        ->toEqualCanonicalizing(['relation_type', 'position', 'created_at', 'updated_at'])
        ->and($incoming->is($source))->toBeTrue()
        ->and($incoming->pivot->getAttribute('relation_type'))->toBe('complements')
        ->and($incoming->pivot->getAttribute('position'))->toBe(7)
        ->and($incoming->pivot->getAttribute('created_at'))->not->toBeNull()
        ->and($incoming->pivot->getAttribute('updated_at'))->not->toBeNull();
});

test('every supported report subject resolves reports through its polymorphic inverse', function () {
    $subjectClasses = [
        AdoptionCase::class,
        ForumAnswer::class,
        ForumComment::class,
        ForumEvent::class,
        ForumExpertSession::class,
        ForumExpertSessionAnswer::class,
        ForumExpertSessionQuestion::class,
        ForumGroup::class,
        ForumMentorship::class,
        ForumTopic::class,
        Listing::class,
        SearchCase::class,
        Sighting::class,
        SocialRelationshipRequest::class,
    ];

    foreach ($subjectClasses as $subjectClass) {
        $subject = $subjectClass::factory()->create();
        $report = ForumReport::factory()->forSubject($subject)->create();
        $relation = match (true) {
            $subject instanceof AdoptionCase => $subject->subjectReports(),
            $subject instanceof ForumAnswer => $subject->subjectReports(),
            $subject instanceof ForumComment => $subject->subjectReports(),
            $subject instanceof ForumEvent => $subject->subjectReports(),
            $subject instanceof ForumExpertSession => $subject->subjectReports(),
            $subject instanceof ForumExpertSessionAnswer => $subject->subjectReports(),
            $subject instanceof ForumExpertSessionQuestion => $subject->subjectReports(),
            $subject instanceof ForumGroup => $subject->subjectReports(),
            $subject instanceof ForumMentorship => $subject->subjectReports(),
            $subject instanceof ForumTopic => $subject->subjectReports(),
            $subject instanceof Listing => $subject->subjectReports(),
            $subject instanceof SearchCase => $subject->subjectReports(),
            $subject instanceof Sighting => $subject->subjectReports(),
            $subject instanceof SocialRelationshipRequest => $subject->subjectReports(),
            default => throw new LogicException('Unsupported forum report subject.'),
        };

        expect($relation)
            ->toBeInstanceOf(MorphMany::class)
            ->and($relation->getMorphType())->toBe('subject_type')
            ->and($relation->getForeignKeyName())->toBe('subject_id')
            ->and($relation->firstOrFail()->is($report))->toBeTrue();
    }
});

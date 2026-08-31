<?php

declare(strict_types=1);

use App\Enums\AdoptionProviderIdentityStatus;
use App\Enums\DiscoveryCategory;
use App\Enums\ForumJournalType;
use App\Enums\ForumMentorshipType;
use App\Enums\MedicalKnowledgeStatus;
use App\Enums\PetSizeCategory;
use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;
use App\Models\Booking;
use App\Models\ContentMediaAsset;
use App\Models\ContentPublication;
use App\Models\Credential;
use App\Models\DiscoveryPreference;
use App\Models\ExpertEngagement;
use App\Models\ExpertProfile;
use App\Models\ForumAnswer;
use App\Models\ForumCategoryLifecycleRule;
use App\Models\ForumCommunityNote;
use App\Models\ForumConfirmation;
use App\Models\ForumGroupActivity;
use App\Models\ForumGroupAnnouncement;
use App\Models\ForumGroupFile;
use App\Models\ForumJournal;
use App\Models\ForumJournalCollaborator;
use App\Models\ForumJournalEntry;
use App\Models\ForumJournalMedia;
use App\Models\ForumMentorScope;
use App\Models\ForumPoll;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\ForumTopicAcceptance;
use App\Models\ForumTopicLegalHold;
use App\Models\ForumTopicUpdateRequest;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\KnowledgeCorrection;
use App\Models\KnowledgeVersion;
use App\Models\KnowledgeWorkflowEvent;
use App\Models\Listing;
use App\Models\MedicalRecord;
use App\Models\Order;
use App\Models\Organization;
use App\Models\PetProfile;
use App\Models\PetProfileMedia;
use App\Models\Reservation;
use App\Models\SearchCase;
use App\Models\Sighting;
use App\Models\SmartDevice;
use App\Models\SocialActor;
use App\Models\TaxonImport;
use App\Models\TaxonVersion;
use App\Models\User;
use Database\Factories\ApplicationFactory;
use Database\Seeders\CareJournalSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DiscoveryDemoSeeder;
use Database\Seeders\ExpertSeeder;
use Database\Seeders\ForumSeeder;
use Database\Seeders\ListingSeeder;
use Database\Seeders\MarketplaceExpansionSeeder;
use Database\Seeders\MedicalRecordSeeder;
use Database\Seeders\PerformanceSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\SmartDeviceSeeder;
use Database\Seeders\SocialIdentitySeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\seed;

beforeEach(function (): void {
    Storage::fake('local');
});

dataset('model factories', static function (): array {
    $models = [];
    $modelDirectory = dirname(__DIR__, 3).'/app/Models';

    foreach (glob($modelDirectory.'/*.php') ?: [] as $path) {
        $class = 'App\\Models\\'.pathinfo($path, PATHINFO_FILENAME);

        if (is_subclass_of($class, Model::class)) {
            $models[pathinfo($path, PATHINFO_FILENAME)] = [$class];
        }
    }

    return $models;
});

dataset('factory states', [
    'adoption application adopted' => [AdoptionApplication::class, 'adopted'],
    'adoption application closed' => [AdoptionApplication::class, 'closed'],
    'adoption application follow up' => [AdoptionApplication::class, 'followUp'],
    'adoption application foster' => [AdoptionApplication::class, 'foster'],
    'adoption case closed' => [AdoptionCase::class, 'closed'],
    'adoption case foster' => [AdoptionCase::class, 'foster'],
    'booking completed' => [Booking::class, 'completed'],
    'expert unverified' => [ExpertProfile::class, 'unverified'],
    'forum answer expert' => [ForumAnswer::class, 'expert'],
    'community note published' => [ForumCommunityNote::class, 'published'],
    'community note safety warning' => [ForumCommunityNote::class, 'safetyWarning'],
    'forum journal archived' => [ForumJournal::class, 'archived'],
    'forum journal collaborator editor' => [ForumJournalCollaborator::class, 'editor'],
    'forum journal collaborator revoked' => [ForumJournalCollaborator::class, 'revoked'],
    'forum journal entry milestone' => [ForumJournalEntry::class, 'milestone'],
    'forum journal entry setback' => [ForumJournalEntry::class, 'setback'],
    'forum journal media archived' => [ForumJournalMedia::class, 'archived'],
    'forum topic draft' => [ForumTopic::class, 'draft'],
    'forum topic medical' => [ForumTopic::class, 'medical'],
    'forum topic resolved' => [ForumTopic::class, 'resolved'],
    'forum topic outdated' => [ForumTopic::class, 'outdated'],
    'forum topic locked' => [ForumTopic::class, 'locked'],
    'forum topic archived' => [ForumTopic::class, 'archived'],
    'forum topic removed' => [ForumTopic::class, 'removed'],
    'forum topic restored' => [ForumTopic::class, 'restored'],
    'forum category lifecycle restrictive' => [ForumCategoryLifecycleRule::class, 'restrictive'],
    'forum topic update community proposal' => [ForumTopicUpdateRequest::class, 'communityProposal'],
    'forum topic update accepted' => [ForumTopicUpdateRequest::class, 'accepted'],
    'forum topic legal hold released' => [ForumTopicLegalHold::class, 'released'],
    'knowledge article draft' => [KnowledgeArticle::class, 'draft'],
    'knowledge article submitted' => [KnowledgeArticle::class, 'submittedForReview'],
    'knowledge article changes requested' => [KnowledgeArticle::class, 'changesRequested'],
    'knowledge article community reviewed' => [KnowledgeArticle::class, 'communityReviewed'],
    'knowledge article expert reviewed' => [KnowledgeArticle::class, 'expertReviewed'],
    'knowledge article correction requested' => [KnowledgeArticle::class, 'correctionRequested'],
    'knowledge article outdated' => [KnowledgeArticle::class, 'outdated'],
    'knowledge article archived' => [KnowledgeArticle::class, 'archived'],
    'knowledge article replaced' => [KnowledgeArticle::class, 'replaced'],
    'knowledge collaborator maintainer' => [KnowledgeArticleCollaborator::class, 'maintainer'],
    'knowledge collaborator community reviewer' => [KnowledgeArticleCollaborator::class, 'communityReviewer'],
    'knowledge collaborator expert reviewer' => [KnowledgeArticleCollaborator::class, 'expertReviewer'],
    'knowledge collaborator revoked' => [KnowledgeArticleCollaborator::class, 'revoked'],
    'knowledge correction accepted' => [KnowledgeCorrection::class, 'accepted'],
    'knowledge correction rejected' => [KnowledgeCorrection::class, 'rejected'],
    'knowledge correction applied' => [KnowledgeCorrection::class, 'applied'],
    'listing adoption' => [Listing::class, 'adoption'],
    'listing draft' => [Listing::class, 'draft'],
    'listing rental' => [Listing::class, 'rental'],
    'listing shelter need' => [Listing::class, 'shelterNeed'],
    'search case found' => [SearchCase::class, 'found'],
    'search case reunited' => [SearchCase::class, 'reunited'],
    'search case returned' => [SearchCase::class, 'returned'],
    'search case sighted' => [SearchCase::class, 'sighted'],
    'search case stolen' => [SearchCase::class, 'stolen'],
    'sighting confirmed' => [Sighting::class, 'confirmed'],
    'user unverified' => [User::class, 'unverified'],
    'user blocked' => [User::class, 'blocked'],
    'user suspended' => [User::class, 'suspended'],
    'user administrator' => [User::class, 'administrator'],
    'user Lithuanian' => [User::class, 'lithuanian'],
    'user Russian' => [User::class, 'russian'],
]);

dataset('direct demo seeders', [
    'care journals' => [CareJournalSeeder::class],
    'experts' => [ExpertSeeder::class],
    'forum' => [ForumSeeder::class],
    'listings' => [ListingSeeder::class],
    'medical records' => [MedicalRecordSeeder::class],
    'search' => [SearchSeeder::class],
    'smart devices' => [SmartDeviceSeeder::class],
    'social identities' => [SocialIdentitySeeder::class],
]);

dataset('zero argument factory helpers', static function (): array {
    $helpers = [];
    $factoryDirectory = dirname(__DIR__, 3).'/database/factories';

    foreach (glob($factoryDirectory.'/*Factory.php') ?: [] as $path) {
        $factoryClass = 'Database\\Factories\\'.pathinfo($path, PATHINFO_FILENAME);

        if ($factoryClass === ApplicationFactory::class || ! class_exists($factoryClass)) {
            continue;
        }

        $reflection = new ReflectionClass($factoryClass);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (
                $method->getDeclaringClass()->getName() !== $factoryClass
                || in_array($method->getName(), ['definition', 'configure'], true)
                || $method->getNumberOfRequiredParameters() !== 0
            ) {
                continue;
            }

            $helpers[
                pathinfo($path, PATHINFO_FILENAME).' '.$method->getName()
            ] = [$factoryClass, $method->getName()];
        }
    }

    return $helpers;
});

test('database seeding dependencies are installed without development packages', function () {
    $composer = json_decode(
        (string) file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($composer['require'])
        ->toHaveKey('fakerphp/faker')
        ->and($composer['require-dev'])
        ->not->toHaveKey('fakerphp/faker');
});

test('every first party model factory creates a persisted valid record', function (string $modelClass) {
    $model = $modelClass::factory()->create();

    expect($model)
        ->toBeInstanceOf($modelClass)
        ->and($model->exists)->toBeTrue()
        ->and($model->getKey())->not->toBeNull();
})->with('model factories');

test('every first party model factory can make an unpersisted model', function (
    string $modelClass,
) {
    $model = $modelClass::factory()->make();

    expect($model)
        ->toBeInstanceOf($modelClass)
        ->and($model->exists)->toBeFalse();
})->with('model factories');

test('factory definitions do not persist related records while defining attributes', function (
    string $modelClass,
) {
    $connection = DB::connection();
    $connection->flushQueryLog();
    $connection->enableQueryLog();

    try {
        $modelClass::factory()->definition();

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

    expect($writes)->toBe([]);
})->with('model factories');

test('every documented factory state creates a persisted valid record', function (
    string $modelClass,
    string $state,
) {
    $model = $modelClass::factory()->{$state}()->create();

    expect($model)
        ->toBeInstanceOf($modelClass)
        ->and($model->exists)->toBeTrue();
})->with('factory states');

test('every zero argument factory helper creates a persisted valid record', function (
    string $factoryClass,
    string $helper,
) {
    $factory = $factoryClass::new()->{$helper}();
    $model = $factory->create();

    expect($factory)->toBeInstanceOf($factoryClass)
        ->and($model)->toBeInstanceOf(Model::class)
        ->and($model->exists)->toBeTrue();
})->with('zero argument factory helpers');

test('every parameterized factory helper creates a persisted valid record', function () {
    $user = User::factory()->create();
    $actor = SocialActor::factory()->forUser($user)->create();
    $credential = Credential::factory()->create();
    $topic = ForumTopic::factory()->create();
    $answer = ForumAnswer::factory()->create(['topic_id' => $topic->id]);
    $journal = ForumJournal::factory()->forUser($user)->create();
    $sourceArticle = KnowledgeArticle::factory()->create([
        'translation_group_key' => 'factory-helper-translation-group',
    ]);
    $profile = PetProfile::factory()->for($user)->create();
    $import = TaxonImport::factory()->create();

    $models = [
        AdoptionCase::factory()->withVerifiedProvider($credential)->create(),
        ContentMediaAsset::factory()->ownedBy($user)->create(),
        ContentPublication::factory()->by($user, $actor)->create(),
        DiscoveryPreference::factory()->category(DiscoveryCategory::Pets)->create(),
        ExpertEngagement::factory()->forUser($user)->create(),
        ForumConfirmation::factory()->forSubject(ForumTopic::class, $topic->id)->create(),
        ForumJournalEntry::factory()->forJournal($journal)->create(),
        ForumJournalEntry::factory()->by($user)->create(),
        ForumJournal::factory()->forUser($user)->create(),
        ForumJournal::factory()->withType(ForumJournalType::Training)->create(),
        ForumMentorScope::factory()->forType(ForumMentorshipType::TrainingSupport)->create(),
        ForumReport::factory()->forSubject($topic)->create(),
        ForumTopicAcceptance::factory()->forAnswer($answer)->create(),
        KnowledgeArticle::factory()->translatedFrom($sourceArticle, $user, 'lt')->create(),
        MedicalRecord::factory()->forPetProfile($profile)->create(),
        Organization::factory()->forOwner($user)->create(),
        PetProfile::factory()->withSize(PetSizeCategory::Large)->create(),
        TaxonVersion::factory()->forImport($import)->create(),
    ];

    expect($models)->toHaveCount(18);

    foreach ($models as $model) {
        expect($model)->toBeInstanceOf(Model::class)
            ->and($model->exists)->toBeTrue();
    }
});

test('database seeding is repeatable without changing stable entity counts', function () {
    seed(DatabaseSeeder::class);
    $firstPetMediaFiles = Storage::disk('local')->allFiles('pet-profiles');

    $firstCounts = [
        'users' => User::query()->count(),
        'topics' => ForumTopic::query()->count(),
        'group_activities' => ForumGroupActivity::query()->count(),
        'group_announcements' => ForumGroupAnnouncement::query()->count(),
        'group_files' => ForumGroupFile::query()->count(),
        'group_polls' => ForumPoll::query()->count(),
        'forum_journals' => ForumJournal::query()->count(),
        'forum_journal_entries' => ForumJournalEntry::query()->count(),
        'listings' => Listing::query()->count(),
        'credentials' => Credential::query()->count(),
        'guides' => KnowledgeArticle::query()->count(),
        'guide_collaborators' => KnowledgeArticleCollaborator::query()->count(),
        'guide_versions' => KnowledgeVersion::query()->count(),
        'guide_workflow_events' => KnowledgeWorkflowEvent::query()->count(),
        'adoption_events' => AdoptionEvent::query()->count(),
        'devices' => SmartDevice::query()->count(),
        'content_publications' => ContentPublication::query()->count(),
        'pet_profile_media' => PetProfileMedia::query()->count(),
    ];

    seed(DatabaseSeeder::class);

    expect([
        'users' => User::query()->count(),
        'topics' => ForumTopic::query()->count(),
        'group_activities' => ForumGroupActivity::query()->count(),
        'group_announcements' => ForumGroupAnnouncement::query()->count(),
        'group_files' => ForumGroupFile::query()->count(),
        'group_polls' => ForumPoll::query()->count(),
        'forum_journals' => ForumJournal::query()->count(),
        'forum_journal_entries' => ForumJournalEntry::query()->count(),
        'listings' => Listing::query()->count(),
        'credentials' => Credential::query()->count(),
        'guides' => KnowledgeArticle::query()->count(),
        'guide_collaborators' => KnowledgeArticleCollaborator::query()->count(),
        'guide_versions' => KnowledgeVersion::query()->count(),
        'guide_workflow_events' => KnowledgeWorkflowEvent::query()->count(),
        'adoption_events' => AdoptionEvent::query()->count(),
        'devices' => SmartDevice::query()->count(),
        'content_publications' => ContentPublication::query()->count(),
        'pet_profile_media' => PetProfileMedia::query()->count(),
    ])->toBe($firstCounts)
        ->and(User::query()->where('actor_key', 'mia-carter')->count())->toBe(1)
        ->and(User::query()->count())->toBe(10)
        ->and(User::query()->where('email', 'user@example.com')->count())->toBe(1)
        ->and(User::query()->where('is_admin', true)->count())->toBe(1)
        ->and(User::query()->where('locale', 'lt')->count())->toBe(1)
        ->and(User::query()->where('status', 'blocked')->count())->toBe(1)
        ->and(User::query()->whereNull('email_verified_at')->count())->toBe(1)
        ->and(ContentPublication::query()
            ->where('idempotency_key', 'discovery-demo-post-v1')
            ->count())->toBe(1)
        ->and(ContentPublication::query()
            ->where('idempotency_key', 'page-identity-demo-post-v1')
            ->value('publication_key'))->toBe('page-identity-demo-post')
        ->and(KnowledgeArticle::query()
            ->whereNotNull('translation_group_key')
            ->whereNotNull('discussion_topic_id')
            ->count())->toBe(2)
        ->and(KnowledgeArticleCollaborator::query()->count())->toBe(10)
        ->and(KnowledgeWorkflowEvent::query()->count())->toBe(10)
        ->and(AdoptionCase::query()
            ->whereHas('listing', fn ($query) => $query
                ->where('slug', 'gentle-adult-cat-meta-is-ready-for-adoption'))
            ->value('provider_identity_status'))->toBe(AdoptionProviderIdentityStatus::Verified)
        ->and(Storage::disk('local')->allFiles('pet-profiles'))->toBe($firstPetMediaFiles);

    $viewer = User::query()->where('email', 'user@example.com')->firstOrFail();
    $publication = ContentPublication::query()
        ->where('publication_key', 'page-identity-demo-post')
        ->firstOrFail();

    $this->actingAs($viewer)
        ->get(route('content.show', $publication))
        ->assertSuccessful()
        ->assertSee('data-content-detail-identity', false);

    $representativeMedia = PetProfileMedia::query()
        ->with(['asset', 'profile'])
        ->whereHas('profile', fn ($query) => $query
            ->where('visibility', 'public')
            ->where('is_discoverable', true))
        ->whereHas('asset', fn ($query) => $query
            ->where('disk', 'local')
            ->where('mime_type', 'image/webp')
            ->where('path', 'like', 'pet-profiles/%/media/%'))
        ->firstOrFail();
    $representativeAsset = $representativeMedia->asset;

    expect($representativeAsset)->not->toBeNull()
        ->and(Storage::disk('local')->exists($representativeAsset->path))->toBeTrue()
        ->and(getimagesizefromstring(Storage::disk('local')->get($representativeAsset->path))['mime'] ?? null)
        ->toBe('image/webp');

    $this->get(route('pets.media.show', [
        'petProfile' => $representativeMedia->profile,
        'petProfileMedia' => $representativeMedia,
    ]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/webp');
});

test('demo seeding refuses an environment not explicitly allowed', function () {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(DatabaseSeeder::class))
        ->toThrow(LogicException::class);
});

test('direct demo seeders refuse an environment not explicitly allowed', function (
    string $seederClass,
) {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed($seederClass))->toThrow(LogicException::class);
})->with('direct demo seeders');

test('unrelated existing records do not suppress deterministic forum and expert graphs', function () {
    ForumTopic::factory()->create();
    ExpertProfile::factory()->create();

    seed(ForumSeeder::class);
    seed(ExpertSeeder::class);

    expect(ForumTopic::query()->where('slug', 'calm-lift-entry-after-loud-noise')->exists())
        ->toBeTrue()
        ->and(ExpertProfile::query()->where('slug', 'dr-emilia-vaitke')->exists())
        ->toBeTrue();
});

test('partial deterministic forum graph fails closed without adding records', function () {
    ForumTopic::factory()->create(['slug' => 'calm-lift-entry-after-loud-noise']);
    $topicCount = ForumTopic::query()->count();

    expect(fn () => seed(ForumSeeder::class))
        ->toThrow(LogicException::class, 'The deterministic forum demo graph is partially present.')
        ->and(ForumTopic::query()->count())->toBe($topicCount);
});

test('partial deterministic expert graph fails closed without adding records', function () {
    ExpertProfile::factory()->create(['slug' => 'dr-emilia-vaitke']);
    $profileCount = ExpertProfile::query()->count();

    expect(fn () => seed(ExpertSeeder::class))
        ->toThrow(LogicException::class, 'The deterministic expert demo graph is partially present.')
        ->and(ExpertProfile::query()->count())->toBe($profileCount);
});

test('deterministic seeders reject missing children after their roots exist', function (
    string $seederClass,
    Closure $deleteChild,
) {
    seed($seederClass);
    $deleteChild();

    expect(fn () => seed($seederClass))->toThrow(LogicException::class);
})->with([
    'forum answer' => [
        ForumSeeder::class,
        static fn () => ForumAnswer::query()
            ->whereHas('topic', fn ($query) => $query->where('slug', 'calm-lift-entry-after-loud-noise'))
            ->firstOrFail()
            ->delete(),
    ],
    'expert credential' => [
        ExpertSeeder::class,
        static fn () => Credential::query()
            ->whereHas('expertProfile', fn ($query) => $query->where('slug', 'dr-emilia-vaitke'))
            ->firstOrFail()
            ->delete(),
    ],
    'medical record' => [
        MedicalRecordSeeder::class,
        static fn () => MedicalRecord::query()
            ->where('slug', 'scout-health')
            ->firstOrFail()
            ->weightEntries()
            ->where('weight_grams', 19200)
            ->where('measurement_context', 'Routine clinic visit')
            ->firstOrFail()
            ->delete(),
    ],
]);

test('medical seeding validates its named child graph before updating deterministic roots', function () {
    seed(MedicalRecordSeeder::class);

    $scout = MedicalRecord::query()->where('slug', 'scout-health')->firstOrFail();
    $scout->forceFill(['allergy_knowledge_status' => MedicalKnowledgeStatus::Unknown])->save();
    $scout->weightEntries()
        ->where('weight_grams', 19200)
        ->where('measurement_context', 'Routine clinic visit')
        ->firstOrFail()
        ->delete();

    expect(fn () => seed(MedicalRecordSeeder::class))
        ->toThrow(LogicException::class, 'The deterministic medical demo graph is partially present.')
        ->and($scout->refresh()->allergy_knowledge_status)
        ->toBe(MedicalKnowledgeStatus::Unknown);
});

test('discovery demo seeding refuses an environment not explicitly allowed', function () {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(DiscoveryDemoSeeder::class))
        ->toThrow(LogicException::class);
});

test('marketplace demo seeding refuses a disallowed environment without mutating a colliding listing', function () {
    $listing = Listing::factory()->create([
        'slug' => 'rehabilitation-ramp-rental-vilnius',
        'quantity' => 17,
    ]);
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(MarketplaceExpansionSeeder::class))
        ->toThrow(LogicException::class)
        ->and($listing->refresh()->quantity)->toBe(17)
        ->and(Reservation::query()->where('listing_id', $listing->id)->exists())->toBeFalse()
        ->and(Order::query()->where('listing_id', $listing->id)->exists())->toBeFalse();
});

test('performance seeding is opt in deterministic and repeatable', function () {
    seed(PerformanceSeeder::class);
    seed(PerformanceSeeder::class);

    expect(PetProfile::query()
        ->where('profile_key', 'like', 'performance-pet-%')
        ->count())->toBe(250)
        ->and(User::query()->where('actor_key', 'performance-owner')->count())->toBe(1);
});

test('performance seeding refuses an environment not explicitly allowed', function () {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(PerformanceSeeder::class))
        ->toThrow(LogicException::class);
});

<?php

declare(strict_types=1);

use App\Enums\PlaceSubmissionStatus;
use App\Enums\UserStatus;
use App\Models\BreedRegistry;
use App\Models\CommunityAnimalGroup;
use App\Models\DomesticClassification;
use App\Models\ForumBadge;
use App\Models\ForumCategory;
use App\Models\ForumCategoryLifecycleRule;
use App\Models\ForumCategoryTranslation;
use App\Models\ForumExpertSession;
use App\Models\ForumGroupFile;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumReportReason;
use App\Models\ForumReputationDimension;
use App\Models\ForumTopicType;
use App\Models\ForumTrustLevel;
use App\Models\PlaceMergeRedirect;
use App\Models\PlaceSubmission;
use App\Models\PlaceSubmissionRevision;
use App\Models\Taxon;
use App\Models\TaxonName;
use App\Models\TaxonSource;
use App\Models\TaxonVersion;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RepresentativeModelManifest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\seed;

beforeEach(function (): void {
    Storage::fake('local');
});

/** @return array<class-string<Model>, string> */
function persistentApplicationModels(): array
{
    $models = [];

    foreach (glob(app_path('Models/*.php')) ?: [] as $path) {
        $class = 'App\\Models\\'.pathinfo($path, PATHINFO_FILENAME);

        if (is_subclass_of($class, Model::class)) {
            $models[$class] = (new $class)->getTable();
        }
    }

    ksort($models);

    return $models;
}

/** @return list<class-string<Model>> */
function representativeReferenceModels(): array
{
    return [
        BreedRegistry::class,
        CommunityAnimalGroup::class,
        DomesticClassification::class,
        ForumBadge::class,
        ForumCategory::class,
        ForumCategoryLifecycleRule::class,
        ForumCategoryTranslation::class,
        ForumModerationActionDefinition::class,
        ForumReportReason::class,
        ForumReputationDimension::class,
        ForumTopicType::class,
        ForumTrustLevel::class,
        Taxon::class,
        TaxonName::class,
        TaxonSource::class,
        TaxonVersion::class,
    ];
}

/** @param class-string<Model> $modelClass */
function unscopedModelCount(string $modelClass): int
{
    return (new $modelClass)->newQueryWithoutScopes()->count();
}

/**
 * @param  list<string>  $foreignKeys
 * @param  list<string>  $requiredMetadata
 * @param  list<string>  $timestamps
 */
function assertCompletePivotRows(
    string $table,
    array $foreignKeys,
    array $requiredMetadata,
    array $timestamps,
): void {
    expect(DB::table($table)->count())
        ->toBeGreaterThanOrEqual(10);

    foreach ([...$foreignKeys, ...$requiredMetadata, ...$timestamps] as $column) {
        expect(DB::table($table)->whereNull($column)->count())->toBe(0);
    }
}

function assertSeededForeignKeysHaveNoOrphans(): void
{
    foreach (Schema::getTables() as $tableDefinition) {
        $table = $tableDefinition['name'];

        foreach (Schema::getForeignKeys($table) as $position => $foreignKey) {
            $columns = $foreignKey['columns'];
            $foreignTable = $foreignKey['foreign_table'];
            $foreignColumns = $foreignKey['foreign_columns'];
            $childAlias = 'seed_child';
            $parentAlias = "seed_parent_{$position}";

            $orphans = DB::table("{$table} as {$childAlias}")
                ->leftJoin(
                    "{$foreignTable} as {$parentAlias}",
                    function (JoinClause $join) use (
                        $childAlias,
                        $columns,
                        $foreignColumns,
                        $parentAlias,
                    ): void {
                        foreach ($columns as $key => $column) {
                            $join->on(
                                "{$parentAlias}.{$foreignColumns[$key]}",
                                '=',
                                "{$childAlias}.{$column}",
                            );
                        }
                    },
                );

            foreach ($columns as $column) {
                $orphans->whereNotNull("{$childAlias}.{$column}");
            }

            $orphanCount = $orphans
                ->whereNull("{$parentAlias}.{$foreignColumns[0]}")
                ->count();

            expect($orphanCount)->toBe(0);
        }
    }
}

test('root seeding creates the complete bounded representative domain and is repeatable', function () {
    seed(DatabaseSeeder::class);

    $manifestModels = RepresentativeModelManifest::classes();
    $dynamicModels = array_keys(persistentApplicationModels());
    sort($manifestModels);
    sort($dynamicModels);

    expect($manifestModels)
        ->toHaveCount(211)
        ->toBe($dynamicModels);

    $target = (new User)->newQueryWithoutScopes()
        ->where('email', 'user@example.com')
        ->firstOrFail();
    $modelCounts = [];

    foreach (array_keys(persistentApplicationModels()) as $modelClass) {
        $modelCounts[$modelClass] = unscopedModelCount($modelClass);
    }

    $underfilled = array_filter($modelCounts, static fn (int $count): bool => $count < 10);
    $nonReferenceCounts = array_diff_key(
        $modelCounts,
        array_flip(representativeReferenceModels()),
    );
    $oversizedNonReferenceModels = array_filter(
        $nonReferenceCounts,
        static fn (int $count): bool => $count > 125,
    );
    $pivotTables = [
        'community_animal_group_taxon',
        'content_publication_media',
        'forum_category_relations',
        'forum_event_registration_pets',
        'forum_event_taxon',
        'forum_group_taxon',
        'forum_moderation_case_reports',
        'forum_topic_taxon',
    ];
    $pivotCounts = collect($pivotTables)
        ->mapWithKeys(static fn (string $table): array => [$table => DB::table($table)->count()])
        ->all();

    expect($underfilled)->toBe([])
        ->and(array_filter($pivotCounts, static fn (int $count): bool => $count < 10))->toBe([])
        ->and($oversizedNonReferenceModels)->toBe([])
        ->and(array_sum($nonReferenceCounts))->toBeLessThanOrEqual(3500)
        ->and(array_sum($modelCounts) + array_sum($pivotCounts))->toBeLessThanOrEqual(15000)
        ->and(unscopedModelCount(User::class))->toBe(10)
        ->and($target->actor_key)->toBe('mia-carter')
        ->and($target->name)->toBe('Mia Carter')
        ->and($target->email)->toBe('user@example.com')
        ->and($target->email_verified_at)->not->toBeNull()
        ->and($target->last_login_at)->not->toBeNull()
        ->and($target->locale)->toBe('en')
        ->and($target->timezone)->toBe('Europe/Vilnius')
        ->and($target->status)->toBe(UserStatus::Active)
        ->and($target->is_admin)->toBeFalse()
        ->and($target->created_at)->not->toBeNull()
        ->and($target->updated_at)->not->toBeNull()
        ->and($target->password)->not->toBe('password')
        ->and(Hash::check('password', $target->password))->toBeTrue();

    expect(ForumExpertSession::query()
        ->whereColumn('question_opens_at', '>=', 'question_closes_at')
        ->orWhereColumn('question_closes_at', '>', 'ends_at')
        ->orWhereColumn('question_opens_at', '>', 'starts_at')
        ->orWhereColumn('starts_at', '>=', 'ends_at')
        ->exists())->toBeFalse();

    $targetRelations = [
        'petProfiles' => $target->petProfiles()->exists(),
        'socialActor' => $target->socialActor()->exists(),
        'ownedOrganizations' => $target->ownedOrganizations()->exists(),
        'organizationMemberships' => $target->organizationMemberships()->exists(),
        'forumGroupMemberships' => $target->forumGroupMemberships()->exists(),
        'authoredForumTopics' => $target->authoredForumTopics()->exists(),
        'authoredForumJournalEntries' => $target->authoredForumJournalEntries()->exists(),
        'mentorshipsAsMentee' => $target->mentorshipsAsMentee()->exists(),
        'adoptionApplications' => $target->adoptionApplications()->exists(),
        'searchCases' => $target->searchCases()->exists(),
        'medicalRecords' => $target->medicalRecords()->exists(),
        'careJournals' => $target->careJournals()->exists(),
        'bookings' => $target->bookings()->exists(),
        'listings' => $target->listings()->exists(),
        'ownedPlaces' => $target->ownedPlaces()->exists(),
        'placeSubmissions' => $target->placeSubmissions()->exists(),
        'smartDevices' => $target->smartDevices()->exists(),
    ];
    $missingTargetRelations = array_keys(array_filter(
        $targetRelations,
        static fn (bool $exists): bool => ! $exists,
    ));

    assertCompletePivotRows(
        'community_animal_group_taxon',
        ['community_animal_group_id', 'taxon_id'],
        ['position', 'includes_descendants'],
        ['created_at', 'updated_at'],
    );
    expect(DB::table('community_animal_group_taxon')->where('position', '>', 0)->exists())
        ->toBeTrue()
        ->and(DB::table('community_animal_group_taxon')->where('includes_descendants', true)->exists())
        ->toBeTrue();

    assertCompletePivotRows(
        'content_publication_media',
        ['content_publication_id', 'content_media_asset_id'],
        ['position', 'is_cover'],
        ['created_at', 'updated_at'],
    );
    expect(DB::table('content_publication_media')->where('is_cover', true)->exists())
        ->toBeTrue()
        ->and(DB::table('content_publication_media')->whereNotNull('caption')->where('caption', '!=', '')->exists())
        ->toBeTrue();

    assertCompletePivotRows(
        'forum_category_relations',
        ['forum_category_id', 'related_forum_category_id'],
        ['relation_type', 'position'],
        ['created_at', 'updated_at'],
    );
    expect(DB::table('forum_category_relations')
        ->whereColumn('forum_category_id', 'related_forum_category_id')
        ->exists())->toBeFalse()
        ->and(DB::table('forum_category_relations')->where('relation_type', '')->exists())
        ->toBeFalse();

    assertCompletePivotRows(
        'forum_event_registration_pets',
        ['forum_event_registration_id', 'pet_profile_id'],
        ['eligibility_status', 'verification_source'],
        ['created_at', 'updated_at'],
    );
    expect(DB::table('forum_event_registration_pets')->whereNotNull('conditions')->exists())
        ->toBeTrue()
        ->and(DB::table('forum_event_registration_pets')->whereNotNull('checked_in_at')->exists())
        ->toBeTrue()
        ->and(DB::table('forum_event_registration_pets')->whereNotNull('checked_out_at')->exists())
        ->toBeTrue()
        ->and(DB::table('forum_event_registration_pets')
            ->whereNotNull('checked_in_at')
            ->whereNotNull('checked_out_at')
            ->whereColumn('checked_out_at', '<', 'checked_in_at')
            ->exists())->toBeFalse();

    foreach (['forum_event_taxon', 'forum_group_taxon'] as $table) {
        assertCompletePivotRows(
            $table,
            [$table === 'forum_event_taxon' ? 'forum_event_id' : 'forum_group_id', 'taxon_id'],
            ['is_primary'],
            ['created_at', 'updated_at'],
        );
        expect(DB::table($table)->where('is_primary', true)->exists())
            ->toBeTrue();
    }

    assertCompletePivotRows(
        'forum_moderation_case_reports',
        ['forum_moderation_case_id', 'forum_report_id', 'linked_by_user_id'],
        [],
        ['created_at'],
    );

    assertCompletePivotRows(
        'forum_topic_taxon',
        ['forum_topic_id', 'taxon_id'],
        ['context_type'],
        ['created_at', 'updated_at'],
    );
    $topicTaxonSnapshot = DB::table('forum_topic_taxon')
        ->whereNotNull('topic_time_snapshot')
        ->value('topic_time_snapshot');
    expect($topicTaxonSnapshot)->not->toBeNull();

    $decodedTopicTaxonSnapshot = json_decode(
        (string) $topicTaxonSnapshot,
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    expect($decodedTopicTaxonSnapshot)->toHaveKeys(['scientific_name', 'captured_by']);

    $groupFile = (new ForumGroupFile)->newQueryWithoutScopes()
        ->where('upload_idempotency_key', 'demo-group-content:file:v1')
        ->firstOrFail();
    expect(Storage::disk($groupFile->disk)->exists($groupFile->path))->toBeTrue();

    $groupFileContents = Storage::disk($groupFile->disk)->get($groupFile->path);

    expect($groupFile->disk)->toBe('local')
        ->and($groupFile->path)->not->toBe('')
        ->and($groupFile->description)->not->toBeNull()
        ->and($groupFile->byte_size)->toBe(strlen($groupFileContents))
        ->and($groupFile->checksum)->toBe(hash('sha256', $groupFileContents));

    foreach (PlaceSubmissionStatus::cases() as $status) {
        expect(PlaceSubmission::query()->where('status', $status->value)->exists())->toBeTrue();
    }

    expect(PlaceSubmission::query()->whereDoesntHave('revisions')->exists())->toBeFalse()
        ->and(PlaceSubmission::query()->whereDoesntHave('facts')->exists())->toBeFalse()
        ->and(PlaceSubmission::query()->whereDoesntHave('events')->exists())->toBeFalse()
        ->and(PlaceSubmission::query()->whereDoesntHave('duplicateCandidates')->exists())->toBeFalse()
        ->and(PlaceSubmissionRevision::query()->whereDoesntHave('facts')->exists())->toBeFalse()
        ->and(PlaceMergeRedirect::query()
            ->whereHas('sourcePlace', fn ($query) => $query->where('status', '!=', 'merged'))
            ->whereNull('restored_at')
            ->exists())->toBeFalse()
        ->and(PlaceMergeRedirect::query()
            ->whereHas('sourcePlace', fn ($query) => $query->where('status', 'merged'))
            ->whereNotNull('restored_at')
            ->exists())->toBeFalse();

    assertSeededForeignKeysHaveNoOrphans();
    expect($missingTargetRelations)->toBe([]);

    seed(DatabaseSeeder::class);

    foreach ($modelCounts as $modelClass => $count) {
        expect(unscopedModelCount($modelClass))
            ->toBe($count);
    }

    foreach ($pivotCounts as $table => $count) {
        expect(DB::table($table)->count())
            ->toBe($count);
    }
});

test('root seeding rejects a conflicting target email without overwriting that identity', function () {
    $existingActor = (new User)->newQueryWithoutScopes()
        ->where('actor_key', 'mia-carter')
        ->firstOrFail();
    $existingActor->forceFill(['email' => 'mia@example.test'])->save();
    $existingActorId = $existingActor->id;
    $conflictingUser = User::factory()->create([
        'actor_key' => 'existing-target-email-owner',
        'name' => 'Existing Target Email Owner',
        'email' => 'user@example.com',
        'password' => 'existing-secret',
        'locale' => 'lt',
        'timezone' => 'Europe/Vilnius',
        'status' => UserStatus::Active,
    ]);

    expect(fn () => seed(DatabaseSeeder::class))->toThrow(
        LogicException::class,
        'Demo identity conflict for actor mia-carter and email user@example.com.',
    );

    $preservedUser = (new User)->newQueryWithoutScopes()->findOrFail($conflictingUser->id);
    $preservedActor = (new User)->newQueryWithoutScopes()
        ->where('actor_key', 'mia-carter')
        ->firstOrFail();

    expect($preservedUser->actor_key)->toBe('existing-target-email-owner')
        ->and($preservedUser->name)->toBe('Existing Target Email Owner')
        ->and($preservedUser->email)->toBe('user@example.com')
        ->and($preservedUser->locale)->toBe('lt')
        ->and(Hash::check('existing-secret', $preservedUser->password))->toBeTrue()
        ->and($preservedActor->id)->toBe($existingActorId)
        ->and($preservedActor->email)->toBe('mia@example.test')
        ->and((new User)->newQueryWithoutScopes()
            ->where('email', 'user@example.com')
            ->count())->toBe(1);
});

test('root seeding rejects a conflicting target actor key without overwriting that identity', function () {
    $existingActor = (new User)->newQueryWithoutScopes()
        ->where('actor_key', 'mia-carter')
        ->firstOrFail();
    $existingActor->forceFill([
        'name' => 'Existing Actor Owner',
        'email' => 'existing-actor-owner@example.test',
        'password' => 'existing-secret',
        'locale' => 'lt',
        'timezone' => 'Europe/Vilnius',
        'status' => UserStatus::Active,
    ])->save();

    expect(fn () => seed(DatabaseSeeder::class))->toThrow(
        LogicException::class,
        'Demo identity conflict for actor mia-carter and email user@example.com.',
    );

    $preservedActor = (new User)->newQueryWithoutScopes()->findOrFail($existingActor->id);

    expect($preservedActor->actor_key)->toBe('mia-carter')
        ->and($preservedActor->name)->toBe('Existing Actor Owner')
        ->and($preservedActor->email)->toBe('existing-actor-owner@example.test')
        ->and($preservedActor->locale)->toBe('lt')
        ->and(Hash::check('existing-secret', $preservedActor->password))->toBeTrue()
        ->and((new User)->newQueryWithoutScopes()
            ->where('email', 'user@example.com')
            ->count())->toBe(0);
});

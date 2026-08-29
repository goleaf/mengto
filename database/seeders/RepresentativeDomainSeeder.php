<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentMediaAsset;
use App\Models\ContentPublication;
use App\Models\ForumCategory;
use App\Models\ForumEvent;
use App\Models\ForumGroup;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventRegistrationPet;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipFeedback;
use App\Models\ForumModerationCase;
use App\Models\ForumReport;
use App\Models\ForumPoll;
use App\Models\ForumPollVote;
use App\Models\ForumTopic;
use App\Models\Taxon;
use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use LogicException;

final class RepresentativeDomainSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var array<class-string<Model>, EloquentCollection<int, Model>> */
    private array $recyclePools = [];

    /** @var array<string, class-string<Model>> */
    private array $modelsByTable = [];

    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Representative seed data may only be created in an explicitly allowed environment.');
        }

        $this->initializePools();

        foreach (RepresentativeModelManifest::classes() as $modelClass) {
            if ($modelClass === User::class) {
                continue;
            }

            $this->topUpModel($modelClass);
        }

        $this->seedPivots();
    }

    private function initializePools(): void
    {
        foreach (RepresentativeModelManifest::classes() as $modelClass) {
            $model = new $modelClass;
            $this->modelsByTable[$model->getTable()] = $modelClass;
            $this->refreshPool($modelClass);
        }
    }

    /** @param class-string<Model> $modelClass */
    private function topUpModel(string $modelClass): void
    {
        $missing = max(
            0,
            RepresentativeModelManifest::TARGET_COUNT - $modelClass::query()->count(),
        );

        for ($position = 0; $position < $missing; $position++) {
            $factory = $modelClass::factory()->recycle($this->recycleModels());
            $overrides = $this->unusedUniqueForeignKeys($modelClass);
            $model = $factory->state($overrides)->create();

            $this->remember($model);
        }

        $this->refreshPool($modelClass);
    }

    /**
     * Allocate unused parents for one-to-one relationships before a factory is
     * expanded. This prevents a recycled parent from violating a unique FK.
     *
     * @param  class-string<Model>  $modelClass
     * @return array<string, mixed>
     */
    private function unusedUniqueForeignKeys(string $modelClass): array
    {
        $model = new $modelClass;
        $table = $model->getTable();
        $foreignKeys = collect(Schema::getForeignKeys($table));
        $overrides = [];

        foreach (Schema::getIndexes($table) as $index) {
            $columns = $index['columns'] ?? [];

            if (($index['unique'] ?? false) !== true || $columns === []) {
                continue;
            }

            if (count($columns) === 2) {
                $overrides = [
                    ...$overrides,
                    ...$this->unusedTwoForeignKeyTuple(
                        modelClass: $modelClass,
                        columns: array_values($columns),
                        foreignKeys: $foreignKeys,
                    ),
                ];

                continue;
            }

            if (count($columns) !== 1) {
                continue;
            }

            $column = $columns[0];
            $foreignKey = $foreignKeys->first(
                static fn (array $key): bool => ($key['columns'] ?? []) === [$column],
            );

            if (! is_array($foreignKey)) {
                continue;
            }

            $foreignTable = $foreignKey['foreign_table'] ?? null;
            $foreignColumn = $foreignKey['foreign_columns'][0] ?? null;
            $parentClass = is_string($foreignTable)
                ? ($this->modelsByTable[$foreignTable] ?? null)
                : null;

            if (! is_string($parentClass) || ! is_string($foreignColumn)) {
                continue;
            }

            $parent = $parentClass::query()
                ->whereNotIn(
                    $foreignColumn,
                    $modelClass::query()->whereNotNull($column)->select($column),
                )
                ->orderBy($foreignColumn)
                ->first();

            if (! $parent instanceof Model) {
                if ($parentClass === User::class) {
                    throw new LogicException("No unused deterministic user remains for {$table}.{$column}.");
                }

                $parent = $parentClass::factory()
                    ->recycle($this->recycleModels())
                    ->create();
                $this->remember($parent);
            }

            $overrides[$column] = $parent->getAttribute($foreignColumn);
        }

        return [
            ...$overrides,
            ...$this->domainSpecificOverrides($modelClass),
        ];
    }

    /**
     * Preserve invariants that cannot be inferred from foreign keys alone.
     *
     * @param  class-string<Model>  $modelClass
     * @return array<string, mixed>
     */
    private function domainSpecificOverrides(string $modelClass): array
    {
        if ($modelClass === ForumMentorshipFeedback::class) {
            $mentorship = ForumMentorship::query()
                ->whereDoesntHave('feedback')
                ->orderBy('id')
                ->firstOrFail();

            return ['forum_mentorship_id' => $mentorship->id];
        }

        if ($modelClass === ForumPollVote::class) {
            $poll = ForumPoll::query()
                ->whereDoesntHave('votes')
                ->orderBy('id')
                ->firstOrFail();

            return ['forum_poll_id' => $poll->id];
        }

        if ($modelClass === ForumEventRegistrationPet::class) {
            $registrations = ForumEventRegistration::query()
                ->with('user:id')
                ->orderBy('id')
                ->get();

            foreach ($registrations as $registration) {
                $pet = PetProfile::query()
                    ->where('user_id', $registration->user_id)
                    ->whereNotIn(
                        'id',
                        ForumEventRegistrationPet::query()
                            ->where('forum_event_registration_id', $registration->id)
                            ->select('pet_profile_id'),
                    )
                    ->orderBy('id')
                    ->first();

                if (! $pet instanceof PetProfile) {
                    $pet = PetProfile::factory()->for($registration->user)->create();
                    $this->remember($pet);
                }

                return [
                    'forum_event_registration_id' => $registration->id,
                    'pet_profile_id' => $pet->id,
                ];
            }
        }

        return [];
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<string>  $columns
     * @param  Collection<int, array<string, mixed>>  $foreignKeys
     * @return array<string, mixed>
     */
    private function unusedTwoForeignKeyTuple(
        string $modelClass,
        array $columns,
        Collection $foreignKeys,
    ): array {
        $definitions = [];

        foreach ($columns as $column) {
            $foreignKey = $foreignKeys->first(
                static fn (array $key): bool => ($key['columns'] ?? []) === [$column],
            );
            $foreignTable = is_array($foreignKey)
                ? ($foreignKey['foreign_table'] ?? null)
                : null;
            $foreignColumn = is_array($foreignKey)
                ? ($foreignKey['foreign_columns'][0] ?? null)
                : null;
            $parentClass = is_string($foreignTable)
                ? ($this->modelsByTable[$foreignTable] ?? null)
                : null;

            if (! is_string($parentClass) || ! is_string($foreignColumn)) {
                return [];
            }

            $definitions[] = [
                'column' => $column,
                'parent_class' => $parentClass,
                'parent_column' => $foreignColumn,
            ];
        }

        [$first, $second] = $definitions;
        $firstParents = $first['parent_class']::query()
            ->orderBy($first['parent_column'])
            ->limit(RepresentativeModelManifest::TARGET_COUNT)
            ->get();
        $secondParents = $second['parent_class']::query()
            ->orderBy($second['parent_column'])
            ->limit(RepresentativeModelManifest::TARGET_COUNT)
            ->get();
        $used = $modelClass::query()
            ->select($columns)
            ->get()
            ->mapWithKeys(static fn (Model $model): array => [
                $model->getAttribute($columns[0]).'|'.$model->getAttribute($columns[1]) => true,
            ]);

        foreach ($firstParents as $firstParent) {
            foreach ($secondParents as $secondParent) {
                $firstValue = $firstParent->getAttribute($first['parent_column']);
                $secondValue = $secondParent->getAttribute($second['parent_column']);

                if ($first['parent_class'] === $second['parent_class']
                    && $firstValue === $secondValue) {
                    continue;
                }

                if (! $used->has($firstValue.'|'.$secondValue)) {
                    return [
                        $first['column'] => $firstValue,
                        $second['column'] => $secondValue,
                    ];
                }
            }
        }

        return [];
    }

    /** @return Collection<int, Model> */
    private function recycleModels(): Collection
    {
        return collect($this->recyclePools)
            ->flatMap(static fn (EloquentCollection $models): array => $models->all())
            ->values();
    }

    /** @param class-string<Model> $modelClass */
    private function refreshPool(string $modelClass): void
    {
        $this->recyclePools[$modelClass] = $modelClass::query()
            ->orderBy((new $modelClass)->getKeyName())
            ->limit(RepresentativeModelManifest::TARGET_COUNT)
            ->get();
    }

    private function remember(Model $model): void
    {
        $modelClass = $model::class;
        $pool = $this->recyclePools[$modelClass] ?? new EloquentCollection;

        if ($pool->count() < RepresentativeModelManifest::TARGET_COUNT) {
            $pool->push($model);
            $this->recyclePools[$modelClass] = $pool;
        }
    }

    private function seedPivots(): void
    {
        $this->seedContentMedia();
        $this->seedCategoryRelations();
        $this->seedTaxonRelations();
        $this->seedModerationReports();
    }

    private function seedContentMedia(): void
    {
        $publications = ContentPublication::query()->orderBy('id')->limit(10)->get();
        $assets = ContentMediaAsset::query()->orderBy('id')->limit(10)->get();

        foreach ($publications as $position => $publication) {
            $asset = $assets[$position];
            $publication->mediaAssets()->syncWithoutDetaching([
                $asset->id => [
                    'position' => 1,
                    'is_cover' => true,
                    'caption' => "Representative media {$position}",
                ],
            ]);
        }
    }

    private function seedCategoryRelations(): void
    {
        $categories = ForumCategory::query()->orderBy('id')->limit(11)->get();

        for ($position = 0; $position < 10; $position++) {
            $categories[$position]->relatedCategories()->syncWithoutDetaching([
                $categories[$position + 1]->id => [
                    'relation_type' => 'related',
                    'position' => $position + 1,
                ],
            ]);
        }
    }

    private function seedTaxonRelations(): void
    {
        $taxa = Taxon::query()->orderBy('id')->limit(10)->get();
        $events = ForumEvent::query()->orderBy('id')->limit(10)->get();
        $groups = ForumGroup::query()->orderBy('id')->limit(10)->get();
        $topics = ForumTopic::query()->orderBy('id')->limit(10)->get();

        for ($position = 0; $position < 10; $position++) {
            $taxon = $taxa[$position];
            $events[$position]->taxa()->syncWithoutDetaching([
                $taxon->id => ['is_primary' => $position === 0],
            ]);
            $groups[$position]->taxa()->syncWithoutDetaching([
                $taxon->id => ['is_primary' => $position === 0],
            ]);
            $topics[$position]->taxa()->syncWithoutDetaching([
                $taxon->id => [
                    'context_type' => 'subject',
                    'topic_time_snapshot' => json_encode([
                        'scientific_name' => $taxon->scientific_name,
                        'captured_by' => 'representative-domain-seeder',
                    ], JSON_THROW_ON_ERROR),
                ],
            ]);
        }
    }

    private function seedModerationReports(): void
    {
        $cases = ForumModerationCase::query()->orderBy('id')->limit(10)->get();
        $reports = ForumReport::query()->orderBy('id')->limit(10)->get();
        $administrator = User::query()
            ->where('is_admin', true)
            ->orderBy('id')
            ->firstOrFail();

        foreach ($cases as $position => $case) {
            $case->reports()->syncWithoutDetaching([
                $reports[$position]->id => [
                    'linked_by_user_id' => $administrator->id,
                    'created_at' => now(),
                ],
            ]);
        }
    }
}

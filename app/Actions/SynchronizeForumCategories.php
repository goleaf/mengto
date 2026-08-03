<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ForumCategorySyncResult;
use App\Models\ForumCategory;
use App\Models\ForumCategoryAlias;
use App\Models\ForumCategoryRedirect;
use App\Models\ForumCategoryTranslation;
use App\Services\ForumCategoryCatalog;
use App\Services\ForumCategoryTree;
use App\Services\ForumTaxonomy;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Translation\Translator;

final class SynchronizeForumCategories
{
    private const LOCALES = ['en', 'lt', 'ru'];

    public function __construct(
        private readonly ForumCategoryCatalog $catalog,
        private readonly DatabaseManager $database,
        private readonly Translator $translator,
        private readonly CacheRepository $cache,
    ) {}

    public function handle(): ForumCategorySyncResult
    {
        $manifest = $this->catalog->load();
        $now = CarbonImmutable::now();

        $result = $this->database->transaction(function () use ($manifest, $now): ForumCategorySyncResult {
            $rootRows = [];

            foreach ($manifest['categories'] as $category) {
                $rootRows[] = $this->categoryRow($category, null, $now);
            }

            ForumCategory::query()->upsert(
                $rootRows,
                ['stable_key'],
                [
                    'slug',
                    'icon',
                    'position',
                    'visibility',
                    'moderation_level',
                    'schema_version',
                    'is_system_managed',
                    'is_active',
                    'rules',
                    'permissions',
                    'topic_type_keys',
                    'metadata',
                    'archived_at',
                    'updated_at',
                ],
            );

            $rootIds = ForumCategory::query()
                ->select(['id', 'stable_key'])
                ->whereIn('stable_key', array_column($rootRows, 'stable_key'))
                ->pluck('id', 'stable_key');
            $childRows = [];

            foreach ($manifest['categories'] as $category) {
                $parentId = (int) $rootIds->get($category['stable_key']);

                foreach ($category['subcategories'] as $position => $subcategory) {
                    $childRows[] = $this->categoryRow(
                        [
                            ...$subcategory,
                            'number' => $position + 1,
                            'icon' => null,
                            'purpose' => '',
                            'source' => $category['source'],
                            'subcategories' => [],
                        ],
                        $parentId,
                        $now,
                    );
                }
            }

            foreach (array_chunk($childRows, 500) as $chunk) {
                ForumCategory::query()->upsert(
                    $chunk,
                    ['stable_key'],
                    [
                        'parent_id',
                        'slug',
                        'position',
                        'visibility',
                        'moderation_level',
                        'schema_version',
                        'is_system_managed',
                        'is_active',
                        'rules',
                        'permissions',
                        'topic_type_keys',
                        'metadata',
                        'archived_at',
                        'updated_at',
                    ],
                );
            }

            $allDefinitions = [...$rootRows, ...$childRows];
            $allStableKeys = array_column($allDefinitions, 'stable_key');
            $categoryIds = ForumCategory::query()
                ->select(['id', 'stable_key'])
                ->whereIn('stable_key', $allStableKeys)
                ->pluck('id', 'stable_key');
            $sourceByKey = [];

            foreach ($manifest['categories'] as $category) {
                $sourceByKey[$category['stable_key']] = [
                    'name' => $category['name'],
                    'description' => $category['purpose'],
                    'slug' => $category['slug'],
                    'root' => true,
                ];

                foreach ($category['subcategories'] as $subcategory) {
                    $sourceByKey[$subcategory['stable_key']] = [
                        'name' => $subcategory['name'],
                        'description' => null,
                        'slug' => $subcategory['slug'],
                        'root' => false,
                    ];
                }
            }

            $translationRows = [];

            foreach ($sourceByKey as $stableKey => $source) {
                foreach (self::LOCALES as $locale) {
                    $translation = $this->translation(
                        $locale,
                        $source['slug'],
                        $source['name'],
                        $source['description'],
                        $source['root'],
                    );
                    $translationRows[] = [
                        'forum_category_id' => (int) $categoryIds->get($stableKey),
                        'locale' => $locale,
                        'name' => $translation['name'],
                        'description' => $translation['description'],
                        'notice' => null,
                        'rules_summary' => null,
                        'is_reviewed' => $translation['reviewed'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            foreach (array_chunk($translationRows, 500) as $chunk) {
                ForumCategoryTranslation::query()->upsert(
                    $chunk,
                    ['forum_category_id', 'locale'],
                    ['name', 'description', 'is_reviewed', 'updated_at'],
                );
            }

            $this->synchronizeLegacyAliases($categoryIds, $now);

            $obsolete = ForumCategory::query()
                ->select(['id', 'stable_key'])
                ->where('is_system_managed', true)
                ->whereNotIn('stable_key', $allStableKeys)
                ->where('is_active', true)
                ->get();

            if ($obsolete->isNotEmpty()) {
                ForumCategory::query()
                    ->whereKey($obsolete->modelKeys())
                    ->update([
                        'is_active' => false,
                        'archived_at' => $now,
                        'updated_at' => $now,
                    ]);
            }

            return new ForumCategorySyncResult(
                rootCount: count($rootRows),
                subcategoryCount: count($childRows),
                translationCount: count($translationRows),
                archivedStableKeys: $obsolete->pluck('stable_key')->all(),
            );
        }, 3);

        foreach (self::LOCALES as $locale) {
            $this->cache->forget(ForumCategoryTree::CACHE_KEY_PREFIX.$locale);
        }

        return $result;
    }

    /**
     * @param  Collection<string, int>  $categoryIds
     */
    private function synchronizeLegacyAliases(
        Collection $categoryIds,
        CarbonImmutable $now,
    ): void {
        $aliasRows = [];
        $redirectRows = [];
        $rootIdsBySlug = ForumCategory::query()
            ->roots()
            ->whereIn('id', $categoryIds->values())
            ->pluck('id', 'slug');

        foreach (ForumTaxonomy::LEGACY_CATEGORY_SLUGS as $legacy => $canonical) {
            if ($legacy === $canonical) {
                continue;
            }

            $categoryId = $rootIdsBySlug->get($canonical);

            if ($categoryId === null) {
                continue;
            }

            $aliasRows[] = [
                'forum_category_id' => $categoryId,
                'locale' => 'und',
                'alias' => $legacy,
                'normalized_alias' => mb_strtolower($legacy),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $redirectRows[] = [
                'source_slug' => $legacy,
                'target_forum_category_id' => $categoryId,
                'reason_code' => 'legacy-category-normalization',
                'is_permanent' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ForumCategoryAlias::query()->upsert(
            $aliasRows,
            ['forum_category_id', 'locale', 'normalized_alias'],
            ['alias', 'is_active', 'updated_at'],
        );
        ForumCategoryRedirect::query()->upsert(
            $redirectRows,
            ['source_slug'],
            ['target_forum_category_id', 'reason_code', 'is_permanent', 'updated_at'],
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function categoryRow(
        array $definition,
        ?int $parentId,
        CarbonImmutable $now,
    ): array {
        return [
            'parent_id' => $parentId,
            'stable_key' => $definition['stable_key'],
            'slug' => $definition['slug'],
            'icon' => $definition['icon'],
            'position' => $definition['number'],
            'visibility' => 'public',
            'moderation_level' => 'standard',
            'schema_version' => 1,
            'is_system_managed' => true,
            'is_active' => true,
            'rules' => json_encode([], JSON_THROW_ON_ERROR),
            'permissions' => json_encode([], JSON_THROW_ON_ERROR),
            'topic_type_keys' => json_encode(
                ['question', 'discussion', 'case', 'guide', 'support-request'],
                JSON_THROW_ON_ERROR,
            ),
            'metadata' => json_encode([
                'source' => $definition['source'],
                'source_payload_sha256' => '6f8a7f987c336a2247755cae1c2fd66dea66d83cfbf038b5fe31aa848097d773',
            ], JSON_THROW_ON_ERROR),
            'archived_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * @return array{name: string, description: string|null, reviewed: bool}
     */
    private function translation(
        string $locale,
        string $slug,
        string $fallback,
        ?string $fallbackDescription,
        bool $isRoot,
    ): array {
        if (! $isRoot) {
            return [
                'name' => $fallback,
                'description' => null,
                'reviewed' => $locale === 'en',
            ];
        }

        $normalizedSlug = str_replace('/', '.', $slug);
        $nameKey = "forum_categories.roots.{$normalizedSlug}";
        $descriptionKey = "forum_categories.descriptions.{$normalizedSlug}";
        $translatedName = $this->translator->get($nameKey, locale: $locale);
        $translatedDescription = $this->translator->get(
            $descriptionKey,
            locale: $locale,
        );

        return [
            'name' => $translatedName === $nameKey ? $fallback : $translatedName,
            'description' => $translatedDescription === $descriptionKey
                ? $fallbackDescription
                : $translatedDescription,
            'reviewed' => $translatedName !== $nameKey
                && $translatedDescription !== $descriptionKey,
        ];
    }
}

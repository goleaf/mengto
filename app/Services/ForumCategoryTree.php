<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

final readonly class ForumCategoryTree
{
    public function __construct(
        private ForumCategoryCatalog $catalog,
        private CacheRepository $cache,
    ) {}

    /**
     * @return array<string, array{label: string, icon: string, subcategories: array<string, string>}>
     */
    public function forLocale(string $locale): array
    {
        $locale = $this->supportedLocale($locale);

        if (
            ! Schema::hasTable('forum_categories')
            || ! ForumCategory::query()->active()->roots()->exists()
        ) {
            return $this->manifestFallback($locale);
        }

        return $this->cache->remember(
            "forum:category-tree:v1:locale:{$locale}",
            now()->addSeconds((int) config('taxonomy.tree_cache_seconds')),
            fn (): array => $this->databaseTree($locale),
        );
    }

    /** @return array<int, string> */
    public function rootOptions(string $locale): array
    {
        $locale = $this->supportedLocale($locale);

        if (! Schema::hasTable('forum_categories')) {
            return [];
        }

        $fallbackLocale = (string) config('app.fallback_locale');

        return ForumCategory::query()
            ->select(['id', 'stable_key', 'position'])
            ->active()
            ->roots()
            ->ordered()
            ->with([
                'translations' => fn ($query) => $query
                    ->select(['id', 'forum_category_id', 'locale', 'name'])
                    ->whereIn('locale', array_values(array_unique([
                        $locale,
                        $fallbackLocale,
                    ]))),
            ])
            ->limit(44)
            ->get()
            ->mapWithKeys(fn (ForumCategory $category): array => [
                $category->id => $this->translatedName(
                    $category->translations,
                    $locale,
                    $fallbackLocale,
                    $category->stable_key,
                ),
            ])
            ->all();
    }

    /**
     * @return array<string, array{label: string, icon: string, subcategories: array<string, string>}>
     */
    private function databaseTree(string $locale): array
    {
        $fallbackLocale = (string) config('app.fallback_locale');
        $locales = array_values(array_unique([$locale, $fallbackLocale]));
        $roots = ForumCategory::query()
            ->select(['id', 'slug', 'icon', 'position'])
            ->active()
            ->roots()
            ->ordered()
            ->with([
                'translations' => fn ($query) => $query
                    ->select(['id', 'forum_category_id', 'locale', 'name'])
                    ->whereIn('locale', $locales),
                'children' => fn ($query) => $query
                    ->select(['id', 'parent_id', 'slug', 'position'])
                    ->active()
                    ->ordered()
                    ->with([
                        'translations' => fn ($translationQuery) => $translationQuery
                            ->select(['id', 'forum_category_id', 'locale', 'name'])
                            ->whereIn('locale', $locales),
                    ]),
            ])
            ->get();
        $tree = [];

        foreach ($roots as $root) {
            $subcategories = [];

            foreach ($root->children as $child) {
                $subcategories[$child->slug] = $this->translatedName(
                    $child->translations,
                    $locale,
                    $fallbackLocale,
                    $child->slug,
                );
            }

            $tree[$root->slug] = [
                'label' => $this->translatedName(
                    $root->translations,
                    $locale,
                    $fallbackLocale,
                    $root->slug,
                ),
                'icon' => $root->icon ?? 'messages-square',
                'subcategories' => $subcategories,
            ];
        }

        return $tree;
    }

    /**
     * @return array<string, array{label: string, icon: string, subcategories: array<string, string>}>
     */
    private function manifestFallback(string $locale): array
    {
        $tree = [];

        foreach ($this->catalog->load()['categories'] as $category) {
            $translationKey = 'forum_categories.roots.'.str_replace('/', '.', $category['slug']);
            $translated = trans($translationKey, locale: $locale);
            $tree[$category['slug']] = [
                'label' => $translated === $translationKey
                    ? $category['name']
                    : $translated,
                'icon' => $category['icon'],
                'subcategories' => collect($category['subcategories'])
                    ->mapWithKeys(static fn (array $subcategory): array => [
                        $subcategory['slug'] => $subcategory['name'],
                    ])
                    ->all(),
            ];
        }

        return $tree;
    }

    /**
     * @param  Collection<int, ForumCategoryTranslation>  $translations
     */
    private function translatedName(
        Collection $translations,
        string $locale,
        string $fallbackLocale,
        string $fallback,
    ): string {
        $localized = $translations->firstWhere('locale', $locale);

        if ($localized instanceof ForumCategoryTranslation) {
            return $localized->name;
        }

        $fallbackTranslation = $translations->firstWhere('locale', $fallbackLocale);

        return $fallbackTranslation instanceof ForumCategoryTranslation
            ? $fallbackTranslation->name
            : $fallback;
    }

    private function supportedLocale(string $locale): string
    {
        $supported = config('platform.supported_locales', ['en']);

        return in_array($locale, $supported, true)
            ? $locale
            : (string) config('app.fallback_locale');
    }
}

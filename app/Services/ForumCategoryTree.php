<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final readonly class ForumCategoryTree
{
    public const CACHE_KEY_PREFIX = 'forum:category-tree:v2:locale:';

    public function __construct(
        private ForumCategoryCatalog $catalog,
        private CacheRepository $cache,
    ) {}

    /**
     * @return array<string, array{label: string, description: string|null, icon: string, subcategories: array<string, string>}>
     */
    public function forLocale(string $locale): array
    {
        $locale = $this->supportedLocale($locale);

        return $this->cache->remember(
            self::CACHE_KEY_PREFIX.$locale,
            now()->addSeconds((int) config('taxonomy.tree_cache_seconds')),
            function () use ($locale): array {
                if (
                    ! Schema::hasTable('forum_categories')
                    || ! ForumCategory::query()->active()->roots()->exists()
                ) {
                    return $this->manifestFallback($locale);
                }

                return $this->databaseTree($locale);
            },
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
                    ->select([
                        'id',
                        'forum_category_id',
                        'locale',
                        'name',
                        'is_reviewed',
                    ])
                    ->whereIn('locale', array_values(array_unique([
                        $locale,
                        $fallbackLocale,
                    ]))),
            ])
            ->limit(44)
            ->get()
            ->mapWithKeys(fn (ForumCategory $category): array => [
                $category->id => $this->displayName($this->translatedName(
                    $category->translations,
                    $locale,
                    $fallbackLocale,
                    $category->stable_key,
                )),
            ])
            ->all();
    }

    /**
     * @return array<string, array{label: string, description: string|null, icon: string, subcategories: array<string, string>}>
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
                    ->select([
                        'id',
                        'forum_category_id',
                        'locale',
                        'name',
                        'description',
                        'is_reviewed',
                    ])
                    ->whereIn('locale', $locales),
                'children' => fn ($query) => $query
                    ->select(['id', 'parent_id', 'slug', 'position'])
                    ->active()
                    ->ordered()
                    ->with([
                        'translations' => fn ($translationQuery) => $translationQuery
                            ->select([
                                'id',
                                'forum_category_id',
                                'locale',
                                'name',
                                'is_reviewed',
                            ])
                            ->whereIn('locale', $locales),
                    ]),
            ])
            ->get();
        $tree = [];

        foreach ($roots as $root) {
            $subcategories = [];

            foreach ($root->children as $child) {
                $subcategories[$child->slug] = $this->displayName($this->translatedName(
                    $child->translations,
                    $locale,
                    $fallbackLocale,
                    $child->slug,
                ));
            }

            $tree[$root->slug] = [
                'label' => $this->displayName($this->translatedName(
                    $root->translations,
                    $locale,
                    $fallbackLocale,
                    $root->slug,
                )),
                'description' => $this->translatedDescription(
                    $root->translations,
                    $locale,
                    $fallbackLocale,
                ),
                'icon' => $root->icon ?? 'messages-square',
                'subcategories' => $subcategories,
            ];
        }

        return $tree;
    }

    /**
     * @return array<string, array{label: string, description: string|null, icon: string, subcategories: array<string, string>}>
     */
    private function manifestFallback(string $locale): array
    {
        $tree = [];

        foreach ($this->catalog->load()['categories'] as $category) {
            $translationKey = 'forum_categories.roots.'.str_replace('/', '.', $category['slug']);
            $translated = trans($translationKey, locale: $locale);
            $tree[$category['slug']] = [
                'label' => $this->displayName($translated === $translationKey
                    ? $category['name']
                    : $translated),
                'description' => $this->manifestDescription(
                    $locale,
                    $category['slug'],
                    $category['purpose'],
                ),
                'icon' => $category['icon'],
                'subcategories' => collect($category['subcategories'])
                    ->mapWithKeys(fn (array $subcategory): array => [
                        $subcategory['slug'] => $this->displayName($subcategory['name']),
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
        $localized = $translations->first(
            fn (ForumCategoryTranslation $translation): bool => $translation->locale === $locale
                && $translation->is_reviewed,
        );

        if ($localized instanceof ForumCategoryTranslation) {
            return $localized->name;
        }

        $fallbackTranslation = $translations->first(
            fn (ForumCategoryTranslation $translation): bool => $translation->locale === $fallbackLocale
                && $translation->is_reviewed,
        );

        return $fallbackTranslation instanceof ForumCategoryTranslation
            ? $fallbackTranslation->name
            : $fallback;
    }

    /**
     * @param  Collection<int, ForumCategoryTranslation>  $translations
     */
    private function translatedDescription(
        Collection $translations,
        string $locale,
        string $fallbackLocale,
    ): ?string {
        $localized = $translations->first(
            fn (ForumCategoryTranslation $translation): bool => $translation->locale === $locale
                && $translation->is_reviewed
                && filled($translation->description),
        );

        if ($localized instanceof ForumCategoryTranslation) {
            return $localized->description;
        }

        $fallbackTranslation = $translations->first(
            fn (ForumCategoryTranslation $translation): bool => $translation->locale === $fallbackLocale
                && $translation->is_reviewed
                && filled($translation->description),
        );

        return $fallbackTranslation instanceof ForumCategoryTranslation
            ? $fallbackTranslation->description
            : null;
    }

    private function manifestDescription(
        string $locale,
        string $slug,
        string $fallback,
    ): string {
        $translationKey = 'forum_categories.descriptions.'.str_replace('/', '.', $slug);
        $translated = trans($translationKey, locale: $locale);

        return $translated === $translationKey ? $fallback : $translated;
    }

    private function displayName(string $name): string
    {
        return Str::ucfirst(Str::squish($name));
    }

    private function supportedLocale(string $locale): string
    {
        $supported = config('platform.supported_locales', ['en']);

        return in_array($locale, $supported, true)
            ? $locale
            : (string) config('app.fallback_locale');
    }
}

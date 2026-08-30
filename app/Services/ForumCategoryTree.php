<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

final readonly class ForumCategoryTree
{
    public const CACHE_KEY_PREFIX = 'forum:category-tree:v4:locale:';

    private const AUDIENCE_ADMINISTRATOR = 'administrator';

    private const AUDIENCE_GUEST = 'guest';

    private const AUDIENCE_MEMBER = 'member';

    public function __construct(
        private ForumCategoryCatalog $catalog,
        private CacheRepository $cache,
        private AuthFactory $auth,
    ) {}

    /**
     * @return array<string, array{label: string, description: string|null, notice: string|null, icon: string, subcategories: array<string, string>}>
     */
    public function forLocale(string $locale): array
    {
        $locale = $this->supportedLocale($locale);
        $audience = $this->audience();

        return $this->rememberTree(
            self::cacheKey($locale, $audience),
            function () use ($locale, $audience): array {
                if (
                    ! Schema::hasTable('forum_categories')
                    || ! ForumCategory::query()->active()->roots()->exists()
                ) {
                    return $this->manifestFallback($locale);
                }

                return $this->databaseTree($locale, $audience);
            },
        );
    }

    /** @return list<string> */
    public static function cacheKeysForLocale(string $locale): array
    {
        return [
            self::cacheKey($locale, self::AUDIENCE_GUEST),
            self::cacheKey($locale, self::AUDIENCE_MEMBER),
            self::cacheKey($locale, self::AUDIENCE_ADMINISTRATOR),
        ];
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
     * @return array<string, array{label: string, description: string|null, notice: string|null, icon: string, subcategories: array<string, string>}>
     */
    private function databaseTree(string $locale, string $audience): array
    {
        $fallbackLocale = (string) config('app.fallback_locale');
        $locales = array_values(array_unique([$locale, $fallbackLocale]));
        $visibilities = $this->visibleToAudience($audience);
        $roots = ForumCategory::query()
            ->select(['id', 'slug', 'icon', 'position'])
            ->active()
            ->roots()
            ->whereIn('visibility', $visibilities)
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
                    ->whereIn('visibility', $visibilities)
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
                'notice' => $this->categoryNotice($locale, $root->slug),
                'icon' => $root->icon ?? 'messages-square',
                'subcategories' => $subcategories,
            ];
        }

        return $tree;
    }

    /**
     * @param  callable(): array<string, array{label: string, description: string|null, notice: string|null, icon: string, subcategories: array<string, string>}>  $resolver
     * @return array<string, array{label: string, description: string|null, notice: string|null, icon: string, subcategories: array<string, string>}>
     */
    private function rememberTree(string $key, callable $resolver): array
    {
        try {
            $cached = $this->cache->get($key);

            if (is_array($cached)) {
                return $cached;
            }

            $remember = fn (): array => $this->cache->remember(
                $key,
                now()->addSeconds((int) config('taxonomy.tree_cache_seconds')),
                $resolver,
            );
            $store = $this->cache->getStore();

            if (! $store instanceof LockProvider) {
                return $remember();
            }

            return $store->lock($key.':refresh', 10)->block(2, $remember);
        } catch (Throwable) {
            return $resolver();
        }
    }

    private static function cacheKey(string $locale, string $audience): string
    {
        $key = self::CACHE_KEY_PREFIX.$locale;

        return $audience === self::AUDIENCE_GUEST
            ? $key
            : $key.':audience:'.$audience;
    }

    private function audience(): string
    {
        $user = $this->auth->guard()->user();

        if (! $user instanceof User || ! $user->isActive()) {
            return self::AUDIENCE_GUEST;
        }

        return $user->isAdministrator()
            ? self::AUDIENCE_ADMINISTRATOR
            : self::AUDIENCE_MEMBER;
    }

    /** @return list<string> */
    private function visibleToAudience(string $audience): array
    {
        return match ($audience) {
            self::AUDIENCE_ADMINISTRATOR => ['public', 'members', 'restricted', 'hidden'],
            self::AUDIENCE_MEMBER => ['public', 'members'],
            default => ['public'],
        };
    }

    /**
     * @return array<string, array{label: string, description: string|null, notice: string|null, icon: string, subcategories: array<string, string>}>
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
                'notice' => $this->categoryNotice($locale, $category['slug']),
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

    private function categoryNotice(string $locale, string $slug): ?string
    {
        $translationKey = 'forum_categories.notices.'.str_replace('/', '.', $slug);
        $translated = trans($translationKey, locale: $locale);

        return is_string($translated) && $translated !== $translationKey
            ? $translated
            : null;
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

<?php

declare(strict_types=1);

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Services\ForumCategoryCatalog;
use App\Services\ForumCategoryTree;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function beforeOwnershipSubcategories(): array
{
    return [
        'am i ready for an animal',
        'choosing an animal for my lifestyle',
        'choosing between animal species',
        'choosing a dog',
        'choosing a cat',
        'choosing a bird',
        'choosing a rabbit',
        'choosing a rodent',
        'choosing a reptile',
        'choosing an amphibian',
        'choosing aquarium animals',
        'choosing a horse',
        'choosing farm animals',
        'choosing an exotic animal',
        'choosing an invertebrate',
        'first animal for a beginner',
        'animals suitable for experienced owners',
        'household agreement before getting an animal',
        'children and the decision to get an animal',
        'existing animals in the household',
        'allergies and medical considerations',
        'housing restrictions',
        'landlord permission',
        'local legal restrictions',
        'expected lifespan',
        'daily time requirements',
        'exercise requirements',
        'social and emotional requirements',
        'training requirements',
        'grooming requirements',
        'habitat requirements',
        'expected veterinary costs',
        'food and supply costs',
        'insurance planning',
        'emergency financial planning',
        'holiday and travel planning',
        'temporary caregiver planning',
        'long-term contingency planning',
        'adoption versus responsible breeder',
        'evaluating a shelter',
        'evaluating a rescue organization',
        'evaluating a breeder',
        'avoiding irresponsible sellers',
        'avoiding adoption and sales scams',
        'ethical sourcing',
        'meeting an animal before commitment',
        'preparing the home',
        'essential starter supplies',
        'safe transport home',
        'the first twenty-four hours',
        'the first week',
        'the first month',
        'first veterinary appointment',
        'first introductions to people',
        'first introductions to other animals',
        'first-time owner mistakes',
        'returning or rehoming responsibly',
        'owner readiness checklists',
        'total cost of ownership calculators',
        'species and lifestyle comparison tools',
    ];
}

test('the runtime catalogue rejects corrupted source manifests', function (Closure $corrupt) {
    $manifest = json_decode(
        File::get(resource_path('data/forum/categories.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $manifest = $corrupt($manifest);
    $fixture = tempnam(sys_get_temp_dir(), 'forum-category-manifest-');

    expect($fixture)->toBeString();
    File::put($fixture, json_encode($manifest, JSON_THROW_ON_ERROR));

    try {
        expect(fn () => (new ForumCategoryCatalog($fixture))->load())
            ->toThrow(RuntimeException::class);
    } finally {
        File::delete($fixture);
    }
})->with([
    'schema version' => [function (array $manifest): array {
        $manifest['schema_version']++;

        return $manifest;
    }],
    'source checksum' => [function (array $manifest): array {
        $manifest['source_payload_sha256'] = str_repeat('0', 64);

        return $manifest;
    }],
    'aggregate root count' => [function (array $manifest): array {
        $manifest['root_category_count']--;

        return $manifest;
    }],
    'aggregate subcategory count' => [function (array $manifest): array {
        $manifest['subcategory_count']--;

        return $manifest;
    }],
    'root sequence' => [function (array $manifest): array {
        $manifest['categories'][20]['number'] = 22;

        return $manifest;
    }],
    'duplicate root key' => [function (array $manifest): array {
        $manifest['categories'][20]['stable_key'] = $manifest['categories'][19]['stable_key'];

        return $manifest;
    }],
    'duplicate root slug' => [function (array $manifest): array {
        $manifest['categories'][20]['slug'] = $manifest['categories'][19]['slug'];

        return $manifest;
    }],
    'invalid child key' => [function (array $manifest): array {
        $manifest['categories'][20]['subcategories'][0]['stable_key'] = 'forum.foreign.child';

        return $manifest;
    }],
    'invalid child slug' => [function (array $manifest): array {
        $manifest['categories'][20]['subcategories'][0]['slug'] = 'foreign/child';

        return $manifest;
    }],
    'empty child name' => [function (array $manifest): array {
        $manifest['categories'][20]['subcategories'][0]['name'] = '  ';

        return $manifest;
    }],
]);

test('the source manifest retains the complete before ownership category', function () {
    $manifest = app(ForumCategoryCatalog::class)->load();
    $category = collect($manifest['categories'])
        ->firstWhere('stable_key', 'forum.before-ownership');

    expect($category)
        ->not->toBeNull()
        ->and($category['number'])->toBe(21)
        ->and($category['slug'])->toBe('before-ownership')
        ->and($category['name'])->toBe('before getting an animal')
        ->and($category['purpose'])->toBe(
            'help people make a responsible decision before adopting, purchasing, rescuing, fostering, or otherwise accepting responsibility for an animal',
        )
        ->and(array_column($category['subcategories'], 'name'))
        ->toBe(beforeOwnershipSubcategories());
});

test('synchronization persists the exact before ownership hierarchy and translations', function () {
    $this->seed(ForumSystemSeeder::class);

    $definition = collect(app(ForumCategoryCatalog::class)->load()['categories'])
        ->firstWhere('stable_key', 'forum.before-ownership');
    $category = ForumCategory::query()
        ->select(['id', 'stable_key', 'slug', 'position'])
        ->where('stable_key', 'forum.before-ownership')
        ->with([
            'children' => fn ($query) => $query->select([
                'id',
                'parent_id',
                'stable_key',
                'slug',
                'position',
            ]),
            'translations' => fn ($query) => $query->select([
                'id',
                'forum_category_id',
                'locale',
                'name',
                'is_reviewed',
            ]),
        ])
        ->firstOrFail();

    expect($category->stable_key)->toBe($definition['stable_key'])
        ->and($category->slug)->toBe($definition['slug'])
        ->and($category->position)->toBe(21)
        ->and($category->children->map->only([
            'stable_key',
            'slug',
            'position',
        ])->values()->all())->toBe(collect($definition['subcategories'])
        ->values()
        ->map(fn (array $subcategory, int $position): array => [
            'stable_key' => $subcategory['stable_key'],
            'slug' => $subcategory['slug'],
            'position' => $position + 1,
        ])
        ->all())
        ->and($category->translations->pluck('locale')->sort()->values()->all())
        ->toBe(['en', 'lt', 'ru'])
        ->and($category->translations->every(
            fn (ForumCategoryTranslation $translation): bool => $translation->is_reviewed,
        ))->toBeTrue();
});

test('a warm localized category tree performs no database queries', function () {
    $this->seed(ForumSystemSeeder::class);
    Cache::forget(ForumCategoryTree::CACHE_KEY_PREFIX.'en');
    DB::enableQueryLog();

    $tree = app(ForumCategoryTree::class)->forLocale('en');
    $coldQueries = count(DB::getQueryLog());
    DB::flushQueryLog();
    $warmTree = app(ForumCategoryTree::class)->forLocale('en');
    $warmQueries = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($tree)->toHaveCount(44)
        ->and($tree['before-ownership']['subcategories'])->toHaveCount(60)
        ->and($warmTree)->toBe($tree)
        ->and($coldQueries)->toBeLessThanOrEqual(6)
        ->and($warmQueries)->toBe(0);
});

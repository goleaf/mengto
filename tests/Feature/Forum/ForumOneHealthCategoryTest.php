<?php

declare(strict_types=1);

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Services\ForumCategoryCatalog;
use App\Services\ForumCategoryTree;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Support\Facades\Cache;

function oneHealthSubcategories(): array
{
    return [
        'general one-health discussions',
        'zoonotic diseases',
        'reverse zoonoses',
        'household hygiene',
        'handwashing and sanitation',
        'bites',
        'scratches',
        'allergy management',
        'asthma and animals',
        'pregnancy and animal contact',
        'newborn children and animals',
        'immunocompromised people',
        'elderly family members',
        'young children',
        'raw food hygiene',
        'food-borne risks',
        'parasite transmission',
        'flea and tick risks to people',
        'ringworm and fungal risks',
        'household quarantine',
        'introducing a recently rescued animal',
        'cleaning after infectious illness',
        'safe waste disposal',
        'litter box hygiene',
        'aquarium hygiene',
        'reptile and amphibian hygiene',
        'farm animal contact',
        'wildlife contact',
        'occupational animal exposure',
        'groomer safety',
        'shelter worker safety',
        'veterinary worker safety',
        'trainer and walker safety',
        'public-health alerts',
        'outbreak discussions',
        'coordinating with veterinarians and doctors',
        'mental health benefits of animals',
        'emotional stress related to animal care',
        'misinformation and stigma',
        'community health education',
        'safe school and educational visits',
        'country-specific public-health guidance',
    ];
}

test('the source manifest retains the complete one health category', function () {
    $manifest = app(ForumCategoryCatalog::class)->load();
    $category = collect($manifest['categories'])
        ->firstWhere('stable_key', 'forum.one-health-human-safety');

    expect($category)
        ->not->toBeNull()
        ->and($category['number'])->toBe(24)
        ->and($category['slug'])->toBe('one-health-human-safety')
        ->and($category['name'])->toBe('one health, zoonoses, and human safety')
        ->and($category['purpose'])->toBe(
            'cover the intersection between animal health, human health, household safety, and public health without replacing medical or veterinary professionals',
        )
        ->and(array_column($category['subcategories'], 'name'))
        ->toBe(oneHealthSubcategories());
});

test('synchronization persists the exact one health hierarchy and locale rows', function () {
    $this->seed(ForumSystemSeeder::class);

    $definition = collect(app(ForumCategoryCatalog::class)->load()['categories'])
        ->firstWhere('stable_key', 'forum.one-health-human-safety');
    $category = ForumCategory::query()
        ->select(['id', 'stable_key', 'slug', 'position'])
        ->where('stable_key', 'forum.one-health-human-safety')
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
        ->and($category->position)->toBe(24)
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

test('the one health directory displays its localized professional boundary only in scope', function () {
    $this->seed(ForumSystemSeeder::class);

    foreach (['en', 'lt', 'ru'] as $locale) {
        app()->setLocale($locale);
        foreach (ForumCategoryTree::cacheKeysForLocale($locale) as $key) {
            Cache::forget($key);
        }

        $tree = app(ForumCategoryTree::class)->forLocale($locale);

        expect($tree['one-health-human-safety']['notice'])
            ->toBe(trans('forum_categories.notices.one-health-human-safety', locale: $locale));
    }

    app()->setLocale('en');
    foreach (ForumCategoryTree::cacheKeysForLocale('en') as $key) {
        Cache::forget($key);
    }

    $this->get(route('forum.index', ['category' => 'one-health-human-safety']))
        ->assertOk()
        ->assertSee('data-section="one-health-professional-boundary"', escape: false)
        ->assertSee(__('forum_categories.notice_title'))
        ->assertSee(__('forum_categories.notices.one-health-human-safety'));

    $this->get(route('forum.index', ['category' => 'wildlife-coexistence']))
        ->assertOk()
        ->assertDontSee('data-section="one-health-professional-boundary"', escape: false);
});

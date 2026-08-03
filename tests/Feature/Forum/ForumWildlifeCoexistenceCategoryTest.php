<?php

declare(strict_types=1);

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Services\ForumCategoryCatalog;
use Database\Seeders\ForumSystemSeeder;

function wildlifeCoexistenceSubcategories(): array
{
    return [
        'general wildlife questions',
        'when to help a wild animal',
        'when not to intervene',
        'injured wildlife',
        'orphaned wildlife',
        'apparently abandoned young animals',
        'licensed wildlife rehabilitators',
        'wildlife rescue organizations',
        'emergency wildlife transport',
        'urban wildlife',
        'rural wildlife',
        'garden wildlife',
        'wild birds',
        'birds of prey',
        'bats',
        'hedgehogs and similar small mammals',
        'foxes and similar urban predators',
        'deer and large mammals',
        'marine mammals',
        'sea turtles',
        'wild reptiles',
        'wild amphibians',
        'wild fish',
        'insects and pollinators',
        'spiders and other arachnids',
        'injured animals near roads',
        'window collisions',
        'fishing line and net entanglement',
        'plastic and waste injuries',
        'wildlife in buildings',
        'wildlife in gardens',
        'coexistence with predators',
        'protecting poultry and livestock',
        'humane conflict prevention',
        'wildlife feeding discussions',
        'bird feeders and hygiene',
        'wildlife water sources',
        'wildlife-friendly gardens',
        'nesting boxes',
        'pollinator habitats',
        'invasive species',
        'native species',
        'protected species',
        'wildlife photography ethics',
        'wildlife tourism ethics',
        'illegal wildlife trade',
        'suspected poaching',
        'reporting wildlife crime',
        'roadkill reporting',
        'wildlife observation records',
        'release after rehabilitation',
        'wildlife disease alerts',
        'country-specific wildlife laws',
        'coexistence success stories',
        'citizen observation projects',
    ];
}

test('the source manifest retains the complete wildlife coexistence category', function () {
    $manifest = app(ForumCategoryCatalog::class)->load();
    $category = collect($manifest['categories'])
        ->firstWhere('stable_key', 'forum.wildlife-coexistence');

    expect($category)
        ->not->toBeNull()
        ->and($category['number'])->toBe(23)
        ->and($category['slug'])->toBe('wildlife-coexistence')
        ->and($category['name'])->toBe('wildlife and human-animal coexistence')
        ->and($category['purpose'])->toBe(
            'help users interact safely, legally, and ethically with wild animals without treating all wildlife as pets',
        )
        ->and(array_column($category['subcategories'], 'name'))
        ->toBe(wildlifeCoexistenceSubcategories());
});

test('synchronization persists the exact wildlife coexistence hierarchy and locale rows', function () {
    $this->seed(ForumSystemSeeder::class);

    $definition = collect(app(ForumCategoryCatalog::class)->load()['categories'])
        ->firstWhere('stable_key', 'forum.wildlife-coexistence');
    $category = ForumCategory::query()
        ->select(['id', 'stable_key', 'slug', 'position'])
        ->where('stable_key', 'forum.wildlife-coexistence')
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
        ->and($category->position)->toBe(23)
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

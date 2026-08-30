<?php

declare(strict_types=1);

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Services\ForumCategoryCatalog;
use App\Services\ForumCategoryTree;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Support\Facades\Cache;

function specialNeedsSubcategories(): array
{
    return [
        'general special-needs care',
        'mobility limitations',
        'paralysis',
        'amputee animals',
        'wheelchair users',
        'blind animals',
        'partially sighted animals',
        'deaf animals',
        'neurological disabilities',
        'developmental disabilities',
        'vestibular conditions',
        'incontinence management',
        'chronic pain support',
        'seizure-related care',
        'feeding disabilities',
        'swallowing difficulties',
        'breathing-related limitations',
        'skin and wound management',
        'long-term medication routines',
        'pressure sore prevention',
        'rehabilitation',
        'physiotherapy',
        'hydrotherapy',
        'massage and safe physical support',
        'mobility aids',
        'wheelchairs',
        'prosthetics',
        'braces and supports',
        'lifting harnesses',
        'ramps and stairs',
        'accessible beds',
        'accessible litter and toilet areas',
        'home adaptations',
        'garden adaptations',
        'vehicle adaptations',
        'accessible travel',
        'accessible public places',
        'enrichment for limited-mobility animals',
        'training blind animals',
        'training deaf animals',
        'communication systems',
        'quality-of-life assessment',
        'special-needs adoption',
        'special-needs fostering',
        'financial support',
        'caregiver fatigue',
        'caregiver routines',
        'disabled owners caring for animals',
        'assistance for elderly owners',
        'success stories',
        'memorial and end-of-life support',
        'verified accessibility product reviews',
        'accessibility service directories',
        'specialist professional directories',
    ];
}

test('the source manifest retains the complete special needs category', function () {
    $manifest = app(ForumCategoryCatalog::class)->load();
    $category = collect($manifest['categories'])
        ->firstWhere('stable_key', 'forum.special-needs-accessibility');

    expect($category)
        ->not->toBeNull()
        ->and($category['number'])->toBe(22)
        ->and($category['slug'])->toBe('special-needs-accessibility')
        ->and($category['name'])->toBe('special needs, disability, and accessibility')
        ->and($category['purpose'])->toBe(
            'support animals with disabilities, chronic limitations, special care requirements, and owners who need accessible care solutions',
        )
        ->and(array_column($category['subcategories'], 'name'))
        ->toBe(specialNeedsSubcategories());
});

test('synchronization persists the exact special needs hierarchy and locale rows', function () {
    $this->seed(ForumSystemSeeder::class);

    $definition = collect(app(ForumCategoryCatalog::class)->load()['categories'])
        ->firstWhere('stable_key', 'forum.special-needs-accessibility');
    $category = ForumCategory::query()
        ->select(['id', 'stable_key', 'slug', 'position'])
        ->where('stable_key', 'forum.special-needs-accessibility')
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
        ->and($category->position)->toBe(22)
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

test('the locale tree never renders an unreviewed category translation', function () {
    $this->seed(ForumSystemSeeder::class);

    $child = ForumCategory::query()
        ->where('stable_key', 'forum.special-needs-accessibility.mobility.limitations')
        ->firstOrFail();
    $translation = ForumCategoryTranslation::query()
        ->whereBelongsTo($child, 'category')
        ->where('locale', 'lt')
        ->firstOrFail();
    $translation->update([
        'name' => 'NEPATIKRINTAS VERTIMAS',
        'is_reviewed' => false,
    ]);
    foreach (ForumCategoryTree::cacheKeysForLocale('lt') as $key) {
        Cache::forget($key);
    }

    $unreviewedTree = app(ForumCategoryTree::class)->forLocale('lt');

    expect($unreviewedTree['special-needs-accessibility']['subcategories'][$child->slug])
        ->toBe('Mobility limitations')
        ->not->toBe('NEPATIKRINTAS VERTIMAS');

    $translation->update([
        'name' => 'Judėjimo apribojimai',
        'is_reviewed' => true,
    ]);
    foreach (ForumCategoryTree::cacheKeysForLocale('lt') as $key) {
        Cache::forget($key);
    }

    $reviewedTree = app(ForumCategoryTree::class)->forLocale('lt');

    expect($reviewedTree['special-needs-accessibility']['subcategories'][$child->slug])
        ->toBe('Judėjimo apribojimai');
});

test('root options also require a reviewed target locale translation', function () {
    $this->seed(ForumSystemSeeder::class);

    $category = ForumCategory::query()
        ->where('stable_key', 'forum.special-needs-accessibility')
        ->firstOrFail();
    ForumCategoryTranslation::query()
        ->whereBelongsTo($category, 'category')
        ->where('locale', 'lt')
        ->update([
            'name' => 'NEPATIKRINTAS ŠAKNIES VERTIMAS',
            'is_reviewed' => false,
        ]);

    $options = app(ForumCategoryTree::class)->rootOptions('lt');

    expect($options[$category->id])
        ->toBe(trans(
            'forum_categories.roots.special-needs-accessibility',
            locale: 'en',
        ))
        ->not->toBe('NEPATIKRINTAS ŠAKNIES VERTIMAS');
});

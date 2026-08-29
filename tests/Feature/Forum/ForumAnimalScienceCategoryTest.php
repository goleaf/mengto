<?php

declare(strict_types=1);

use App\Actions\SynchronizeForumCategories;
use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Services\ForumCategoryCatalog;
use Database\Seeders\ForumSystemSeeder;

function animalScienceEvidenceSubcategories(): array
{
    return [
        'general animal science',
        'anatomy',
        'physiology',
        'genetics',
        'epigenetics',
        'evolution',
        'domestication',
        'cognition',
        'emotions',
        'learning science',
        'behavior research',
        'welfare science',
        'veterinary research',
        'nutrition research',
        'reproduction research',
        'aging research',
        'pain research',
        'rehabilitation research',
        'environmental enrichment research',
        'comparative medicine',
        'conservation science',
        'taxonomy and systematics',
        'ecology',
        'population science',
        'epidemiology',
        'research methods',
        'study design',
        'statistics',
        'interpreting risk',
        'correlation and causation',
        'systematic reviews',
        'meta-analyses',
        'clinical trials',
        'observational studies',
        'case reports',
        'laboratory studies',
        'preprints',
        'peer review',
        'replication',
        'conflicts of interest',
        'funding disclosures',
        'product claims',
        'advertising claim analysis',
        'source verification',
        'evidence grading',
        'myth checking',
        'outdated recommendations',
        'research summaries',
        'plain-language science explanations',
        'research requests',
        'citizen science',
        'ethical research discussions',
        'open data',
        'research corrections and retractions',
    ];
}

test('the source manifest retains the complete animal science category', function () {
    $category = collect(app(ForumCategoryCatalog::class)->load()['categories'])
        ->firstWhere('stable_key', 'forum.animal-science-evidence');

    expect($category)
        ->not->toBeNull()
        ->and($category['number'])->toBe(25)
        ->and($category['stable_key'])->toBe('forum.animal-science-evidence')
        ->and($category['slug'])->toBe('animal-science-evidence')
        ->and($category['name'])->toBe('animal science, research, and evidence')
        ->and($category['purpose'])->toBe(
            'create an evidence-oriented space for understanding animal science, evaluating claims, and discussing research responsibly',
        )
        ->and(array_column($category['subcategories'], 'name'))
        ->toBe(animalScienceEvidenceSubcategories())
        ->and(array_column($category['subcategories'], 'stable_key'))
        ->toBe(collect($category['subcategories'])
            ->pluck('slug')
            ->map(fn (string $slug): string => 'forum.animal-science-evidence.'.str_replace(
                '-',
                '.',
                explode('/', $slug, 2)[1],
            ))
            ->all());
});

test('synchronization persists the exact animal science hierarchy and locale trust state', function () {
    $this->seed(ForumSystemSeeder::class);

    $definition = collect(app(ForumCategoryCatalog::class)->load()['categories'])
        ->firstWhere('stable_key', 'forum.animal-science-evidence');
    $category = ForumCategory::query()
        ->select([
            'id',
            'parent_id',
            'stable_key',
            'slug',
            'position',
            'is_system_managed',
            'is_active',
        ])
        ->where('stable_key', 'forum.animal-science-evidence')
        ->with([
            'children' => fn ($query) => $query->select([
                'id',
                'parent_id',
                'stable_key',
                'slug',
                'position',
                'is_system_managed',
                'is_active',
            ])->with([
                'translations' => fn ($translations) => $translations->select([
                    'id',
                    'forum_category_id',
                    'locale',
                    'name',
                    'description',
                    'is_reviewed',
                ]),
            ]),
            'translations' => fn ($query) => $query->select([
                'id',
                'forum_category_id',
                'locale',
                'name',
                'description',
                'is_reviewed',
            ]),
        ])
        ->firstOrFail();

    $expectedChildren = collect($definition['subcategories'])
        ->values()
        ->map(fn (array $subcategory, int $position): array => [
            'parent_id' => $category->id,
            'stable_key' => $subcategory['stable_key'],
            'slug' => $subcategory['slug'],
            'position' => $position + 1,
            'is_system_managed' => true,
            'is_active' => true,
        ])
        ->all();
    $expectedRootTranslations = [
        [
            'locale' => 'en',
            'name' => 'Animal science, research, and evidence',
            'description' => 'Evidence-oriented animal science, responsible claim evaluation, and research discussion.',
            'is_reviewed' => true,
        ],
        [
            'locale' => 'lt',
            'name' => 'Gyvūnų mokslas, tyrimai ir įrodymai',
            'description' => 'Į įrodymus orientuotas gyvūnų mokslas, atsakingas teiginių vertinimas ir tyrimų aptarimas.',
            'is_reviewed' => true,
        ],
        [
            'locale' => 'ru',
            'name' => 'Наука о животных, исследования и доказательства',
            'description' => 'Ориентированная на доказательства наука о животных, ответственная оценка утверждений и обсуждение исследований.',
            'is_reviewed' => true,
        ],
    ];

    expect($category->parent_id)->toBeNull()
        ->and($category->stable_key)->toBe($definition['stable_key'])
        ->and($category->slug)->toBe($definition['slug'])
        ->and($category->position)->toBe(25)
        ->and($category->is_system_managed)->toBeTrue()
        ->and($category->is_active)->toBeTrue()
        ->and($category->children->map->only(array_keys($expectedChildren[0]))->values()->all())
        ->toBe($expectedChildren)
        ->and($category->translations
            ->sortBy('locale')
            ->map->only(['locale', 'name', 'description', 'is_reviewed'])
            ->values()
            ->all())
        ->toBe($expectedRootTranslations)
        ->and($category->children->every(function (ForumCategory $child, int $index) use ($definition): bool {
            $translations = $child->translations->keyBy('locale');
            $sourceName = $definition['subcategories'][$index]['name'];

            return $translations->count() === 3
                && $translations->keys()->sort()->values()->all() === ['en', 'lt', 'ru']
                && $translations->every(
                    fn (ForumCategoryTranslation $translation): bool => $translation->name === $sourceName
                        && $translation->description === null
                        && $translation->is_reviewed === ($translation->locale === 'en'),
                );
        }))->toBeTrue();
});

test('synchronization preserves administrator categories outside the source hierarchy', function () {
    $this->seed(ForumSystemSeeder::class);

    $customCategory = ForumCategory::factory()->create([
        'stable_key' => 'forum.community-created-research-circle',
        'slug' => 'community-created-research-circle',
        'position' => 901,
        'is_system_managed' => false,
    ]);

    app(SynchronizeForumCategories::class)->handle();

    $customCategory->refresh();

    expect($customCategory->stable_key)->toBe('forum.community-created-research-circle')
        ->and($customCategory->slug)->toBe('community-created-research-circle')
        ->and($customCategory->position)->toBe(901)
        ->and($customCategory->is_system_managed)->toBeFalse()
        ->and($customCategory->is_active)->toBeTrue();
});

test('the public directory renders the complete selected category with stable child links', function () {
    $this->seed(ForumSystemSeeder::class);
    $this->authenticatedUser->update(['locale' => 'lt']);

    $response = $this->get(route('forum.index', ['category' => 'animal-science-evidence']))
        ->assertOk()
        ->assertSee('Gyvūnų mokslas, tyrimai ir įrodymai')
        ->assertSee('Į įrodymus orientuotas gyvūnų mokslas, atsakingas teiginių vertinimas ir tyrimų aptarimas.')
        ->assertDontSee('forum_categories.', escape: false);
    $xpath = responseXPath($response);
    $childNodes = $xpath->query(
        '//*[@data-subcategory-list="animal-science-evidence"]/*[@data-category-child]',
    );
    $childSlugs = [];

    foreach ($childNodes as $childNode) {
        $childSlugs[] = $childNode->attributes?->getNamedItem('data-category-child')?->nodeValue;
        parse_str((string) parse_url(
            (string) $childNode->attributes?->getNamedItem('href')?->nodeValue,
            PHP_URL_QUERY,
        ), $query);

        expect($query['category'] ?? null)->toBe(end($childSlugs));
    }

    $definition = collect(app(ForumCategoryCatalog::class)->load()['categories'])
        ->firstWhere('stable_key', 'forum.animal-science-evidence');

    expect($xpath->query(
        '//*[@data-category-root="animal-science-evidence" and @data-active-root="true" and @aria-current="page"]',
    )->length)->toBe(1)
        ->and($xpath->query('//h3[@id="active-forum-category-heading"]')->length)->toBe(1)
        ->and($childNodes->length)->toBe(54)
        ->and($childSlugs)->toBe(array_column($definition['subcategories'], 'slug'))
        ->and($childSlugs)->toContain(
            'animal-science-evidence/taxonomy-and-systematics',
            'animal-science-evidence/case-reports',
        );

    $selectedChild = 'animal-science-evidence/source-verification';
    $selectedResponse = $this->get(route('forum.index', ['category' => $selectedChild]))
        ->assertOk();
    $selectedXPath = responseXPath($selectedResponse);

    expect($selectedXPath->query(
        '//*[@data-subcategory-list="animal-science-evidence"]/*[@aria-current="page"]',
    )->length)->toBe(1)
        ->and($selectedXPath->query(
            '//*[@data-category-child="'.$selectedChild.'" and @aria-current="page"]',
        )->length)->toBe(1);
});

test('invalid animal science child keys fail server-side validation', function () {
    $this->from(route('forum.index'))
        ->get(route('forum.index', [
            'category' => 'animal-science-evidence/not-a-source-category',
        ]))
        ->assertRedirect(route('forum.index'))
        ->assertSessionHasErrors('category');
});

test('the animal science navigator uses the canonical fixed heading scale', function () {
    $styles = file_get_contents(resource_path('scss/_forum.scss'));
    preg_match(
        '/&__header h2,\s*&__selection-header h3\s*\{(?<rules>[^}]*)}/s',
        (string) $styles,
        $matches,
    );

    expect($matches['rules'] ?? null)
        ->toBeString()
        ->toContain('font-size: 1.125rem;')
        ->not->toContain('clamp(')
        ->not->toContain('Georgia')
        ->not->toContain('Times New Roman');
});

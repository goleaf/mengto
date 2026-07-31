<?php

declare(strict_types=1);

use App\Enums\ForumTopicType;
use App\Models\ForumCategory;
use App\Models\ForumCategoryAlias;
use App\Models\ForumCategoryRedirect;
use App\Models\ForumCategoryTranslation;
use App\Models\ForumTopic;
use App\Models\ForumTopicType as ForumTopicTypeModel;
use Database\Seeders\ForumSystemSeeder;

test('forum system seed creates the complete deterministic category hierarchy', function () {
    $this->seed(ForumSystemSeeder::class);

    $manifest = json_decode(
        file_get_contents(resource_path('data/forum/categories.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect(ForumCategory::query()->roots()->count())->toBe(44)
        ->and(ForumCategory::query()->whereNotNull('parent_id')->count())->toBe(1637)
        ->and(ForumCategory::query()->count())->toBe(1681)
        ->and(ForumCategory::query()->distinct()->count('stable_key'))->toBe(1681)
        ->and(ForumCategory::query()->where('is_system_managed', true)->count())->toBe(1681)
        ->and(ForumCategoryTranslation::query()->count())->toBe(1681 * 3)
        ->and(ForumCategoryAlias::query()->count())->toBe(7)
        ->and(ForumCategoryRedirect::query()->count())->toBe(7)
        ->and(ForumTopicTypeModel::query()->count())->toBe(count(ForumTopicType::cases()))
        ->and($manifest['root_category_count'])->toBe(44)
        ->and($manifest['subcategory_count'])->toBe(1637);

    foreach ($manifest['categories'] as $definition) {
        expect(ForumCategory::query()
            ->where('stable_key', $definition['stable_key'])
            ->whereNull('parent_id')
            ->exists())->toBeTrue();
    }
});

test('legacy topic taxonomy backfill is restartable and preserves source values', function () {
    $legacy = ForumTopic::factory()->create([
        'category' => 'travel',
        'type' => ForumTopicType::Guide,
        'forum_category_id' => null,
        'forum_topic_type_id' => null,
    ]);
    $unknown = ForumTopic::factory()->create([
        'category' => 'legacy-private-taxonomy',
        'forum_category_id' => null,
    ]);

    $this->seed(ForumSystemSeeder::class);
    $firstCategoryId = $legacy->fresh()->forum_category_id;
    $firstTopicTypeId = $legacy->fresh()->forum_topic_type_id;
    $this->seed(ForumSystemSeeder::class);
    $legacy->refresh();

    expect($legacy->category)->toBe('travel')
        ->and($legacy->forum_category_id)->toBe($firstCategoryId)
        ->and($legacy->forum_topic_type_id)->toBe($firstTopicTypeId)
        ->and($legacy->normalizedCategory->slug)->toBe('travel-documents')
        ->and($unknown->fresh()->forum_category_id)->toBeNull()
        ->and(ForumCategoryAlias::query()->count())->toBe(7)
        ->and(ForumCategoryRedirect::query()->count())->toBe(7);
});

test('category synchronization preserves ids user categories topics and archives obsolete system categories', function () {
    $this->seed(ForumSystemSeeder::class);

    $health = ForumCategory::query()->where('stable_key', 'forum.health')->firstOrFail();
    $healthId = $health->id;
    $custom = ForumCategory::factory()->create([
        'stable_key' => 'forum.community.custom-vilnius',
        'slug' => 'community/custom-vilnius',
        'is_system_managed' => false,
    ]);
    $obsolete = ForumCategory::factory()->systemManaged()->create([
        'stable_key' => 'forum.legacy-obsolete',
        'slug' => 'legacy-obsolete',
    ]);
    $topic = ForumTopic::factory()->create([
        'category' => 'legacy-obsolete',
        'forum_category_id' => $obsolete->id,
    ]);

    $this->seed(ForumSystemSeeder::class);

    expect(ForumCategory::query()->where('stable_key', 'forum.health')->value('id'))
        ->toBe($healthId)
        ->and(ForumCategory::query()->whereKey($custom->id)->exists())->toBeTrue()
        ->and($obsolete->refresh()->is_active)->toBeFalse()
        ->and($obsolete->archived_at)->not->toBeNull()
        ->and($topic->refresh()->forum_category_id)->toBe($obsolete->id)
        ->and(ForumTopic::query()->whereKey($topic->id)->exists())->toBeTrue();
});

test('root category translations are reviewed for every supported locale', function () {
    $this->seed(ForumSystemSeeder::class);

    $roots = ForumCategory::query()
        ->roots()
        ->with(['translations' => fn ($query) => $query->select([
            'id',
            'forum_category_id',
            'locale',
            'name',
            'is_reviewed',
        ])])
        ->get();

    expect($roots)->toHaveCount(44);

    foreach ($roots as $root) {
        expect($root->translations->pluck('locale')->sort()->values()->all())
            ->toBe(['en', 'lt', 'ru'])
            ->and($root->translations->every(
                fn (ForumCategoryTranslation $translation): bool => $translation->is_reviewed
                    && trim($translation->name) !== '',
            ))->toBeTrue();
    }
});

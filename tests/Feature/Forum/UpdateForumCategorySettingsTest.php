<?php

declare(strict_types=1);

use App\Actions\UpdateForumCategorySettings;
use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Models\User;
use App\Services\ForumCategoryTree;
use Database\Seeders\ForumSystemSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

test('forum category settings update atomically and invalidate category caches', function (): void {
    $this->seed(ForumSystemSeeder::class);
    $administrator = User::factory()->administrator()->create();
    $category = ForumCategory::query()->where('stable_key', 'forum.health')->firstOrFail();
    Cache::put(ForumCategoryTree::CACHE_KEY_PREFIX.'en', ['stale' => true], 600);

    $updated = app(UpdateForumCategorySettings::class)->handle(
        actor: $administrator,
        categoryId: $category->id,
        locale: 'en',
        name: 'Animal health and veterinary preparation',
        visibility: 'members',
        moderationLevel: 'high-risk',
    );

    expect($updated)
        ->visibility->toBe('members')
        ->moderation_level->toBe('high-risk')
        ->and(ForumCategoryTranslation::query()
            ->where('forum_category_id', $category->id)
            ->where('locale', 'en')
            ->value('name'))
        ->toBe('Animal health and veterinary preparation')
        ->and(Cache::has(ForumCategoryTree::CACHE_KEY_PREFIX.'en'))->toBeFalse();
});

test('forum category settings reject an unauthorized actor without changes', function (): void {
    $this->seed(ForumSystemSeeder::class);
    $member = User::factory()->create();
    $category = ForumCategory::query()->where('stable_key', 'forum.health')->firstOrFail();
    $originalVisibility = $category->visibility;
    $originalModerationLevel = $category->moderation_level;

    expect(fn () => app(UpdateForumCategorySettings::class)->handle(
        actor: $member,
        categoryId: $category->id,
        locale: 'en',
        name: 'Unauthorized category name',
        visibility: 'hidden',
        moderationLevel: 'emergency',
    ))->toThrow(AuthorizationException::class)
        ->and($category->refresh()->visibility)->toBe($originalVisibility)
        ->and($category->moderation_level)->toBe($originalModerationLevel);
});

test('forum category settings roll back the category when translation persistence fails', function (): void {
    $this->seed(ForumSystemSeeder::class);
    $administrator = User::factory()->administrator()->create();
    $category = ForumCategory::query()->where('stable_key', 'forum.health')->firstOrFail();
    $originalVisibility = $category->visibility;
    $originalModerationLevel = $category->moderation_level;
    $failTranslationWrite = false;
    Cache::put(ForumCategoryTree::CACHE_KEY_PREFIX.'en', ['stale' => true], 600);
    DB::listen(static function (QueryExecuted $query) use (&$failTranslationWrite): void {
        if ($failTranslationWrite && str_contains($query->sql, 'forum_category_translations')) {
            throw new RuntimeException('Injected translation persistence failure.');
        }
    });
    $failTranslationWrite = true;

    try {
        expect(fn () => app(UpdateForumCategorySettings::class)->handle(
            actor: $administrator,
            categoryId: $category->id,
            locale: 'en',
            name: 'This translation must roll back',
            visibility: 'hidden',
            moderationLevel: 'emergency',
        ))->toThrow(RuntimeException::class, 'Injected translation persistence failure.');
    } finally {
        $failTranslationWrite = false;
    }

    expect($category->refresh())
        ->visibility->toBe($originalVisibility)
        ->moderation_level->toBe($originalModerationLevel)
        ->and(Cache::has(ForumCategoryTree::CACHE_KEY_PREFIX.'en'))->toBeTrue();
});

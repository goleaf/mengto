<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumCategory;
use App\Models\ForumCategoryTranslation;
use App\Models\User;
use App\Services\ForumCategoryTree;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\DB;

final readonly class UpdateForumCategorySettings
{
    public function __construct(
        private Gate $gate,
        private CacheRepository $cache,
    ) {}

    public function handle(
        User $actor,
        int $categoryId,
        string $locale,
        string $name,
        string $visibility,
        string $moderationLevel,
    ): ForumCategory {
        $category = DB::transaction(function () use (
            $actor,
            $categoryId,
            $locale,
            $name,
            $visibility,
            $moderationLevel,
        ): ForumCategory {
            $category = ForumCategory::query()
                ->lockForUpdate()
                ->findOrFail($categoryId);
            $this->gate->forUser($actor)->authorize('manage', $category);
            $category->forceFill([
                'visibility' => $visibility,
                'moderation_level' => $moderationLevel,
            ])->save();
            ForumCategoryTranslation::query()->updateOrCreate(
                [
                    'forum_category_id' => $category->id,
                    'locale' => $locale,
                ],
                [
                    'name' => $name,
                    'is_reviewed' => true,
                ],
            );

            return $category->refresh();
        }, 3);

        foreach (config('platform.supported_locales', ['en']) as $supportedLocale) {
            foreach (ForumCategoryTree::cacheKeysForLocale($supportedLocale) as $key) {
                $this->cache->forget($key);
            }
        }

        return $category;
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ForumTaxonomyBackfillResult;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\ForumTopicType;
use App\Services\ForumTaxonomy;
use Illuminate\Support\Facades\DB;

final class BackfillForumTopicTaxonomy
{
    public function handle(): ForumTaxonomyBackfillResult
    {
        return DB::transaction(function (): ForumTaxonomyBackfillResult {
            $categoryIds = ForumCategory::query()
                ->active()
                ->roots()
                ->pluck('id', 'slug');
            $categoryAssignments = 0;

            foreach (ForumTaxonomy::LEGACY_CATEGORY_SLUGS as $legacy => $canonical) {
                $categoryId = $categoryIds->get($canonical);

                if ($categoryId === null) {
                    continue;
                }

                $categoryAssignments += ForumTopic::query()
                    ->whereNull('forum_category_id')
                    ->where('category', $legacy)
                    ->update(['forum_category_id' => $categoryId]);
            }

            foreach ($categoryIds as $canonical => $categoryId) {
                $categoryAssignments += ForumTopic::query()
                    ->whereNull('forum_category_id')
                    ->where('category', $canonical)
                    ->update(['forum_category_id' => $categoryId]);
            }

            $topicTypeIds = ForumTopicType::query()->pluck('id', 'stable_key');
            $topicTypeAssignments = 0;

            foreach ($topicTypeIds as $stableKey => $topicTypeId) {
                $topicTypeAssignments += ForumTopic::query()
                    ->whereNull('forum_topic_type_id')
                    ->where('type', $stableKey)
                    ->update(['forum_topic_type_id' => $topicTypeId]);
            }

            return new ForumTaxonomyBackfillResult(
                categoryAssignments: $categoryAssignments,
                topicTypeAssignments: $topicTypeAssignments,
                unmappedCategories: ForumTopic::query()
                    ->whereNull('forum_category_id')
                    ->distinct()
                    ->count('category'),
                unmappedTopicTypes: ForumTopic::query()
                    ->whereNull('forum_topic_type_id')
                    ->distinct()
                    ->count('type'),
            );
        }, 3);
    }
}

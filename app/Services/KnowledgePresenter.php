<?php

namespace App\Services;

use App\Models\KnowledgeArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class KnowledgePresenter
{
    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumTaxonomy $taxonomy,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function library(array $filters): array
    {
        $query = trim((string) ($filters['q'] ?? ''));
        $category = (string) ($filters['category'] ?? 'all');
        $type = (string) ($filters['type'] ?? 'all');
        $articles = KnowledgeArticle::query()
            ->forLibrary()
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $term = '%'.$query.'%';
                $builder->where(function (Builder $search) use ($term): void {
                    $search
                        ->where('title', 'like', $term)
                        ->orWhere('summary', 'like', $term)
                        ->orWhere('body', 'like', $term)
                        ->orWhere('tags', 'like', $term);
                });
            })
            ->when($category !== 'all', fn (Builder $builder): Builder => $builder->where('category', $category))
            ->when($type !== 'all', fn (Builder $builder): Builder => $builder->where('type', $type))
            ->orderByDesc('last_reviewed_at')
            ->orderByDesc('id')
            ->simplePaginate(9)
            ->withQueryString();

        $articles->through(fn (KnowledgeArticle $article): array => $this->card($article));

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => 'Knowledge base | PawCircle',
            'active_section' => 'forum',
            'articles' => $articles,
            'filters' => ['q' => $query, 'category' => $category, 'type' => $type],
            'categories' => $this->taxonomy->categoryOptions(),
            'types' => [
                'all' => 'All formats',
                'guide' => 'Guides',
                'checklist' => 'Checklists',
                'faq' => 'FAQ',
                'comparison' => 'Comparisons',
                'local-guide' => 'Local guides',
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function article(KnowledgeArticle $article): array
    {
        $article->load([
            'versions' => fn ($versions) => $versions
                ->select(['id', 'article_id', 'version_number', 'edited_by', 'change_summary', 'created_at'])
                ->latest('version_number')
                ->limit(8),
            'sourceTopic' => fn ($topic) => $topic->forDirectory(),
        ]);

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => $article->title.' | PawCircle',
            'active_section' => 'forum',
            'article' => [
                ...$this->card($article),
                'body' => $article->body,
                'audience' => $article->audience,
                'language' => Str::upper($article->language),
                'tags' => $article->tags ?? [],
                'sources' => collect($article->sources ?? [])
                    ->map(fn (string $source): array => [
                        'url' => $source,
                        'label' => parse_url($source, PHP_URL_HOST) ?: $source,
                    ])
                    ->all(),
                'contributors' => collect($article->contributors ?? [])
                    ->map(fn (array|string $contributor): array => is_array($contributor)
                        ? [
                            'name' => (string) ($contributor['name'] ?? 'Editorial contributor'),
                            'role' => (string) ($contributor['role'] ?? 'Contributor'),
                        ]
                        : ['name' => $contributor, 'role' => 'Contributor'])
                    ->all(),
                'next_review_label' => $article->next_review_at?->format('M j, Y'),
                'source_topic' => $article->sourceTopic ? [
                    'slug' => $article->sourceTopic->slug,
                    'title' => $article->sourceTopic->title,
                ] : null,
            ],
            'versions' => $article->versions,
            'related' => KnowledgeArticle::query()
                ->forLibrary()
                ->where('category', $article->category)
                ->whereKeyNot($article->id)
                ->latest('last_reviewed_at')
                ->limit(3)
                ->get()
                ->map(fn (KnowledgeArticle $related): array => $this->card($related))
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function card(KnowledgeArticle $article): array
    {
        return [
            'id' => $article->id,
            'slug' => $article->slug,
            'title' => $article->title,
            'summary' => $article->summary,
            'category' => $article->category,
            'category_label' => $this->taxonomy->categoryOptions()[$article->category] ?? Str::headline($article->category),
            'type_label' => Str::headline($article->type),
            'difficulty_label' => Str::headline($article->difficulty),
            'status_label' => $article->status->label(),
            'is_outdated' => $article->status->value === 'outdated',
            'reviewed_label' => $article->last_reviewed_at?->format('M j, Y') ?? 'Editorial review pending',
            'version' => $article->current_version,
        ];
    }
}

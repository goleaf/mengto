<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleCollaborator;
use App\Models\KnowledgeVersion;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;

class KnowledgePresenter
{
    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumTaxonomy $taxonomy,
        private readonly LocaleFormatter $formatter,
        private readonly Gate $gate,
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
            'page_title' => __('messages.knowledge_base_brand'),
            'active_section' => 'forum',
            'articles' => $articles,
            'filters' => ['q' => $query, 'category' => $category, 'type' => $type],
            'categories' => $this->taxonomy->categoryOptions(),
            'types' => [
                'all' => __('messages.all_formats'),
                'guide' => __('knowledge.types.guide'),
                'checklist' => __('knowledge.types.checklist'),
                'faq' => __('knowledge.types.faq'),
                'comparison' => __('knowledge.types.comparison'),
                'local-guide' => __('knowledge.types.local-guide'),
            ],
            'can_create' => $this->gate->allows('create', KnowledgeArticle::class),
        ];
    }

    /** @return array<string, mixed> */
    public function article(KnowledgeArticle $article): array
    {
        $article->load([
            'versions' => fn ($versions) => $versions
                ->select([
                    'id',
                    'article_id',
                    'version_number',
                    'edited_by',
                    'change_summary',
                    'created_at',
                ])
                ->latest('version_number')
                ->limit(8),
            'sourceTopic' => fn ($topic) => $topic->forDirectory(),
            'discussionTopic' => fn ($topic) => $topic->forDirectory(),
            'replacement' => fn ($replacement) => $replacement->forLibrary(),
            'translatedFrom' => fn ($source) => $source->select([
                'id',
                'created_by_user_id',
                'forum_group_id',
                'slug',
                'title',
                'status',
                'language',
            ]),
            'translator:id,name',
            'activeCollaborators' => fn ($collaborators) => $collaborators
                ->select([
                    'id',
                    'article_id',
                    'user_id',
                    'role',
                    'attribution_name',
                    'revoked_at',
                ])
                ->with('user:id,name'),
            'taxon' => fn ($taxon) => $taxon
                ->select([
                    'id',
                    'stable_key',
                    'accepted_taxon_id',
                    'is_active',
                    'archived_at',
                ])
                ->with('activeVersion:id,taxon_id,rank,scientific_name,is_active_version'),
        ]);
        $translations = $article->translation_group_key === null
            ? collect()
            : KnowledgeArticle::query()
                ->forLibrary()
                ->where('translation_group_key', $article->translation_group_key)
                ->whereKeyNot($article->id)
                ->orderBy('language')
                ->limit(20)
                ->get()
                ->map(fn (KnowledgeArticle $translation): array => [
                    'slug' => $translation->slug,
                    'title' => $translation->title,
                    'language' => __("auth.locales.{$translation->language}"),
                ]);
        $supportedLocales = config('platform.supported_locales', ['en']);
        $canTranslate = $article->translation_group_key !== null
            && $this->gate->allows('translate', $article);
        $usedTranslationLocaleCount = $canTranslate
            ? KnowledgeArticle::query()
                ->where('translation_group_key', $article->translation_group_key)
                ->whereIn('language', $supportedLocales)
                ->distinct()
                ->count('language')
            : count($supportedLocales);
        $source = $article->translatedFrom;
        $visibleSource = $source instanceof KnowledgeArticle
            && $this->gate->allows('view', $source)
                ? [
                    'slug' => $source->slug,
                    'title' => $source->title,
                    'language' => __("auth.locales.{$source->language}"),
                ]
                : null;
        $normalizedContributors = $article->activeCollaborators
            ->map(static fn (KnowledgeArticleCollaborator $collaborator): array => [
                'name' => $collaborator->attribution_name ?? $collaborator->user->name,
                'role' => $collaborator->role->label(),
            ]);
        $legacyContributors = collect($article->contributors ?? [])
            ->map(fn (array|string $contributor): array => is_array($contributor)
                ? [
                    'name' => (string) ($contributor['name'] ?? __('messages.editorial_contributor')),
                    'role' => (string) ($contributor['role'] ?? __('messages.contributor')),
                ]
                : [
                    'name' => $contributor,
                    'role' => __('messages.contributor'),
                ]);

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => __('presentation.brand_title', ['title' => $article->title]),
            'active_section' => 'forum',
            'article' => [
                ...$this->card($article),
                'body' => $article->body,
                'audience' => $article->audience,
                'language' => __("auth.locales.{$article->language}"),
                'jurisdiction' => $article->jurisdiction,
                'tags' => $article->tags ?? [],
                'sources' => collect($article->sources ?? [])
                    ->map(fn (string $source): array => [
                        'url' => $source,
                        'label' => parse_url($source, PHP_URL_HOST) ?: $source,
                    ])
                    ->all(),
                'contributors' => $normalizedContributors
                    ->concat($legacyContributors)
                    ->unique(fn (array $contributor): string => $contributor['name'].'|'.$contributor['role'])
                    ->values()
                    ->all(),
                'next_review_label' => $this->formatter->date($article->next_review_at),
                'source_topic' => $article->sourceTopic ? [
                    'slug' => $article->sourceTopic->slug,
                    'title' => $article->sourceTopic->title,
                ] : null,
                'discussion_topic' => $article->discussionTopic ? [
                    'slug' => $article->discussionTopic->slug,
                    'title' => $article->discussionTopic->title,
                ] : null,
                'replacement' => $article->replacement ? [
                    'slug' => $article->replacement->slug,
                    'title' => $article->replacement->title,
                ] : null,
                'taxon' => $article->taxon?->activeVersion === null ? null : [
                    'scientific_name' => $article->taxon->activeVersion->scientific_name,
                    'rank' => $article->taxon->activeVersion->rank,
                ],
                'translations' => $translations->all(),
                'translation' => $article->translation_source === null ? null : [
                    'source_type' => $article->translation_source->value,
                    'source_label' => $article->translation_source->label(),
                    'source_article' => $visibleSource,
                    'translator' => $article->translator?->name,
                ],
                'can_edit' => $this->gate->allows('update', $article),
                'can_translate' => $canTranslate
                    && $usedTranslationLocaleCount < count($supportedLocales),
            ],
            'versions' => $article->versions
                ->map(fn (KnowledgeVersion $version): array => [
                    'number' => $version->version_number,
                    'editor' => $version->edited_by,
                    'summary' => $version->change_summary,
                    'created' => $this->formatter->date($version->created_at),
                ])
                ->all(),
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
            'category_label' => $this->taxonomy->categoryLabel($article->category),
            'type_label' => __("knowledge.types.{$article->type}"),
            'difficulty_label' => __("knowledge.difficulty.{$article->difficulty}"),
            'status_label' => $article->status->label(),
            'is_outdated' => $article->status->value === 'outdated',
            'reviewed_label' => $this->formatter->date($article->last_reviewed_at)
                ?? __('presentation.editorial_review_pending'),
            'version' => $article->current_version,
        ];
    }
}

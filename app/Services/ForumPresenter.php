<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumTopicStatus;
use App\Models\ForumEngagement;
use App\Models\ForumNotification;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ForumPresenter
{
    public function __construct(
        private readonly ForumTaxonomy $taxonomy,
        private readonly ForumActor $actor,
        private readonly LocaleFormatter $formatter,
        private readonly Gate $gate,
        private readonly Repository $config,
        private readonly PortalMediaUrl $mediaUrl,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function directory(array $filters): array
    {
        $query = trim((string) ($filters['q'] ?? ''));
        $category = (string) ($filters['category'] ?? 'all');
        $activeFilter = (string) ($filters['filter'] ?? 'all');
        $sort = (string) ($filters['sort'] ?? 'active');
        $language = (string) ($filters['language'] ?? 'all');
        $categories = $this->taxonomy->categories();
        $categorySelection = $this->taxonomy->browseSelection($category, $categories);

        $topicsQuery = ForumTopic::query()
            ->forDirectory()
            ->published()
            ->withoutBlockedAuthors($this->actor->key())
            ->search($query)
            ->inCategory($category)
            ->withStatusFilter($activeFilter)
            ->withCount([
                'answers' => fn (Builder $answers): Builder => $answers->where('status', 'published'),
                'comments' => fn (Builder $comments): Builder => $comments->where('status', 'published'),
            ])
            ->withSum([
                'answers as helpful_score' => fn (Builder $answers): Builder => $answers->where('status', 'published'),
            ], 'helpful_count');

        if ($language !== 'all') {
            $topicsQuery->where('language', $language);
        }

        match ($sort) {
            'new' => $topicsQuery->latest('published_at')->latest('id'),
            'helpful' => $topicsQuery->orderByDesc('helpful_score')->orderByDesc('last_activity_at'),
            'unanswered' => $topicsQuery->orderBy('answers_count')->orderByDesc('published_at'),
            default => $topicsQuery->orderByDesc('last_activity_at')->orderByDesc('id'),
        };

        $topics = $topicsQuery
            ->simplePaginate(8)
            ->withQueryString();

        $topicIds = $topics->getCollection()->pluck('id');
        $bookmarked = ForumEngagement::query()
            ->select(['topic_id'])
            ->where('user_key', $this->actor->key())
            ->where('is_bookmarked', true)
            ->whereIn('topic_id', $topicIds)
            ->pluck('topic_id')
            ->all();

        $topics->through(fn (ForumTopic $topic): array => $this->topicCard(
            $topic,
            in_array($topic->id, $bookmarked, true),
        ));

        return [
            'page_title' => __('messages.forum_and_knowledge_brand'),
            'active_section' => 'forum',
            'topics' => $topics,
            'filters' => [
                'q' => $query,
                'category' => $category,
                'filter' => $activeFilter,
                'sort' => $sort,
                'language' => $language,
            ],
            'filter_options' => $this->taxonomy->filterOptions(),
            'sort_options' => $this->taxonomy->sortOptions(),
            'category_navigation' => [
                'items' => $categories,
                'total' => count($categories),
                'active_root' => $categorySelection['root'],
                'active_subcategory' => $categorySelection['subcategory'],
                'active_category' => $categories[$categorySelection['root']] ?? null,
                'active_subcategory_total' => isset($categories[$categorySelection['root']])
                    ? count($categories[$categorySelection['root']]['subcategories'])
                    : 0,
            ],
            'stats' => $this->stats(),
            'knowledge' => KnowledgeArticle::query()
                ->forLibrary()
                ->latest('last_reviewed_at')
                ->limit(3)
                ->get()
                ->map(fn (KnowledgeArticle $article): array => $this->knowledgeCard($article))
                ->all(),
            'notifications' => ForumNotification::query()
                ->select(['id', 'topic_id', 'type', 'title', 'body', 'read_at', 'created_at'])
                ->where('user_key', $this->actor->key())
                ->latest('created_at')
                ->limit(4)
                ->get()
                ->map(fn (ForumNotification $notification): array => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'created_label' => $this->formatter->relative($notification->created_at),
                    'state_label' => $notification->read_at === null
                        ? __('forum.notifications.unread')
                        : __('forum.notifications.read'),
                ])
                ->all(),
            'draft_count' => ForumTopic::query()
                ->where('author_key', $this->actor->key())
                ->where('status', ForumTopicStatus::Draft->value)
                ->count(),
        ];
    }

    /** @return array<string, mixed> */
    public function topic(ForumTopic $topic): array
    {
        $topic->increment('view_count');
        $topic->load([
            'answers' => fn ($answers) => $answers
                ->forThread()
                ->with([
                    'expertProfile' => fn ($profiles) => $profiles->select([
                        'id', 'slug', 'public_name', 'primary_type', 'country',
                        'qualification_verified', 'verification_status', 'status',
                    ]),
                    'comments' => fn ($comments) => $comments
                        ->forThread()
                        ->orderBy('created_at')
                        ->orderBy('id'),
                    'votes' => fn ($votes) => $votes
                        ->select(['id', 'answer_id', 'user_key', 'value', 'reason']),
                ])
                ->orderByDesc('is_accepted')
                ->orderByDesc('is_verified_expert')
                ->orderByDesc('helpful_count')
                ->orderBy('created_at')
                ->orderBy('id'),
        ]);

        $engagement = ForumEngagement::query()
            ->select(['id', 'topic_id', 'is_bookmarked', 'subscription_level', 'last_read_at'])
            ->firstOrCreate(
                ['topic_id' => $topic->id, 'user_key' => $this->actor->key()],
                ['subscription_level' => 'none', 'last_read_at' => now()],
            );

        if ($engagement->last_read_at === null) {
            $engagement->update(['last_read_at' => now()]);
        }

        $journalId = $topic->type->value === 'journal'
            ? $topic->journal()->value('id')
            : null;

        return [
            'page_title' => __('presentation.brand_title', ['title' => $topic->title]),
            'active_section' => 'forum',
            'topic' => $this->topicDetail($topic),
            'answers' => $topic->answers
                ->map(fn ($answer): array => [
                    'id' => $answer->id,
                    'author_key' => $answer->author_key,
                    'author_name' => $answer->author_name,
                    'author_initials' => $answer->author_initials,
                    'author_role' => $answer->author_role,
                    'body' => $answer->body,
                    'experience_label' => Str::headline($answer->experience_type),
                    'is_verified_expert' => $answer->is_verified_expert,
                    'expertise' => $answer->expertise,
                    'qualification_region' => $answer->qualification_region,
                    'expert_profile' => $answer->expertProfile ? [
                        'slug' => $answer->expertProfile->slug,
                        'name' => $answer->expertProfile->public_name,
                        'type' => Str::headline($answer->expertProfile->primary_type),
                        'qualification_verified' => $answer->expertProfile->qualification_verified,
                        'verification_status' => $answer->expertProfile->verification_status->label(),
                        'profile_status' => $answer->expertProfile->status->label(),
                    ] : null,
                    'sources' => $answer->sources ?? [],
                    'is_accepted' => $answer->is_accepted,
                    'is_highlighted' => $answer->is_highlighted,
                    'needs_source' => $answer->needs_source,
                    'helpful_count' => $answer->helpful_count,
                    'voted' => $answer->votes
                        ->firstWhere('user_key', $this->actor->key())
                        ?->value
                        ?->value,
                    'created_label' => $this->formatter->relative($answer->created_at),
                    'comments' => $answer->comments->map(fn ($comment): array => [
                        'id' => $comment->id,
                        'parent_id' => $comment->parent_id,
                        'author_name' => $comment->author_name,
                        'author_initials' => $comment->author_initials,
                        'body' => $comment->body,
                        'is_pinned' => $comment->is_pinned,
                        'created_label' => $this->formatter->relative($comment->created_at),
                    ])->all(),
                ])
                ->all(),
            'engagement' => [
                'is_bookmarked' => $engagement->is_bookmarked,
                'subscription_level' => $engagement->subscription_level->value,
            ],
            'subscription_options' => $this->taxonomy->subscriptionOptions(),
            'similar_topics' => $this->similar($topic->title, $topic->category, $topic->id),
            'related_articles' => KnowledgeArticle::query()
                ->forLibrary()
                ->where('category', $topic->category)
                ->latest('last_reviewed_at')
                ->limit(3)
                ->get()
                ->map(fn (KnowledgeArticle $article): array => $this->knowledgeCard($article))
                ->all(),
            'can_manage' => $this->gate->allows('update', $topic),
            'can_answer' => $this->gate->allows('answer', $topic),
            'journal_id' => is_int($journalId) ? $journalId : null,
        ];
    }

    /** @return array<string, mixed> */
    public function editor(?ForumTopic $topic = null): array
    {
        return [
            'page_title' => $topic ? __('messages.edit_topic_brand') : __('messages.ask_the_community_brand'),
            'active_section' => 'forum',
            'topic' => $topic,
            'selected_taxon_ids' => $topic instanceof ForumTopic
                ? $topic->taxa()
                    ->pluck('taxa.id')
                    ->map(static fn (int $id): int => $id)
                    ->all()
                : [],
            'types' => $this->taxonomy->typeOptions(),
            'categories' => $this->taxonomy->categories(),
            'pets' => $this->taxonomy->petOptions(),
            'desired_answers' => $this->taxonomy->desiredAnswerOptions(),
            'visibility_options' => $this->taxonomy->visibilityOptions(),
            'comment_policies' => $this->taxonomy->commentPolicyOptions(),
            'suggested_tags' => $this->taxonomy->suggestedTags(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function similar(string $query, ?string $category = null, ?int $excludeId = null): array
    {
        return ForumTopic::query()
            ->forDirectory()
            ->published()
            ->when($excludeId !== null, fn (Builder $builder): Builder => $builder->whereKeyNot($excludeId))
            ->when(filled($category), fn (Builder $builder): Builder => $builder->where('category', $category))
            ->search(trim($query))
            ->withCount('answers')
            ->latest('last_activity_at')
            ->limit(5)
            ->get()
            ->map(fn (ForumTopic $topic): array => $this->topicCard($topic))
            ->all();
    }

    /** @return array<int, array{label: string, value: int, icon: string}> */
    private function stats(): array
    {
        $base = fn (): Builder => ForumTopic::query()->published();

        return [
            ['label' => __('messages.open_topics'), 'value' => $base()->count(), 'icon' => 'messages-square'],
            ['label' => __('messages.need_an_answer'), 'value' => $base()->whereDoesntHave('answers')->count(), 'icon' => 'circle-help'],
            ['label' => __('messages.resolved'), 'value' => $base()->whereIn('status', [
                ForumTopicStatus::Solved->value,
                ForumTopicStatus::Resolved->value,
            ])->count(), 'icon' => 'circle-check-big'],
            ['label' => __('messages.expert_replies'), 'value' => $base()->where('has_expert_answer', true)->count(), 'icon' => 'badge-check'],
        ];
    }

    /** @return array<string, mixed> */
    private function topicCard(ForumTopic $topic, bool $bookmarked = false): array
    {
        return [
            'id' => $topic->id,
            'slug' => $topic->slug,
            'title' => $topic->title,
            'excerpt' => Str::limit(strip_tags($topic->body), 190),
            'type_label' => $topic->type->label(),
            'status_label' => $topic->status->label(),
            'category' => $topic->category,
            'category_label' => $this->taxonomy->categoryLabel($topic->category),
            'tags' => $topic->tags ?? [],
            'author_name' => $topic->author_name,
            'author_key' => $topic->author_key,
            'author_initials' => $topic->author_initials,
            'author_role' => $topic->author_role,
            'pet_name' => $topic->pet_name,
            'pet_species' => $topic->pet_species,
            'pet_age_label' => $topic->pet_age_label,
            'location' => $topic->location,
            'is_urgent' => $topic->is_urgent,
            'is_medical' => $topic->is_medical,
            'has_expert_answer' => $topic->has_expert_answer,
            'has_accepted_answer' => $topic->accepted_answer_id !== null,
            'answers_count' => (int) ($topic->answers_count ?? 0),
            'comments_count' => (int) ($topic->comments_count ?? 0),
            'helpful_score' => (int) ($topic->helpful_score ?? 0),
            'view_count' => $topic->view_count,
            'activity_label' => $this->formatter->relative($topic->last_activity_at)
                ?? __('presentation.recently'),
            'bookmarked' => $bookmarked,
        ];
    }

    /** @return array<string, mixed> */
    private function topicDetail(ForumTopic $topic): array
    {
        return [
            ...$this->topicCard($topic),
            'body' => $topic->body,
            'visibility_label' => $topic->visibility->label(),
            'desired_answer_label' => $topic->desired_answer ? Str::headline($topic->desired_answer) : null,
            'comment_policy_label' => Str::headline($topic->comment_policy),
            'language' => Str::upper($topic->language),
            'media' => collect($topic->media ?? [])
                ->map(fn (array $media): array => $this->presentMedia($media, $topic->language))
                ->all(),
            'status_value' => $topic->status->value,
            'is_locked' => $topic->is_locked,
            'answers_count' => $topic->answers->count(),
            'comments_count' => $topic->answers->sum(fn ($answer): int => $answer->comments->count()),
        ];
    }

    /**
     * @param  array<string, mixed>  $media
     * @return array<string, mixed>
     */
    private function presentMedia(array $media, string $topicLocale): array
    {
        $description = trim((string) ($media['alt'] ?? ''));
        $description = $description !== ''
            ? $description
            : __('forum_accessibility.media.legacy_description');
        $type = (string) ($media['type'] ?? 'image');
        $captionPath = trim((string) ($media['caption_path'] ?? ''));
        $captionLocale = (string) ($media['caption_locale'] ?? $topicLocale);

        if (! in_array($captionLocale, ['en', 'lt', 'ru'], true)) {
            $captionLocale = $this->config->string('app.fallback_locale');
        }

        return [
            ...$media,
            'type' => $type,
            'alt' => $description,
            'transcript' => $type === 'video'
                ? (trim((string) ($media['transcript'] ?? '')) ?: $description)
                : null,
            'url' => Str::startsWith((string) $media['path'], ['http://', 'https://'])
                ? $media['path']
                : $this->mediaUrl->for((string) $media['path']),
            'captions_url' => $captionPath !== ''
                ? $this->mediaUrl->for($captionPath)
                : null,
            'caption_locale' => $captionLocale,
        ];
    }

    /** @return array<string, mixed> */
    private function knowledgeCard(KnowledgeArticle $article): array
    {
        return [
            'slug' => $article->slug,
            'title' => $article->title,
            'summary' => $article->summary,
            'category' => $article->category,
            'type_label' => Str::headline($article->type),
            'difficulty_label' => Str::headline($article->difficulty),
            'reviewed_label' => $this->formatter->date($article->last_reviewed_at)
                ?? __('presentation.editorial_review_pending'),
            'is_outdated' => $article->status->value === 'outdated',
        ];
    }
}

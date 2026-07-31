<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;
use Database\Factories\ForumTopicFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property-read ForumAnswer|null $acceptedAnswer
 * @property int|null $accepted_answer_id
 * @property-read Collection<int, ForumAnswer> $answers
 * @property-read User|null $author
 * @property int|null $author_id
 * @property string $author_initials
 * @property string $author_key
 * @property string $author_name
 * @property string|null $author_role
 * @property string $body
 * @property string $category
 * @property string $comment_policy
 * @property-read Collection<int, ForumComment> $comments
 * @property Carbon|null $created_at
 * @property string|null $desired_answer
 * @property-read Collection<int, ForumEngagement> $engagements
 * @property bool $has_expert_answer
 * @property int $id
 * @property int|null $forum_category_id
 * @property int|null $forum_group_id
 * @property bool $is_locked
 * @property bool $is_medical
 * @property bool $is_urgent
 * @property Carbon|null $last_author_update_at
 * @property Carbon|null $last_bumped_at
 * @property-read ForumJournal|null $journal
 * @property-read Collection<int, KnowledgeArticle> $knowledgeArticles
 * @property string $language
 * @property Carbon|null $last_activity_at
 * @property Carbon|null $legal_hold_at
 * @property int $lock_version
 * @property string|null $location
 * @property array<array-key, mixed>|null $media
 * @property string|null $pet_age_label
 * @property string|null $pet_key
 * @property string|null $pet_name
 * @property string|null $pet_species
 * @property Carbon|null $published_at
 * @property Carbon|null $redirected_at
 * @property array<int, int>|null $redirect_path
 * @property Carbon|null $removed_at
 * @property-read Collection<int, ForumReport> $reports
 * @property Carbon|null $restored_at
 * @property Carbon|null $retention_until
 * @property string $slug
 * @property Carbon|null $state_entered_at
 * @property ForumTopicStatus $status
 * @property Carbon|null $stale_review_requested_at
 * @property int $structured_data_version
 * @property string|null $subcategory
 * @property array<array-key, mixed>|null $tags
 * @property string $title
 * @property ForumTopicType $type
 * @property Carbon|null $updated_at
 * @property int $view_count
 * @property ForumVisibility $visibility
 */
class ForumTopic extends Model
{
    /** @use HasFactory<ForumTopicFactory> */
    use HasFactory;

    protected $attributes = [
        'structured_data_version' => 1,
        'lock_version' => 1,
    ];

    private const ROUTE_COLUMNS = [
        'id',
        'author_id',
        'author_key',
        'author_name',
        'author_initials',
        'author_role',
        'slug',
        'type',
        'title',
        'body',
        'category',
        'subcategory',
        'forum_category_id',
        'forum_group_id',
        'forum_topic_type_id',
        'tags',
        'pet_key',
        'pet_name',
        'pet_species',
        'pet_age_label',
        'location',
        'status',
        'visibility',
        'desired_answer',
        'comment_policy',
        'language',
        'media',
        'is_urgent',
        'is_medical',
        'is_locked',
        'has_expert_answer',
        'accepted_answer_id',
        'view_count',
        'last_activity_at',
        'published_at',
        'state_entered_at',
        'last_author_update_at',
        'last_bumped_at',
        'stale_review_requested_at',
        'outdated_at',
        'locked_at',
        'removed_at',
        'restored_at',
        'redirected_at',
        'redirect_path',
        'legal_hold_at',
        'retention_until',
        'structured_data',
        'structured_data_version',
        'lock_version',
        'archived_at',
        'merged_into_topic_id',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'author_id',
        'author_key',
        'author_name',
        'author_initials',
        'author_role',
        'slug',
        'type',
        'title',
        'body',
        'category',
        'subcategory',
        'forum_category_id',
        'forum_group_id',
        'forum_topic_type_id',
        'tags',
        'pet_key',
        'pet_name',
        'pet_species',
        'pet_age_label',
        'location',
        'status',
        'visibility',
        'desired_answer',
        'comment_policy',
        'language',
        'media',
        'is_urgent',
        'is_medical',
        'is_locked',
        'has_expert_answer',
        'accepted_answer_id',
        'view_count',
        'last_activity_at',
        'published_at',
        'state_entered_at',
        'last_author_update_at',
        'last_bumped_at',
        'stale_review_requested_at',
        'outdated_at',
        'locked_at',
        'removed_at',
        'restored_at',
        'redirected_at',
        'redirect_path',
        'legal_hold_at',
        'retention_until',
        'structured_data',
        'structured_data_version',
        'lock_version',
        'archived_at',
        'merged_into_topic_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => ForumTopicType::class,
            'status' => ForumTopicStatus::class,
            'visibility' => ForumVisibility::class,
            'tags' => 'array',
            'media' => 'array',
            'structured_data' => 'array',
            'structured_data_version' => 'integer',
            'lock_version' => 'integer',
            'is_urgent' => 'boolean',
            'is_medical' => 'boolean',
            'is_locked' => 'boolean',
            'has_expert_answer' => 'boolean',
            'last_activity_at' => 'datetime',
            'published_at' => 'datetime',
            'state_entered_at' => 'immutable_datetime',
            'last_author_update_at' => 'immutable_datetime',
            'last_bumped_at' => 'immutable_datetime',
            'stale_review_requested_at' => 'immutable_datetime',
            'outdated_at' => 'immutable_datetime',
            'locked_at' => 'immutable_datetime',
            'removed_at' => 'immutable_datetime',
            'restored_at' => 'immutable_datetime',
            'redirected_at' => 'immutable_datetime',
            'redirect_path' => 'array',
            'legal_hold_at' => 'immutable_datetime',
            'retention_until' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    /** @return BelongsTo<\App\Models\User, $this>*/
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function normalizedCategory(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'forum_category_id');
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<\App\Models\ForumTopicType, $this> */
    public function topicTypeDefinition(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ForumTopicType::class, 'forum_topic_type_id');
    }

    /** @return HasOne<ForumJournal, $this> */
    public function journal(): HasOne
    {
        return $this->hasOne(ForumJournal::class);
    }

    /** @return HasMany<ForumTopicAcceptance, $this> */
    public function acceptances(): HasMany
    {
        return $this->hasMany(ForumTopicAcceptance::class);
    }

    /** @return HasMany<ForumTopicLifecycleEvent, $this> */
    public function lifecycleEvents(): HasMany
    {
        return $this->hasMany(ForumTopicLifecycleEvent::class)
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');
    }

    /** @return HasMany<ForumTopicUpdateRequest, $this> */
    public function updateRequests(): HasMany
    {
        return $this->hasMany(ForumTopicUpdateRequest::class);
    }

    /** @return HasMany<ForumTopicLegalHold, $this> */
    public function legalHolds(): HasMany
    {
        return $this->hasMany(ForumTopicLegalHold::class);
    }

    /** @return HasOne<ForumTopicLegalHold, $this> */
    public function activeLegalHold(): HasOne
    {
        return $this->hasOne(ForumTopicLegalHold::class)
            ->whereNull('released_at');
    }

    /** @return BelongsTo<ForumTopic, $this> */
    public function redirectionTarget(): BelongsTo
    {
        return $this->belongsTo(self::class, 'merged_into_topic_id');
    }

    /** @return HasMany<ForumCommunityNote, $this> */
    public function communityNotes(): HasMany
    {
        return $this->hasMany(ForumCommunityNote::class, 'subject_id')
            ->where('subject_type', 'forum-topic');
    }

    /** @return BelongsToMany<Taxon, $this> */
    public function taxa(): BelongsToMany
    {
        return $this->belongsToMany(Taxon::class, 'forum_topic_taxon')
            ->withPivot(['context_type', 'topic_time_snapshot'])
            ->withTimestamps();
    }

    /** @return HasMany<\App\Models\ForumAnswer, $this>*/
    public function answers(): HasMany
    {
        return $this->hasMany(ForumAnswer::class, 'topic_id');
    }

    /** @return HasMany<\App\Models\ForumComment, $this>*/
    public function comments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'topic_id');
    }

    /** @return HasOne<\App\Models\ForumAnswer, $this>*/
    public function acceptedAnswer(): HasOne
    {
        return $this->hasOne(ForumAnswer::class, 'topic_id')->where('is_accepted', true);
    }

    /** @return HasMany<\App\Models\ForumEngagement, $this>*/
    public function engagements(): HasMany
    {
        return $this->hasMany(ForumEngagement::class, 'topic_id');
    }

    /** @return HasMany<\App\Models\ForumReport, $this>*/
    public function reports(): HasMany
    {
        return $this->hasMany(ForumReport::class, 'topic_id');
    }

    /** @return HasMany<\App\Models\KnowledgeArticle, $this>*/
    public function knowledgeArticles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class, 'source_topic_id');
    }

    public function scopeForDirectory(Builder $query): Builder
    {
        return $query->select([
            'id',
            'author_key',
            'author_name',
            'author_initials',
            'author_role',
            'slug',
            'type',
            'title',
            'body',
            'category',
            'forum_category_id',
            'forum_group_id',
            'subcategory',
            'tags',
            'pet_key',
            'pet_name',
            'pet_species',
            'pet_age_label',
            'location',
            'status',
            'visibility',
            'language',
            'is_urgent',
            'is_medical',
            'has_expert_answer',
            'accepted_answer_id',
            'view_count',
            'last_activity_at',
            'published_at',
            'created_at',
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', ForumTopicStatus::publicValues())
            ->whereNull('forum_group_id')
            ->where('visibility', ForumVisibility::Public->value);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        $term = '%'.$search.'%';

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('title', 'like', $term)
                ->orWhere('body', 'like', $term)
                ->orWhere('category', 'like', $term)
                ->orWhere('subcategory', 'like', $term)
                ->orWhere('tags', 'like', $term)
                ->orWhere('pet_name', 'like', $term)
                ->orWhere('location', 'like', $term);
        });
    }

    public function scopeInCategory(Builder $query, string $category): Builder
    {
        if ($category === 'all') {
            return $query;
        }

        return $query->where(function (Builder $categoryQuery) use ($category): void {
            $categoryQuery
                ->where('category', $category)
                ->orWhereHas(
                    'normalizedCategory',
                    fn (Builder $normalized): Builder => $normalized->where('slug', $category),
                );
        });
    }

    public function scopeWithStatusFilter(Builder $query, string $status): Builder
    {
        return match ($status) {
            'unanswered' => $query->whereDoesntHave('answers', fn (Builder $answers): Builder => $answers->where('status', 'published')),
            'resolved' => $query->whereIn('status', [
                ForumTopicStatus::Solved->value,
                ForumTopicStatus::Resolved->value,
            ]),
            'expert' => $query->where('has_expert_answer', true),
            'local' => $query->whereNotNull('location'),
            'medical' => $query->where('is_medical', true),
            default => $query,
        };
    }

    public function scopeWithoutBlockedAuthors(Builder $query, string $userKey): Builder
    {
        return $query->whereNotIn(
            'author_key',
            ForumBlock::query()
                ->select('blocked_author_key')
                ->where('user_key', $userKey),
        );
    }

    public function hasActiveLegalHold(): bool
    {
        return $this->legal_hold_at !== null;
    }
}

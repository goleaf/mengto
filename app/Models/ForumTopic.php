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
 * @property bool $is_locked
 * @property bool $is_medical
 * @property bool $is_urgent
 * @property-read Collection<int, KnowledgeArticle> $knowledgeArticles
 * @property string $language
 * @property Carbon|null $last_activity_at
 * @property string|null $location
 * @property array<array-key, mixed>|null $media
 * @property string|null $pet_age_label
 * @property string|null $pet_key
 * @property string|null $pet_name
 * @property string|null $pet_species
 * @property Carbon|null $published_at
 * @property-read Collection<int, ForumReport> $reports
 * @property string $slug
 * @property ForumTopicStatus $status
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
    ];

    protected function casts(): array
    {
        return [
            'type' => ForumTopicType::class,
            'status' => ForumTopicStatus::class,
            'visibility' => ForumVisibility::class,
            'tags' => 'array',
            'media' => 'array',
            'is_urgent' => 'boolean',
            'is_medical' => 'boolean',
            'is_locked' => 'boolean',
            'has_expert_answer' => 'boolean',
            'last_activity_at' => 'datetime',
            'published_at' => 'datetime',
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
        return $query->whereNotIn('status', [
            ForumTopicStatus::Draft->value,
            ForumTopicStatus::Review->value,
            ForumTopicStatus::Archived->value,
            ForumTopicStatus::Merged->value,
        ])->where('visibility', '!=', ForumVisibility::Private->value);
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
        return $category === 'all' ? $query : $query->where('category', $category);
    }

    public function scopeWithStatusFilter(Builder $query, string $status): Builder
    {
        return match ($status) {
            'unanswered' => $query->whereDoesntHave('answers', fn (Builder $answers): Builder => $answers->where('status', 'published')),
            'resolved' => $query->where('status', ForumTopicStatus::Resolved->value),
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
}

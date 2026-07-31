<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KnowledgeStatus;
use Database\Factories\KnowledgeArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string|null $audience
 * @property string $body
 * @property string $category
 * @property array<array-key, mixed>|null $contributors
 * @property int|null $created_by_user_id
 * @property-read Collection<int, KnowledgeCorrection> $corrections
 * @property Carbon|null $created_at
 * @property int $current_version
 * @property string $difficulty
 * @property int|null $discussion_topic_id
 * @property int|null $forum_group_id
 * @property Carbon|null $editorial_locked_at
 * @property int|null $editorial_locked_by_user_id
 * @property string|null $editorial_lock_reason
 * @property int $id
 * @property string|null $jurisdiction
 * @property string $language
 * @property Carbon|null $last_reviewed_at
 * @property int $lock_version
 * @property Carbon|null $next_review_at
 * @property array<array-key, mixed>|null $protected_sections
 * @property Carbon|null $published_at
 * @property int|null $replaced_by_article_id
 * @property string $slug
 * @property-read ForumTopic|null $sourceTopic
 * @property int|null $source_topic_id
 * @property array<array-key, mixed>|null $sources
 * @property KnowledgeStatus $status
 * @property string $summary
 * @property array<array-key, mixed>|null $tags
 * @property int|null $taxon_id
 * @property string $title
 * @property string|null $translation_group_key
 * @property string $type
 * @property Carbon|null $updated_at
 * @property-read Collection<int, KnowledgeArticleCollaborator> $collaborators
 * @property-read Collection<int, KnowledgeWorkflowEvent> $workflowEvents
 * @property-read Collection<int, KnowledgeVersion> $versions
 */
class KnowledgeArticle extends Model
{
    /** @use HasFactory<KnowledgeArticleFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id',
        'created_by_user_id',
        'forum_group_id',
        'source_topic_id',
        'discussion_topic_id',
        'taxon_id',
        'replaced_by_article_id',
        'slug',
        'translation_group_key',
        'title',
        'summary',
        'body',
        'category',
        'type',
        'difficulty',
        'audience',
        'status',
        'language',
        'tags',
        'sources',
        'contributors',
        'jurisdiction',
        'protected_sections',
        'current_version',
        'lock_version',
        'last_reviewed_at',
        'next_review_at',
        'published_at',
        'editorial_locked_at',
        'editorial_locked_by_user_id',
        'editorial_lock_reason',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'created_by_user_id',
        'forum_group_id',
        'source_topic_id',
        'discussion_topic_id',
        'taxon_id',
        'replaced_by_article_id',
        'slug',
        'translation_group_key',
        'title',
        'summary',
        'body',
        'category',
        'type',
        'difficulty',
        'audience',
        'status',
        'language',
        'tags',
        'sources',
        'contributors',
        'jurisdiction',
        'protected_sections',
        'current_version',
        'lock_version',
        'last_reviewed_at',
        'next_review_at',
        'published_at',
        'editorial_locked_at',
        'editorial_locked_by_user_id',
        'editorial_lock_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => KnowledgeStatus::class,
            'tags' => 'array',
            'sources' => 'array',
            'contributors' => 'array',
            'protected_sections' => 'array',
            'lock_version' => 'integer',
            'last_reviewed_at' => 'datetime',
            'next_review_at' => 'datetime',
            'published_at' => 'datetime',
            'editorial_locked_at' => 'immutable_datetime',
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

    /** @return BelongsTo<\App\Models\ForumTopic, $this>*/
    public function sourceTopic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'source_topic_id');
    }

    /** @return BelongsTo<ForumGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ForumGroup::class, 'forum_group_id');
    }

    /** @return BelongsTo<ForumTopic, $this> */
    public function discussionTopic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'discussion_topic_id');
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    /** @return BelongsTo<KnowledgeArticle, $this> */
    public function replacement(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaced_by_article_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function editorialLocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editorial_locked_by_user_id');
    }

    /** @return HasMany<\App\Models\KnowledgeVersion, $this>*/
    public function versions(): HasMany
    {
        return $this->hasMany(KnowledgeVersion::class, 'article_id');
    }

    /** @return HasMany<\App\Models\KnowledgeCorrection, $this>*/
    public function corrections(): HasMany
    {
        return $this->hasMany(KnowledgeCorrection::class, 'article_id');
    }

    /** @return HasMany<KnowledgeArticleCollaborator, $this> */
    public function collaborators(): HasMany
    {
        return $this->hasMany(KnowledgeArticleCollaborator::class, 'article_id');
    }

    /** @return HasMany<KnowledgeArticleCollaborator, $this> */
    public function activeCollaborators(): HasMany
    {
        return $this->collaborators()->whereNull('revoked_at');
    }

    /** @return HasMany<KnowledgeWorkflowEvent, $this> */
    public function workflowEvents(): HasMany
    {
        return $this->hasMany(KnowledgeWorkflowEvent::class, 'article_id');
    }

    public function scopeForLibrary(Builder $query): Builder
    {
        return $query->select([
            'id',
            'created_by_user_id',
            'forum_group_id',
            'source_topic_id',
            'discussion_topic_id',
            'taxon_id',
            'replaced_by_article_id',
            'slug',
            'translation_group_key',
            'title',
            'summary',
            'category',
            'type',
            'difficulty',
            'audience',
            'status',
            'language',
            'tags',
            'sources',
            'contributors',
            'jurisdiction',
            'protected_sections',
            'current_version',
            'lock_version',
            'last_reviewed_at',
            'next_review_at',
            'published_at',
            'updated_at',
        ])
            ->whereNull('forum_group_id')
            ->whereIn('status', KnowledgeStatus::publicValues());
    }

    public function scopeForEditor(Builder $query): Builder
    {
        return $query->select(self::ROUTE_COLUMNS);
    }
}

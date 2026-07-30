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
 * @property-read Collection<int, KnowledgeCorrection> $corrections
 * @property Carbon|null $created_at
 * @property int $current_version
 * @property string $difficulty
 * @property int $id
 * @property string $language
 * @property Carbon|null $last_reviewed_at
 * @property Carbon|null $next_review_at
 * @property Carbon|null $published_at
 * @property string $slug
 * @property-read ForumTopic|null $sourceTopic
 * @property int|null $source_topic_id
 * @property array<array-key, mixed>|null $sources
 * @property KnowledgeStatus $status
 * @property string $summary
 * @property array<array-key, mixed>|null $tags
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 * @property-read Collection<int, KnowledgeVersion> $versions
 */
class KnowledgeArticle extends Model
{
    /** @use HasFactory<KnowledgeArticleFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id',
        'source_topic_id',
        'slug',
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
        'current_version',
        'last_reviewed_at',
        'next_review_at',
        'published_at',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'source_topic_id',
        'slug',
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
        'current_version',
        'last_reviewed_at',
        'next_review_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => KnowledgeStatus::class,
            'tags' => 'array',
            'sources' => 'array',
            'contributors' => 'array',
            'last_reviewed_at' => 'datetime',
            'next_review_at' => 'datetime',
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

    /** @return BelongsTo<\App\Models\ForumTopic, $this>*/
    public function sourceTopic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'source_topic_id');
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

    public function scopeForLibrary(Builder $query): Builder
    {
        return $query->select([
            'id',
            'source_topic_id',
            'slug',
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
            'current_version',
            'last_reviewed_at',
            'next_review_at',
            'published_at',
            'updated_at',
        ])->whereIn('status', [
            KnowledgeStatus::Published->value,
            KnowledgeStatus::Outdated->value,
        ]);
    }
}

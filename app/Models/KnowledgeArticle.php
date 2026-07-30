<?php

namespace App\Models;

use App\Enums\KnowledgeStatus;
use Database\Factories\KnowledgeArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function sourceTopic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'source_topic_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(KnowledgeVersion::class, 'article_id');
    }

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

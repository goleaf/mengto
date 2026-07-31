<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ForumCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Collection<int, ForumCategory> $children
 * @property-read ForumCategory|null $parent
 * @property-read Collection<int, ForumCategoryTranslation> $translations
 */
final class ForumCategory extends Model
{
    /** @use HasFactory<ForumCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'stable_key',
        'slug',
        'icon',
        'position',
        'visibility',
        'moderation_level',
        'schema_version',
        'is_system_managed',
        'is_active',
        'rules',
        'permissions',
        'topic_type_keys',
        'metadata',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'schema_version' => 'integer',
            'is_system_managed' => 'boolean',
            'is_active' => 'boolean',
            'rules' => 'array',
            'permissions' => 'array',
            'topic_type_keys' => 'array',
            'metadata' => 'array',
            'archived_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ForumCategory, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<ForumCategory, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    /** @return HasMany<ForumCategoryTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(ForumCategoryTranslation::class);
    }

    /** @return HasOne<ForumCategoryLifecycleRule, $this> */
    public function lifecycleRule(): HasOne
    {
        return $this->hasOne(ForumCategoryLifecycleRule::class);
    }

    /** @return HasMany<ForumCategoryAlias, $this> */
    public function aliases(): HasMany
    {
        return $this->hasMany(ForumCategoryAlias::class);
    }

    /** @return BelongsToMany<ForumCategory, $this> */
    public function relatedCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'forum_category_relations',
            'forum_category_id',
            'related_forum_category_id',
        )->withPivot(['relation_type', 'position'])->withTimestamps();
    }

    /** @return HasMany<ForumTopic, $this> */
    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('archived_at');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }
}

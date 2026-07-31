<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TaxonFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read TaxonVersion|null $activeVersion
 * @property-read Collection<int, TaxonName> $names
 */
final class Taxon extends Model
{
    /** @use HasFactory<TaxonFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'accepted_taxon_id',
        'original_taxon_id',
        'resolution_status',
        'requires_review',
        'is_active',
        'metadata',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'requires_review' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
            'archived_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Taxon, $this> */
    public function acceptedTaxon(): BelongsTo
    {
        return $this->belongsTo(self::class, 'accepted_taxon_id');
    }

    /** @return HasMany<TaxonVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(TaxonVersion::class);
    }

    /** @return HasOne<TaxonVersion, $this> */
    public function activeVersion(): HasOne
    {
        return $this->hasOne(TaxonVersion::class)->where('is_active_version', true);
    }

    /** @return HasMany<TaxonName, $this> */
    public function names(): HasMany
    {
        return $this->hasMany(TaxonName::class);
    }

    /** @return HasMany<AdoptionCase, $this> */
    public function adoptionCases(): HasMany
    {
        return $this->hasMany(AdoptionCase::class);
    }

    /** @return HasMany<SearchCase, $this> */
    public function searchCases(): HasMany
    {
        return $this->hasMany(SearchCase::class);
    }

    /** @return HasMany<DomesticClassification, $this> */
    public function domesticClassifications(): HasMany
    {
        return $this->hasMany(DomesticClassification::class);
    }

    /** @return BelongsToMany<ForumTopic, $this> */
    public function forumTopics(): BelongsToMany
    {
        return $this->belongsToMany(ForumTopic::class, 'forum_topic_taxon')
            ->withPivot(['context_type', 'topic_time_snapshot'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('archived_at');
    }
}

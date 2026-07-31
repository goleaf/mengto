<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DomesticClassificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class DomesticClassification extends Model
{
    /** @use HasFactory<DomesticClassificationFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'taxon_id',
        'breed_registry_id',
        'parent_id',
        'classification_type',
        'canonical_name',
        'registry_identifier',
        'is_active',
        'aliases',
        'metadata',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'aliases' => 'array',
            'metadata' => 'array',
            'archived_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    /** @return BelongsTo<BreedRegistry, $this> */
    public function registry(): BelongsTo
    {
        return $this->belongsTo(BreedRegistry::class, 'breed_registry_id');
    }

    /** @return BelongsTo<DomesticClassification, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<DomesticClassification, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
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
}

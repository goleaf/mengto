<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TaxonVersionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TaxonVersion extends Model
{
    /** @use HasFactory<TaxonVersionFactory> */
    use HasFactory;

    protected $fillable = [
        'taxon_id',
        'taxon_import_id',
        'taxon_source_id',
        'parent_taxon_id',
        'source_record_id',
        'rank',
        'scientific_name',
        'canonical_name',
        'normalized_scientific_name',
        'authorship',
        'nomenclatural_code',
        'taxonomic_status',
        'depth',
        'hierarchy_path',
        'is_extinct',
        'is_fossil',
        'is_marine',
        'is_freshwater',
        'is_terrestrial',
        'has_domestic_relevance',
        'has_community_relevance',
        'is_active_version',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'is_extinct' => 'boolean',
            'is_fossil' => 'boolean',
            'is_marine' => 'boolean',
            'is_freshwater' => 'boolean',
            'is_terrestrial' => 'boolean',
            'has_domestic_relevance' => 'boolean',
            'has_community_relevance' => 'boolean',
            'is_active_version' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    /** @return BelongsTo<Taxon, $this> */
    public function parentTaxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class, 'parent_taxon_id');
    }

    /** @return BelongsTo<TaxonImport, $this> */
    public function import(): BelongsTo
    {
        return $this->belongsTo(TaxonImport::class, 'taxon_import_id');
    }

    /** @return BelongsTo<TaxonSource, $this> */
    public function source(): BelongsTo
    {
        return $this->belongsTo(TaxonSource::class, 'taxon_source_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active_version', true);
    }
}

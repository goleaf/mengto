<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TaxonNameFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TaxonName extends Model
{
    /** @use HasFactory<TaxonNameFactory> */
    use HasFactory;

    protected $fillable = [
        'taxon_id',
        'taxon_import_id',
        'taxon_source_id',
        'locale',
        'language',
        'script',
        'name',
        'normalized_name',
        'name_type',
        'source_record_id',
        'import_key',
        'geographic_scope',
        'is_preferred',
        'is_verified',
        'is_local_override',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_preferred' => 'boolean',
            'is_verified' => 'boolean',
            'is_local_override' => 'boolean',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    public function scopeSearch(Builder $query, string $normalizedQuery): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('normalized_name', 'like', $normalizedQuery.'%');
    }
}

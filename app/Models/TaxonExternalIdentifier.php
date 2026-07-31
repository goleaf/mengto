<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TaxonExternalIdentifierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TaxonExternalIdentifier extends Model
{
    /** @use HasFactory<TaxonExternalIdentifierFactory> */
    use HasFactory;

    protected $fillable = [
        'taxon_id',
        'taxon_source_id',
        'external_identifier',
        'identifier_type',
        'version',
        'external_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    /** @return BelongsTo<TaxonSource, $this> */
    public function source(): BelongsTo
    {
        return $this->belongsTo(TaxonSource::class, 'taxon_source_id');
    }
}

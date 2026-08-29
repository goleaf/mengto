<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TaxonSourceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TaxonSource extends Model
{
    /** @use HasFactory<TaxonSourceFactory> */
    use HasFactory;

    protected $fillable = [
        'stable_key',
        'name',
        'source_type',
        'version',
        'release_date',
        'downloaded_at',
        'checksum',
        'license',
        'attribution',
        'source_url',
        'import_priority',
        'is_active',
        'active_taxon_import_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'immutable_date',
            'downloaded_at' => 'immutable_datetime',
            'import_priority' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<TaxonImport, $this> */
    public function activeImport(): BelongsTo
    {
        return $this->belongsTo(TaxonImport::class, 'active_taxon_import_id');
    }

    /** @return HasMany<TaxonImport, $this> */
    public function imports(): HasMany
    {
        return $this->hasMany(TaxonImport::class);
    }

    /** @return HasMany<TaxonExternalIdentifier, $this> */
    public function externalIdentifiers(): HasMany
    {
        return $this->hasMany(TaxonExternalIdentifier::class);
    }

    /** @return HasMany<TaxonName, $this> */
    public function names(): HasMany
    {
        return $this->hasMany(TaxonName::class);
    }

    /** @return HasMany<TaxonVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(TaxonVersion::class);
    }
}

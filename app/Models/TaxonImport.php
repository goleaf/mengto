<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TaxonImportState;
use Database\Factories\TaxonImportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $current_chunk
 * @property int $error_rows
 * @property int $id
 * @property array<string, mixed>|null $impact_report
 * @property array<string, mixed>|null $metadata
 * @property int $processed_rows
 * @property string|null $resume_token
 * @property TaxonImportState $state
 * @property int $synonym_rows
 * @property int $taxon_source_id
 * @property-read TaxonSource $source
 */
final class TaxonImport extends Model
{
    /** @use HasFactory<TaxonImportFactory> */
    use HasFactory;

    protected $fillable = [
        'taxon_source_id',
        'initiated_by_user_id',
        'source_version',
        'state',
        'checksum',
        'current_chunk',
        'processed_rows',
        'inserted_rows',
        'updated_rows',
        'unchanged_rows',
        'synonym_rows',
        'archived_rows',
        'error_rows',
        'warning_rows',
        'resume_token',
        'impact_report',
        'error_report',
        'metadata',
        'started_at',
        'completed_at',
        'activated_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'state' => TaxonImportState::class,
            'current_chunk' => 'integer',
            'processed_rows' => 'integer',
            'inserted_rows' => 'integer',
            'updated_rows' => 'integer',
            'unchanged_rows' => 'integer',
            'synonym_rows' => 'integer',
            'archived_rows' => 'integer',
            'error_rows' => 'integer',
            'warning_rows' => 'integer',
            'impact_report' => 'array',
            'error_report' => 'array',
            'metadata' => 'array',
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'activated_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<TaxonSource, $this> */
    public function source(): BelongsTo
    {
        return $this->belongsTo(TaxonSource::class, 'taxon_source_id');
    }

    /** @return BelongsTo<User, $this> */
    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    /** @return HasMany<TaxonVersion, $this> */
    public function versions(): HasMany
    {
        return $this->hasMany(TaxonVersion::class);
    }

    /** @return HasMany<TaxonImportIssue, $this> */
    public function issues(): HasMany
    {
        return $this->hasMany(TaxonImportIssue::class);
    }

    /** @return HasMany<TaxonChange, $this> */
    public function changes(): HasMany
    {
        return $this->hasMany(TaxonChange::class);
    }

    /** @return HasMany<TaxonName, $this> */
    public function names(): HasMany
    {
        return $this->hasMany(TaxonName::class);
    }
}

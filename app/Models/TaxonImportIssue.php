<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TaxonImportIssueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TaxonImportIssue extends Model
{
    /** @use HasFactory<TaxonImportIssueFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'taxon_import_id',
        'source_row',
        'source_record_id',
        'severity',
        'code',
        'context',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'source_row' => 'integer',
            'context' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<TaxonImport, $this> */
    public function import(): BelongsTo
    {
        return $this->belongsTo(TaxonImport::class, 'taxon_import_id');
    }
}

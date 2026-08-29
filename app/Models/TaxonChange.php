<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TaxonChangeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TaxonChange extends Model
{
    /** @use HasFactory<TaxonChangeFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'taxon_id',
        'taxon_import_id',
        'actor_user_id',
        'change_type',
        'before',
        'after',
        'reason_code',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Taxon, $this> */
    public function taxon(): BelongsTo
    {
        return $this->belongsTo(Taxon::class);
    }

    /** @return BelongsTo<TaxonImport, $this> */
    public function import(): BelongsTo
    {
        return $this->belongsTo(TaxonImport::class, 'taxon_import_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

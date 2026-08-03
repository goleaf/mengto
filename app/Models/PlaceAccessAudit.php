<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceAccessAuditFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceAccessAudit extends Model
{
    /** @use HasFactory<PlaceAccessAuditFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'place_id', 'user_id', 'place_access_grant_id', 'event_id', 'event_type',
        'purpose', 'channel', 'metadata', 'created_at',
    ];

    protected $hidden = ['metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'encrypted:array', 'created_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<PlaceAccessGrant, $this> */
    public function grant(): BelongsTo
    {
        return $this->belongsTo(PlaceAccessGrant::class, 'place_access_grant_id');
    }
}

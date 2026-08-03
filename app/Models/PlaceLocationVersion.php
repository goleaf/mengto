<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceLocationVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceLocationVersion extends Model
{
    /** @use HasFactory<PlaceLocationVersionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'place_id', 'changed_by_user_id', 'version', 'public_region', 'public_address',
        'public_latitude', 'public_longitude', 'exact_address', 'exact_latitude',
        'exact_longitude', 'private_instructions', 'reason_code', 'created_at',
    ];

    protected $hidden = ['exact_address', 'exact_latitude', 'exact_longitude', 'private_instructions'];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'public_latitude' => 'decimal:6',
            'public_longitude' => 'decimal:6',
            'exact_address' => 'encrypted',
            'exact_latitude' => 'encrypted',
            'exact_longitude' => 'encrypted',
            'private_instructions' => 'encrypted',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}

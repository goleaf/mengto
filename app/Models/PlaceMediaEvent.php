<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class PlaceMediaEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'place_media_id', 'actor_user_id', 'event_type', 'reason_code',
        'idempotency_key', 'metadata', 'created_at',
    ];

    protected $hidden = ['idempotency_key', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'encrypted:array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('Place media events are append-only.');
        });
        static::deleting(static function (): never {
            throw new LogicException('Place media events are immutable.');
        });
    }

    /** @return BelongsTo<PlaceMedia, $this> */
    public function media(): BelongsTo
    {
        return $this->belongsTo(PlaceMedia::class, 'place_media_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

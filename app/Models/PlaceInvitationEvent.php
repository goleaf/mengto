<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class PlaceInvitationEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'place_invitation_id', 'actor_user_id', 'event_type', 'reason_code',
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
            throw new LogicException('Place invitation events are append-only.');
        });

        static::deleting(static function (): never {
            throw new LogicException('Place invitation events are immutable.');
        });
    }

    /** @return BelongsTo<PlaceInvitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(PlaceInvitation::class, 'place_invitation_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

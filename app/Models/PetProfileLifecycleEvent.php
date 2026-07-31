<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PetProfileLifecycleEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property string $actor_key_snapshot
 * @property string $event_type
 * @property int $id
 * @property CarbonImmutable $occurred_at
 */
final class PetProfileLifecycleEvent extends Model
{
    /** @use HasFactory<PetProfileLifecycleEventFactory> */
    use HasFactory;

    protected $fillable = [
        'pet_profile_id',
        'actor_user_id',
        'manager_id',
        'actor_key_snapshot',
        'actor_role_snapshot',
        'event_type',
        'from_status',
        'to_status',
        'reason_code',
        'reason_translation_key',
        'lock_version',
        'idempotency_key',
        'public_metadata',
        'private_metadata',
        'occurred_at',
    ];

    protected $hidden = ['private_metadata'];

    protected function casts(): array
    {
        return [
            'lock_version' => 'integer',
            'public_metadata' => 'array',
            'private_metadata' => 'encrypted:array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Pet profile lifecycle events are immutable.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Pet profile lifecycle events are append-only.');
        });
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<PetProfileManager, $this> */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(PetProfileManager::class, 'manager_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceCorrectionStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceCorrectionEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int|null $actor_user_id
 * @property CarbonImmutable $created_at
 * @property string $event_type
 * @property int $id
 * @property string|null $idempotency_key
 * @property array<string, mixed>|null $metadata
 * @property int $place_correction_id
 * @property string|null $private_note
 * @property string|null $public_summary_key
 * @property PlaceCorrectionStatus|null $from_status
 * @property PlaceCorrectionStatus|null $to_status
 */
final class PlaceCorrectionEvent extends Model
{
    /** @use HasFactory<PlaceCorrectionEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_correction_id',
        'actor_user_id',
        'idempotency_key',
        'event_type',
        'from_status',
        'to_status',
        'public_summary_key',
        'private_note',
        'metadata',
        'created_at',
    ];

    protected $hidden = ['idempotency_key', 'private_note', 'metadata'];

    protected function casts(): array
    {
        return [
            'from_status' => PlaceCorrectionStatus::class,
            'to_status' => PlaceCorrectionStatus::class,
            'private_note' => 'encrypted',
            'metadata' => 'encrypted:array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException('Place correction events are append-only.'));
        self::deleting(static fn (): never => throw new LogicException('Place correction events are append-only.'));
    }

    /** @return BelongsTo<PlaceCorrection, $this> */
    public function correction(): BelongsTo
    {
        return $this->belongsTo(PlaceCorrection::class, 'place_correction_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AdoptionEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int $adoption_case_id
 * @property int|null $adoption_application_id
 * @property int|null $actor_user_id
 * @property string|null $current_status
 * @property string $event_type
 * @property int $id
 * @property array<string, mixed>|null $metadata
 * @property string|null $previous_status
 * @property string $reason_translation_key
 */
final class AdoptionEvent extends Model
{
    /** @use HasFactory<AdoptionEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'adoption_case_id',
        'adoption_application_id',
        'actor_user_id',
        'event_type',
        'previous_status',
        'current_status',
        'reason_translation_key',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new LogicException('Adoption events are append-only.');
        });
        self::deleting(static function (): never {
            throw new LogicException('Adoption events are append-only.');
        });
    }

    /** @return BelongsTo<AdoptionCase, $this> */
    public function adoptionCase(): BelongsTo
    {
        return $this->belongsTo(AdoptionCase::class);
    }

    /** @return BelongsTo<AdoptionApplication, $this> */
    public function adoptionApplication(): BelongsTo
    {
        return $this->belongsTo(AdoptionApplication::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}

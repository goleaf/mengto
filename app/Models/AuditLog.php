<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $action
 * @property string $actor_key
 * @property string $actor_role
 * @property-read Booking|null $booking
 * @property int|null $booking_id
 * @property Carbon|null $created_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int|null $expert_profile_id
 * @property int $id
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $target_id
 * @property string $target_type
 * @property Carbon|null $updated_at
 */
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'booking_id', 'actor_key', 'actor_role', 'action',
        'target_type', 'target_id', 'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return BelongsTo<\App\Models\Booking, $this>*/
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

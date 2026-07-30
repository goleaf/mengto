<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DocumentGrantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read Booking|null $booking
 * @property int $booking_id
 * @property Carbon|null $created_at
 * @property string $document_type
 * @property Carbon|null $downloaded_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property Carbon $expires_at
 * @property string $file_path
 * @property int $id
 * @property string $label
 * @property Carbon|null $last_opened_at
 * @property string $owner_key
 * @property array<array-key, mixed>|null $permissions
 * @property Carbon|null $revoked_at
 * @property Carbon|null $updated_at
 */
class DocumentGrant extends Model
{
    /** @use HasFactory<DocumentGrantFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_id', 'expert_profile_id', 'owner_key', 'label', 'document_type',
        'file_path', 'permissions', 'expires_at', 'last_opened_at',
        'downloaded_at', 'revoked_at',
    ];

    protected $hidden = ['file_path'];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'expires_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'downloaded_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\Booking, $this>*/
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }
}

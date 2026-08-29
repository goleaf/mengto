<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AvailabilitySlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $booked_count
 * @property int $capacity
 * @property Carbon|null $created_at
 * @property Carbon $ends_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property string $format
 * @property int $id
 * @property string|null $location_label
 * @property-read Service|null $service
 * @property int|null $service_id
 * @property Carbon $starts_at
 * @property string $status
 * @property string $timezone
 * @property Carbon|null $updated_at
 */
class AvailabilitySlot extends Model
{
    /** @use HasFactory<AvailabilitySlotFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'service_id', 'starts_at', 'ends_at', 'timezone',
        'format', 'location_label', 'capacity', 'booked_count', 'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    /** @return BelongsTo<\App\Models\Service, $this>*/
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->where('status', 'open')
            ->whereColumn('booked_count', '<', 'capacity')
            ->where('starts_at', '>', now());
    }

    public function hasCapacity(): bool
    {
        return $this->status === 'open' && $this->booked_count < $this->capacity;
    }
}

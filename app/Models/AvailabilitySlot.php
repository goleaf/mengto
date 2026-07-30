<?php

namespace App\Models;

use Database\Factories\AvailabilitySlotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
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

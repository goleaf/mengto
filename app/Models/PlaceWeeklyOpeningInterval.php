<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceWeeklyOpeningIntervalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceWeeklyOpeningInterval extends Model
{
    /** @use HasFactory<PlaceWeeklyOpeningIntervalFactory> */
    use HasFactory;

    protected $fillable = [
        'place_operating_schedule_id',
        'iso_weekday',
        'starts_at_minute',
        'ends_at_minute',
        'is_appointment_only',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'iso_weekday' => 'integer',
            'starts_at_minute' => 'integer',
            'ends_at_minute' => 'integer',
            'is_appointment_only' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function getSpansNextDayAttribute(): bool
    {
        return $this->ends_at_minute > 1440;
    }

    /** @return BelongsTo<PlaceOperatingSchedule, $this> */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PlaceOperatingSchedule::class, 'place_operating_schedule_id');
    }
}

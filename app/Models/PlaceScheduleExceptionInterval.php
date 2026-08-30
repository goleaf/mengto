<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceScheduleExceptionIntervalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceScheduleExceptionInterval extends Model
{
    /** @use HasFactory<PlaceScheduleExceptionIntervalFactory> */
    use HasFactory;

    protected $fillable = [
        'place_schedule_exception_id',
        'starts_at_minute',
        'ends_at_minute',
        'is_appointment_only',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'starts_at_minute' => 'integer',
            'ends_at_minute' => 'integer',
            'is_appointment_only' => 'boolean',
            'position' => 'integer',
        ];
    }

    /** @return BelongsTo<PlaceScheduleException, $this> */
    public function exception(): BelongsTo
    {
        return $this->belongsTo(PlaceScheduleException::class, 'place_schedule_exception_id');
    }
}

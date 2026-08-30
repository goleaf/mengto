<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceScheduleExceptionKind;
use App\Enums\PlaceVerificationStatus;
use Database\Factories\PlaceScheduleExceptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PlaceScheduleException extends Model
{
    /** @use HasFactory<PlaceScheduleExceptionFactory> */
    use HasFactory;

    protected $fillable = [
        'place_operating_schedule_id',
        'stable_key',
        'local_date',
        'kind',
        'reason_code',
        'verification_status',
        'verification_source',
        'observed_at',
        'verified_at',
        'fresh_until',
    ];

    protected function casts(): array
    {
        return [
            'local_date' => 'immutable_date',
            'kind' => PlaceScheduleExceptionKind::class,
            'verification_status' => PlaceVerificationStatus::class,
            'observed_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
            'fresh_until' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<PlaceOperatingSchedule, $this> */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(PlaceOperatingSchedule::class, 'place_operating_schedule_id');
    }

    /** @return HasMany<PlaceScheduleExceptionInterval, $this> */
    public function intervals(): HasMany
    {
        return $this->hasMany(PlaceScheduleExceptionInterval::class)
            ->orderBy('starts_at_minute')
            ->orderBy('id');
    }
}

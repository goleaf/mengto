<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceScheduleCoverage;
use App\Enums\PlaceVerificationStatus;
use Database\Factories\PlaceOperatingScheduleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property PlaceScheduleCoverage $coverage_status
 * @property PlaceVerificationStatus $verification_status
 * @property-read Collection<int, PlaceScheduleException> $exceptions
 * @property-read Collection<int, PlaceWeeklyOpeningInterval> $weeklyIntervals
 */
final class PlaceOperatingSchedule extends Model
{
    /** @use HasFactory<PlaceOperatingScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'place_id',
        'timezone',
        'coverage_status',
        'verification_status',
        'verification_source',
        'observed_at',
        'verified_at',
        'fresh_until',
        'lock_version',
    ];

    protected $attributes = [
        'coverage_status' => 'partial',
        'verification_status' => 'not_assessed',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'coverage_status' => PlaceScheduleCoverage::class,
            'verification_status' => PlaceVerificationStatus::class,
            'observed_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
            'fresh_until' => 'immutable_datetime',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return HasMany<PlaceWeeklyOpeningInterval, $this> */
    public function weeklyIntervals(): HasMany
    {
        return $this->hasMany(PlaceWeeklyOpeningInterval::class)
            ->orderBy('iso_weekday')
            ->orderBy('starts_at_minute')
            ->orderBy('id');
    }

    /** @return HasMany<PlaceScheduleException, $this> */
    public function exceptions(): HasMany
    {
        return $this->hasMany(PlaceScheduleException::class)
            ->orderBy('local_date')
            ->orderBy('id');
    }
}

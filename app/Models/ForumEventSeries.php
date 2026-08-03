<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ForumEventRecurrenceFrequency;
use Carbon\CarbonImmutable;
use Database\Factories\ForumEventSeriesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property CarbonImmutable|null $ends_on
 * @property ForumEventRecurrenceFrequency $frequency
 * @property int $id
 * @property int $interval
 * @property bool $is_active
 * @property int|null $maximum_occurrences
 * @property string $name
 * @property int|null $owner_user_id
 * @property CarbonImmutable $starts_on
 * @property string $stable_key
 * @property string $timezone
 * @property list<int>|null $weekdays
 */
final class ForumEventSeries extends Model
{
    /** @use HasFactory<ForumEventSeriesFactory> */
    use HasFactory;

    protected $table = 'forum_event_series';

    protected $fillable = [
        'owner_user_id',
        'stable_key',
        'name',
        'frequency',
        'interval',
        'weekdays',
        'timezone',
        'starts_on',
        'ends_on',
        'maximum_occurrences',
        'is_active',
    ];

    protected $attributes = [
        'interval' => 1,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'frequency' => ForumEventRecurrenceFrequency::class,
            'interval' => 'integer',
            'weekdays' => 'array',
            'starts_on' => 'immutable_date',
            'ends_on' => 'immutable_date',
            'maximum_occurrences' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return HasMany<ForumEventOccurrence, $this> */
    public function occurrences(): HasMany
    {
        return $this->hasMany(ForumEventOccurrence::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SightingStatus;
use Database\Factories\SightingFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property string|null $animal_condition
 * @property string $confidence
 * @property string $contact_status
 * @property Carbon|null $created_at
 * @property string|null $danger
 * @property string|null $direction
 * @property string|null $distance
 * @property array<array-key, mixed>|null $exact_location
 * @property bool $exact_location_public
 * @property int $id
 * @property string $idempotency_key
 * @property bool $is_anonymous
 * @property string|null $notes
 * @property Carbon $observed_at
 * @property string|null $photo_url
 * @property string $public_area
 * @property numeric-string|null $public_latitude
 * @property numeric-string|null $public_longitude
 * @property int|null $reporter_id
 * @property string $reporter_key
 * @property string $reporter_name
 * @property-read Collection<int, SearchReport> $reports
 * @property array<array-key, mixed>|null $risk_flags
 * @property-read SearchCase|null $searchCase
 * @property int $search_case_id
 * @property SightingStatus $status
 * @property Carbon $submitted_at
 * @property string $time_accuracy
 * @property Carbon|null $updated_at
 * @property Carbon|null $verified_at
 * @property string|null $verified_by_key
 * @property string|null $video_url
 */
class Sighting extends Model
{
    /** @use HasFactory<SightingFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn (): bool => SearchCase::invalidateDirectoryStats());
        static::deleted(fn (): bool => SearchCase::invalidateDirectoryStats());
    }

    protected $fillable = [
        'search_case_id', 'reporter_id', 'reporter_key', 'reporter_name',
        'idempotency_key', 'status', 'observed_at', 'submitted_at',
        'time_accuracy', 'public_area', 'public_latitude', 'public_longitude',
        'exact_location', 'direction', 'distance', 'confidence',
        'contact_status', 'animal_condition', 'danger', 'notes', 'photo_url',
        'video_url', 'is_anonymous', 'exact_location_public', 'risk_flags',
        'verified_by_key', 'verified_at',
    ];

    protected $hidden = ['exact_location'];

    protected $attributes = [
        'status' => 'submitted',
        'time_accuracy' => 'exact',
        'confidence' => 'possible',
        'contact_status' => 'seen-only',
        'is_anonymous' => false,
        'exact_location_public' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => SightingStatus::class,
            'observed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'public_latitude' => 'decimal:6',
            'public_longitude' => 'decimal:6',
            'exact_location' => 'encrypted:array',
            'is_anonymous' => 'boolean',
            'exact_location_public' => 'boolean',
            'risk_flags' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<\App\Models\SearchCase, $this>*/
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** @return HasMany<\App\Models\SearchReport, $this>*/
    public function reports(): HasMany
    {
        return $this->hasMany(SearchReport::class);
    }

    /** @return MorphMany<ForumReport, $this> */
    public function subjectReports(): MorphMany
    {
        return $this->morphMany(ForumReport::class, 'subject');
    }
}

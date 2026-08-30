<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceCorrectionField;
use App\Enums\PlaceCorrectionResolution;
use App\Enums\PlaceCorrectionSource;
use App\Enums\PlaceCorrectionStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PlaceCorrectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $applied_by_user_id
 * @property CarbonImmutable|null $applied_at
 * @property string|null $applied_value
 * @property PlaceCorrectionField $correction_field
 * @property string|null $decision_reason
 * @property string|null $evidence
 * @property string $explanation
 * @property int $id
 * @property string $idempotency_key
 * @property int $lock_version
 * @property CarbonImmutable|null $observed_at
 * @property int $original_version
 * @property string|null $original_value
 * @property int $place_id
 * @property string|null $proposed_value
 * @property PlaceCorrectionResolution|null $resolution
 * @property int|null $reviewer_user_id
 * @property CarbonImmutable|null $reviewed_at
 * @property PlaceCorrectionSource $source
 * @property string $stable_key
 * @property PlaceCorrectionStatus $moderation_status
 * @property int $submitter_user_id
 * @property-read Place $place
 * @property-read User $submitter
 * @property-read User|null $reviewer
 * @property-read User|null $appliedBy
 * @property-read Collection<int, PlaceCorrectionEvent> $events
 */
final class PlaceCorrection extends Model
{
    /** @use HasFactory<PlaceCorrectionFactory> */
    use HasFactory;

    protected $fillable = [
        'place_id',
        'submitter_user_id',
        'reviewer_user_id',
        'applied_by_user_id',
        'stable_key',
        'idempotency_key',
        'correction_field',
        'original_value',
        'original_version',
        'proposed_value',
        'explanation',
        'evidence',
        'source',
        'observed_at',
        'moderation_status',
        'resolution',
        'decision_reason',
        'applied_value',
        'reviewed_at',
        'applied_at',
        'lock_version',
        'pending_fingerprint',
    ];

    protected $hidden = ['idempotency_key', 'pending_fingerprint', 'evidence', 'decision_reason'];

    protected $attributes = [
        'moderation_status' => 'pending',
        'lock_version' => 0,
    ];

    protected function casts(): array
    {
        return [
            'correction_field' => PlaceCorrectionField::class,
            'source' => PlaceCorrectionSource::class,
            'moderation_status' => PlaceCorrectionStatus::class,
            'resolution' => PlaceCorrectionResolution::class,
            'evidence' => 'encrypted',
            'decision_reason' => 'encrypted',
            'observed_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'applied_at' => 'immutable_datetime',
            'lock_version' => 'integer',
            'original_version' => 'integer',
        ];
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_user_id');
    }

    /** @return HasMany<PlaceCorrectionEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(PlaceCorrectionEvent::class);
    }

    /** @return MorphMany<ForumReport, $this> */
    public function reports(): MorphMany
    {
        return $this->morphMany(ForumReport::class, 'subject');
    }

    /** @param Builder<PlaceCorrection> $query @return Builder<PlaceCorrection> */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('moderation_status', [
            PlaceCorrectionStatus::Pending->value,
            PlaceCorrectionStatus::InReview->value,
            PlaceCorrectionStatus::NeedsInformation->value,
        ]);
    }
}

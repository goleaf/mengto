<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceFactScope;
use App\Enums\PlaceSubmissionSource;
use Database\Factories\PlaceFactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class PlaceFact extends Model
{
    /** @use HasFactory<PlaceFactFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_submission_id',
        'place_submission_revision_id',
        'place_id',
        'origin_place_id',
        'copied_from_fact_id',
        'submitted_by_user_id',
        'reviewed_by_user_id',
        'stable_key',
        'field_key',
        'field_value',
        'value_hash',
        'source_kind',
        'source_reference',
        'provenance_scope',
        'visibility_scope',
        'observed_at',
        'verified_at',
        'created_at',
    ];

    protected $hidden = ['field_value', 'source_reference'];

    protected function casts(): array
    {
        return [
            'field_value' => 'encrypted',
            'source_reference' => 'encrypted',
            'source_kind' => PlaceSubmissionSource::class,
            'provenance_scope' => PlaceFactScope::class,
            'observed_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException('Place facts are append-only.'));
        self::deleting(static fn (): never => throw new LogicException('Place facts are append-only.'));
    }

    /** @return BelongsTo<PlaceSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(PlaceSubmission::class, 'place_submission_id');
    }

    /** @return BelongsTo<PlaceSubmissionRevision, $this> */
    public function submissionRevision(): BelongsTo
    {
        return $this->belongsTo(PlaceSubmissionRevision::class, 'place_submission_revision_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /** @return BelongsTo<Place, $this> */
    public function originPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'origin_place_id');
    }

    /** @return BelongsTo<self, $this> */
    public function copiedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'copied_from_fact_id');
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}

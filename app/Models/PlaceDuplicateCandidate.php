<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceDuplicateConfidence;
use Database\Factories\PlaceDuplicateCandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * @property int $id
 * @property int $place_submission_id
 * @property int|null $candidate_place_id
 * @property int|null $candidate_submission_id
 * @property string $candidate_key
 * @property int $score
 * @property PlaceDuplicateConfidence $confidence
 * @property list<string> $matched_signals
 * @property int|null $distance_meters
 * @property string $presentation_scope
 * @property-read PlaceSubmission $submission
 * @property-read Place|null $candidatePlace
 * @property-read PlaceSubmission|null $candidateSubmission
 */
final class PlaceDuplicateCandidate extends Model
{
    /** @use HasFactory<PlaceDuplicateCandidateFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_submission_id',
        'candidate_place_id',
        'candidate_submission_id',
        'candidate_key',
        'algorithm_version',
        'signals_fingerprint',
        'score',
        'confidence',
        'matched_signals',
        'distance_meters',
        'presentation_scope',
        'created_at',
    ];

    protected $hidden = [
        'candidate_place_id',
        'candidate_submission_id',
        'candidate_key',
        'matched_signals',
        'signals_fingerprint',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'confidence' => PlaceDuplicateConfidence::class,
            'matched_signals' => 'array',
            'distance_meters' => 'integer',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::creating(static function (self $candidate): void {
            $targets = (int) ($candidate->candidate_place_id !== null)
                + (int) ($candidate->candidate_submission_id !== null);

            if ($targets !== 1
                || ($candidate->candidate_submission_id !== null
                    && $candidate->candidate_submission_id === $candidate->place_submission_id)) {
                throw new LogicException('A duplicate candidate must reference exactly one different target.');
            }
        });
        self::updating(static fn (): never => throw new LogicException('Place duplicate candidates are immutable.'));
        self::deleting(static fn (): never => throw new LogicException('Place duplicate candidates are immutable.'));
    }

    /** @return BelongsTo<PlaceSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(PlaceSubmission::class, 'place_submission_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function candidatePlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'candidate_place_id');
    }

    /** @return BelongsTo<PlaceSubmission, $this> */
    public function candidateSubmission(): BelongsTo
    {
        return $this->belongsTo(PlaceSubmission::class, 'candidate_submission_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PlaceSubmissionAction;
use App\Enums\PlaceSubmissionStatus;
use Database\Factories\PlaceSubmissionEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class PlaceSubmissionEvent extends Model
{
    /** @use HasFactory<PlaceSubmissionEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_submission_id',
        'actor_user_id',
        'place_duplicate_candidate_id',
        'candidate_place_id',
        'destination_place_id',
        'idempotency_key',
        'action',
        'from_status',
        'to_status',
        'reason_code',
        'reason_detail',
        'payload_fingerprint',
        'expected_lock_version',
        'result_lock_version',
        'audit_context',
        'created_at',
    ];

    protected $hidden = ['idempotency_key', 'reason_detail', 'payload_fingerprint', 'audit_context'];

    protected function casts(): array
    {
        return [
            'action' => PlaceSubmissionAction::class,
            'from_status' => PlaceSubmissionStatus::class,
            'to_status' => PlaceSubmissionStatus::class,
            'reason_detail' => 'encrypted',
            'audit_context' => 'encrypted:array',
            'expected_lock_version' => 'integer',
            'result_lock_version' => 'integer',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException('Place submission events are immutable.'));
        self::deleting(static fn (): never => throw new LogicException('Place submission events are immutable.'));
    }

    /** @return BelongsTo<PlaceSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(PlaceSubmission::class, 'place_submission_id');
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function candidatePlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'candidate_place_id');
    }

    /** @return BelongsTo<PlaceDuplicateCandidate, $this> */
    public function duplicateCandidate(): BelongsTo
    {
        return $this->belongsTo(PlaceDuplicateCandidate::class, 'place_duplicate_candidate_id');
    }

    /** @return BelongsTo<Place, $this> */
    public function destinationPlace(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'destination_place_id');
    }
}

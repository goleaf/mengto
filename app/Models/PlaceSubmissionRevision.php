<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceSubmissionRevisionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

final class PlaceSubmissionRevision extends Model
{
    /** @use HasFactory<PlaceSubmissionRevisionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'place_submission_id',
        'submitted_by_user_id',
        'stable_key',
        'revision_number',
        'kind',
        'summary',
        'created_at',
    ];

    protected $hidden = ['summary'];

    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'summary' => 'encrypted',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static fn (): never => throw new LogicException('Place submission revisions are immutable.'));
        self::deleting(static fn (): never => throw new LogicException('Place submission revisions are immutable.'));
    }

    /** @return BelongsTo<PlaceSubmission, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(PlaceSubmission::class, 'place_submission_id');
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /** @return HasMany<PlaceFact, $this> */
    public function facts(): HasMany
    {
        return $this->hasMany(PlaceFact::class, 'place_submission_revision_id')->whereNull('place_id');
    }
}

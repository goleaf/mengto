<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlaceSubmissionIdentityLockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaceSubmissionIdentityLock extends Model
{
    /** @use HasFactory<PlaceSubmissionIdentityLockFactory> */
    use HasFactory;

    protected $table = 'place_submission_identity_locks';

    protected $primaryKey = 'identity_hash';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['identity_hash', 'first_submission_id', 'lock_version'];

    protected $hidden = ['identity_hash'];

    protected function casts(): array
    {
        return ['lock_version' => 'integer'];
    }

    /** @return BelongsTo<PlaceSubmission, $this> */
    public function firstSubmission(): BelongsTo
    {
        return $this->belongsTo(PlaceSubmission::class, 'first_submission_id');
    }
}

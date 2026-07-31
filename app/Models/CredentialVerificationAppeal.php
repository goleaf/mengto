<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CredentialVerificationAppealFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CredentialVerificationAppeal extends Model
{
    /** @use HasFactory<CredentialVerificationAppealFactory> */
    use HasFactory;

    protected $fillable = [
        'credential_id',
        'submitted_by_user_id',
        'reviewer_user_id',
        'status',
        'statement',
        'reviewer_response',
        'reviewed_at',
        'closed_at',
        'metadata',
    ];

    protected $hidden = [
        'statement',
        'reviewer_response',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Credential, $this> */
    public function credential(): BelongsTo
    {
        return $this->belongsTo(Credential::class);
    }

    /** @return BelongsTo<User, $this> */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}

<?php

namespace App\Models;

use App\Enums\CredentialStatus;
use Database\Factories\CredentialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credential extends Model
{
    /** @use HasFactory<CredentialFactory> */
    use HasFactory;

    protected $fillable = [
        'expert_profile_id', 'type', 'title', 'issuer', 'region',
        'number_last_four', 'issued_at', 'expires_at', 'status', 'file_path',
        'reviewed_by', 'verified_at', 'rejection_reason', 'verification_notes',
    ];

    protected $hidden = ['file_path', 'verification_notes'];

    protected function casts(): array
    {
        return [
            'status' => CredentialStatus::class,
            'issued_at' => 'date',
            'expires_at' => 'date',
            'verified_at' => 'datetime',
            'verification_notes' => 'array',
        ];
    }

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }
}

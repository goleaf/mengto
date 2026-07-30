<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CredentialStatus;
use Database\Factories\CredentialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property-read ExpertProfile|null $expertProfile
 * @property int $expert_profile_id
 * @property Carbon|null $expires_at
 * @property string|null $file_path
 * @property int $id
 * @property Carbon|null $issued_at
 * @property string $issuer
 * @property string|null $number_last_four
 * @property string|null $region
 * @property string|null $rejection_reason
 * @property string|null $reviewed_by
 * @property CredentialStatus $status
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 * @property array<array-key, mixed>|null $verification_notes
 * @property Carbon|null $verified_at
 */
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

    /** @return BelongsTo<\App\Models\ExpertProfile, $this>*/
    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }
}

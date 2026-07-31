<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdoptionApplicationStatus;
use App\Enums\AdoptionPlacementType;
use Database\Factories\AdoptionApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $adoption_case_id
 * @property int $applicant_user_id
 * @property Carbon|null $closed_at
 * @property array<string, mixed>|null $contract_metadata
 * @property Carbon|null $follow_up_at
 * @property string $idempotency_key
 * @property string $identity_status
 * @property int $id
 * @property int $lock_version
 * @property Carbon|null $meeting_at
 * @property string $message
 * @property AdoptionPlacementType $placement_type
 * @property array<string, string> $private_profile
 * @property array<int, array<string, string>>|null $private_references
 * @property bool $privacy_accepted
 * @property bool $reference_contact_consent
 * @property Carbon|null $reserved_at
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewer_user_id
 * @property string|null $screening_notes
 * @property AdoptionApplicationStatus $status
 * @property Carbon $submitted_at
 * @property bool $terms_accepted
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $trial_started_at
 * @property-read AdoptionCase $adoptionCase
 * @property-read User $applicant
 */
final class AdoptionApplication extends Model
{
    /** @use HasFactory<AdoptionApplicationFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'submitted',
        'identity_status' => 'unverified',
        'terms_accepted' => false,
        'privacy_accepted' => false,
        'reference_contact_consent' => false,
        'lock_version' => 1,
    ];

    protected $fillable = [
        'adoption_case_id',
        'applicant_user_id',
        'reviewer_user_id',
        'idempotency_key',
        'placement_type',
        'status',
        'identity_status',
        'message',
        'private_profile',
        'private_references',
        'screening_notes',
        'home_check_notes',
        'contract_metadata',
        'terms_accepted',
        'privacy_accepted',
        'reference_contact_consent',
        'lock_version',
        'submitted_at',
        'reviewed_at',
        'meeting_at',
        'reserved_at',
        'contracted_at',
        'trial_started_at',
        'trial_ends_at',
        'follow_up_at',
        'closed_at',
    ];

    protected $hidden = [
        'private_profile',
        'private_references',
        'screening_notes',
        'home_check_notes',
        'contract_metadata',
    ];

    protected function casts(): array
    {
        return [
            'placement_type' => AdoptionPlacementType::class,
            'status' => AdoptionApplicationStatus::class,
            'private_profile' => 'encrypted:array',
            'private_references' => 'encrypted:array',
            'screening_notes' => 'encrypted',
            'home_check_notes' => 'encrypted',
            'contract_metadata' => 'encrypted:array',
            'terms_accepted' => 'boolean',
            'privacy_accepted' => 'boolean',
            'reference_contact_consent' => 'boolean',
            'lock_version' => 'integer',
            'submitted_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'meeting_at' => 'immutable_datetime',
            'reserved_at' => 'immutable_datetime',
            'contracted_at' => 'immutable_datetime',
            'trial_started_at' => 'immutable_datetime',
            'trial_ends_at' => 'immutable_datetime',
            'follow_up_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<AdoptionCase, $this> */
    public function adoptionCase(): BelongsTo
    {
        return $this->belongsTo(AdoptionCase::class);
    }

    /** @return BelongsTo<User, $this> */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    /** @return HasMany<AdoptionEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(AdoptionEvent::class);
    }
}

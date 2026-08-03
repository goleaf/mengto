<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PetManagerRole;
use App\Enums\PetProfileAccessRequestStatus;
use App\Enums\PetProfileAccessRequestType;
use Database\Factories\PetProfileAccessRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string|null $active_key
 * @property string|null $decision_key
 * @property string $evidence_summary
 * @property int|null $granted_manager_id
 * @property int $id
 * @property int $lock_version
 * @property int $pet_profile_id
 * @property string $request_key
 * @property PetProfileAccessRequestType $request_type
 * @property string $requester_actor_key_snapshot
 * @property int|null $requester_user_id
 * @property PetManagerRole $requested_role
 * @property string|null $resolution_note
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by_user_id
 * @property PetProfileAccessRequestStatus $status
 * @property Carbon|null $temporary_access_ends_at
 * @property-read PetProfileManager|null $grantedManager
 * @property-read PetProfile $profile
 * @property-read User|null $requester
 * @property-read User|null $reviewer
 */
final class PetProfileAccessRequest extends Model
{
    /** @use HasFactory<PetProfileAccessRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'request_key',
        'pet_profile_id',
        'requester_user_id',
        'requester_actor_key_snapshot',
        'request_type',
        'requested_role',
        'status',
        'evidence_summary',
        'temporary_access_ends_at',
        'active_key',
        'submission_key',
        'decision_key',
        'reviewed_by_user_id',
        'reviewed_at',
        'granted_manager_id',
        'resolution_note',
        'lock_version',
    ];

    protected $hidden = [
        'active_key',
        'decision_key',
        'evidence_summary',
        'resolution_note',
        'submission_key',
    ];

    protected function casts(): array
    {
        return [
            'request_type' => PetProfileAccessRequestType::class,
            'requested_role' => PetManagerRole::class,
            'status' => PetProfileAccessRequestStatus::class,
            'evidence_summary' => 'encrypted',
            'temporary_access_ends_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'resolution_note' => 'encrypted',
            'lock_version' => 'integer',
        ];
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /** @return BelongsTo<PetProfileManager, $this> */
    public function grantedManager(): BelongsTo
    {
        return $this->belongsTo(PetProfileManager::class, 'granted_manager_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PetEvidenceStatus;
use App\Enums\PetManagerRole;
use App\Enums\PetManagerStatus;
use App\Enums\PetProfilePermission;
use Database\Factories\PetProfileManagerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $accepted_at
 * @property string $actor_key_snapshot
 * @property Carbon|null $ends_at
 * @property PetEvidenceStatus $evidence_status
 * @property int $id
 * @property PetManagerRole $role
 * @property PetManagerStatus $status
 * @property array<string, list<string>>|null $permission_overrides
 * @property int $pet_profile_id
 * @property Carbon|null $revoked_at
 * @property Carbon|null $starts_at
 * @property int|null $user_id
 * @property int $lock_version
 * @property-read User|null $inviter
 * @property-read PetProfile $profile
 * @property-read User|null $user
 */
final class PetProfileManager extends Model
{
    /** @use HasFactory<PetProfileManagerFactory> */
    use HasFactory;

    protected $fillable = [
        'pet_profile_id',
        'user_id',
        'actor_key_snapshot',
        'role',
        'status',
        'permission_overrides',
        'evidence_status',
        'evidence_summary',
        'starts_at',
        'ends_at',
        'accepted_at',
        'revoked_at',
        'invited_by_user_id',
        'revoked_by_user_id',
        'lock_version',
        'metadata',
    ];

    protected $hidden = ['evidence_summary', 'metadata'];

    protected function casts(): array
    {
        return [
            'role' => PetManagerRole::class,
            'status' => PetManagerStatus::class,
            'permission_overrides' => 'array',
            'evidence_status' => PetEvidenceStatus::class,
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'accepted_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'lock_version' => 'integer',
            'metadata' => 'encrypted:array',
        ];
    }

    /** @return BelongsTo<PetProfile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(PetProfile::class, 'pet_profile_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    /** @return HasMany<PetProfileAccessRequest, $this> */
    public function grantedAccessRequests(): HasMany
    {
        return $this->hasMany(PetProfileAccessRequest::class, 'granted_manager_id');
    }

    /** @return HasMany<PetProfileLifecycleEvent, $this> */
    public function lifecycleEvents(): HasMany
    {
        return $this->hasMany(PetProfileLifecycleEvent::class, 'manager_id');
    }

    public function scopeActiveAt(Builder $query, Carbon $at): Builder
    {
        return self::constrainActiveAt($query, $at);
    }

    public static function constrainActiveAt(Builder $query, Carbon $at): Builder
    {
        return $query
            ->where('status', PetManagerStatus::Active)
            ->where(function (Builder $starts) use ($at): void {
                $starts->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
            })
            ->where(function (Builder $ends) use ($at): void {
                $ends->whereNull('ends_at')->orWhere('ends_at', '>', $at);
            })
            ->whereNull('revoked_at');
    }

    public function isActiveAt(Carbon $at): bool
    {
        return $this->status === PetManagerStatus::Active
            && $this->revoked_at === null
            && ($this->starts_at === null || $this->starts_at->lessThanOrEqualTo($at))
            && ($this->ends_at === null || $this->ends_at->isAfter($at));
    }

    public function allows(PetProfilePermission $permission, ?Carbon $at = null): bool
    {
        if (! $this->isActiveAt($at ?? now())) {
            return false;
        }

        $overrides = $this->permission_overrides ?? [];
        $denied = array_values($overrides['deny'] ?? []);

        if (in_array($permission->value, $denied, true)) {
            return false;
        }

        $granted = array_values($overrides['grant'] ?? []);

        return in_array($permission->value, $granted, true)
            || in_array($permission, $this->role->defaultPermissions(), true);
    }

    public function canRepresentAtMeetup(?Carbon $at = null): bool
    {
        return $this->allows(PetProfilePermission::ManageCare, $at)
            || $this->allows(PetProfilePermission::ManageSocial, $at);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationInvitationStatus;
use App\Enums\OrganizationRole;
use Carbon\CarbonImmutable;
use Database\Factories\OrganizationInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

/**
 * @property CarbonImmutable $expires_at
 * @property int $id
 * @property string $idempotency_key
 * @property int|null $invited_by_user_id
 * @property int $invited_user_id
 * @property int $organization_id
 * @property CarbonImmutable|null $responded_at
 * @property CarbonImmutable|null $revoked_at
 * @property int|null $revoked_by_user_id
 * @property OrganizationRole $role
 * @property string $stable_key
 * @property OrganizationInvitationStatus $status
 * @property string $token_hash
 * @property-read Organization $organization
 */
final class OrganizationInvitation extends Model
{
    /** @use HasFactory<OrganizationInvitationFactory> */
    use HasFactory;

    public ?string $plainTextToken = null;

    protected $fillable = [
        'organization_id',
        'invited_user_id',
        'invited_by_user_id',
        'stable_key',
        'idempotency_key',
        'token_hash',
        'role',
        'status',
        'expires_at',
        'responded_at',
        'revoked_by_user_id',
        'revoked_at',
    ];

    protected $hidden = ['idempotency_key', 'token_hash'];

    protected $attributes = ['status' => 'pending'];

    protected function casts(): array
    {
        return [
            'role' => OrganizationRole::class,
            'status' => OrganizationInvitationStatus::class,
            'expires_at' => 'immutable_datetime',
            'responded_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'stable_key';
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
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

    public function isCurrent(): bool
    {
        return $this->status === OrganizationInvitationStatus::Pending
            && $this->revoked_at === null
            && $this->expires_at->isFuture();
    }

    public function tokenMatches(string $token): bool
    {
        return hash_equals($this->token_hash, hash('sha256', $token));
    }

    public function signedResponseUrl(string $token): string
    {
        return URL::temporarySignedRoute(
            'organizations.invitations.respond',
            $this->expires_at,
            ['organizationInvitation' => $this, 'token' => $token],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CredentialStatus;
use App\Enums\ForumMentorshipState;
use App\Models\Credential;
use App\Models\ForumBlock;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorship;
use App\Models\ForumUserTrustLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class MentorshipEligibility
{
    private const MENTOR_TRUST_LEVELS = [
        'mentor',
        'community-reviewer',
        'category-steward',
        'moderator',
        'senior-moderator',
        'administrator',
    ];

    public function canActivateProfile(User $user): bool
    {
        return $user->isActive()
            && $user->hasVerifiedEmail()
            && ($user->isAdministrator() || $this->hasMentorTrust($user));
    }

    public function hasMentorTrust(User $user): bool
    {
        return ForumUserTrustLevel::query()
            ->where('user_id', $user->id)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->whereHas('level', function ($query): void {
                $query
                    ->whereIn('stable_key', self::MENTOR_TRUST_LEVELS)
                    ->where('is_active', true);
            })
            ->exists();
    }

    public function hasCurrentProfessionalVerification(User $user): bool
    {
        return in_array($user->id, $this->professionallyVerifiedUserIds([$user->id]), true);
    }

    /**
     * @param  list<int>  $userIds
     * @return list<int>
     */
    public function professionallyVerifiedUserIds(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        /** @var Collection<int, Credential> $credentials */
        $credentials = Credential::query()
            ->select([
                'id',
                'expert_profile_id',
                'type',
                'status',
                'expires_at',
                'renewal_due_at',
                'suspended_at',
                'revoked_at',
            ])
            ->with('expertProfile:id,owner_id')
            ->whereHas('expertProfile', fn ($query) => $query->whereIn('owner_id', $userIds))
            ->whereIn('status', [
                CredentialStatus::Verified->value,
                CredentialStatus::Expiring->value,
            ])
            ->get();

        return $credentials
            ->filter(fn (Credential $credential): bool => in_array(
                $credential->effectiveStatus(),
                [CredentialStatus::Verified, CredentialStatus::Expiring],
                true,
            ))
            ->pluck('expertProfile.owner_id')
            ->filter(static fn (mixed $id): bool => is_int($id))
            ->unique()
            ->values()
            ->all();
    }

    public function usersBlockEachOther(User $first, User $second): bool
    {
        return ForumBlock::query()
            ->where(function ($query) use ($first, $second): void {
                $query
                    ->where('user_key', $first->actor_key)
                    ->where('blocked_author_key', $second->actor_key);
            })
            ->orWhere(function ($query) use ($first, $second): void {
                $query
                    ->where('user_key', $second->actor_key)
                    ->where('blocked_author_key', $first->actor_key);
            })
            ->exists();
    }

    public function profileHasRequestCapacity(ForumMentorProfile $profile): bool
    {
        return ForumMentorship::query()
            ->where('mentor_user_id', $profile->user_id)
            ->whereIn('state', [
                ForumMentorshipState::Requested->value,
                ForumMentorshipState::Active->value,
            ])
            ->count() < $profile->capacity;
    }

    public function profileHasActiveCapacity(ForumMentorProfile $profile): bool
    {
        return ForumMentorship::query()
            ->where('mentor_user_id', $profile->user_id)
            ->where('state', ForumMentorshipState::Active->value)
            ->count() < $profile->capacity;
    }
}

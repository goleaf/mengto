<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumPollEligibility as ForumPollEligibilityType;
use App\Models\ForumGroup;
use App\Models\ForumPoll;
use App\Models\ForumUserTrustLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class ForumPollEligibility
{
    private const TRUSTED_LEVELS = [
        'established-member',
        'trusted-contributor',
        'mentor',
        'community-reviewer',
        'category-steward',
    ];

    public function allows(User $user, ForumPoll $poll): bool
    {
        if (! $user->isActive() || ! $this->hasActiveMembership($user, $poll)) {
            return false;
        }

        $group = $poll->relationLoaded('group')
            ? $poll->group
            : $poll->group()->first();

        if (! $group instanceof ForumGroup) {
            return false;
        }

        return $this->allowsWithinAuthorizedGroup(
            $user,
            $poll,
            $group,
            $this->hasTrustedCommunityAssignment($user),
        );
    }

    public function allowsWithinAuthorizedGroup(
        User $user,
        ForumPoll $poll,
        ForumGroup $group,
        bool $hasTrustedCommunityAssignment,
    ): bool {
        if (! $user->isActive() || $poll->forum_group_id !== $group->id) {
            return false;
        }

        return match ($poll->eligibility) {
            ForumPollEligibilityType::GroupMembers => true,
            ForumPollEligibilityType::TrustedMembers => $hasTrustedCommunityAssignment,
            ForumPollEligibilityType::LocationMembers => filled($poll->location_scope)
                && $poll->location_scope === $group->location_scope,
        };
    }

    public function denialTranslationKey(ForumPoll $poll): string
    {
        return match ($poll->eligibility) {
            ForumPollEligibilityType::TrustedMembers => 'forum_polls.validation.trusted_required',
            ForumPollEligibilityType::LocationMembers => 'forum_polls.validation.location_required',
            ForumPollEligibilityType::GroupMembers => 'forum_polls.notices.member_only',
        };
    }

    private function hasActiveMembership(User $user, ForumPoll $poll): bool
    {
        return $poll->group()
            ->whereHas('memberships', function (Builder $membership) use ($user): void {
                $membership
                    ->where('user_id', $user->id)
                    ->where('state', ForumGroupMembershipState::Active->value);
            })
            ->exists();
    }

    public function hasTrustedCommunityAssignment(User $user): bool
    {
        return ForumUserTrustLevel::query()
            ->where('user_id', $user->id)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas(
                'level',
                fn (Builder $query): Builder => $query
                    ->where('is_active', true)
                    ->whereIn('stable_key', self::TRUSTED_LEVELS),
            )
            ->exists();
    }
}

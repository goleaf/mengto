<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Enums\ForumGroupStatus;
use App\Enums\ForumGroupVisibility;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\User;

final class ForumGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, ForumGroup $group): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($this->managesPlatform($user) || $group->owner_user_id === $user->id) {
            return true;
        }

        if ($group->status === ForumGroupStatus::Archived) {
            return false;
        }

        if (in_array($group->visibility, [
            ForumGroupVisibility::Public,
            ForumGroupVisibility::RequestToJoin,
            ForumGroupVisibility::Unlisted,
        ], true)) {
            return true;
        }

        if ($group->hasActiveMembership($user)) {
            return true;
        }

        return $group->invitations()
            ->where('invited_user_id', $user->id)
            ->where('state', 'pending')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function viewMemberContent(User $user, ForumGroup $group): bool
    {
        return $user->isActive()
            && ($this->managesPlatform($user)
                || $group->owner_user_id === $user->id
                || $group->hasActiveMembership($user));
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->hasVerifiedEmail();
    }

    public function update(User $user, ForumGroup $group): bool
    {
        $role = $this->role($user, $group);

        return $this->managesPlatform($user)
            || in_array($role, [
                ForumGroupRole::Owner,
                ForumGroupRole::Administrator,
            ], true);
    }

    public function requestMembership(User $user, ForumGroup $group): bool
    {
        if (! $user->isActive()
            || ! $user->hasVerifiedEmail()
            || $group->status !== ForumGroupStatus::Active
            || $group->owner_user_id === $user->id
            || $group->visibility === ForumGroupVisibility::Private
        ) {
            return false;
        }

        $membership = $group->membershipFor($user);

        return $membership === null
            || ! in_array($membership->state, [
                ForumGroupMembershipState::Active,
                ForumGroupMembershipState::Pending,
                ForumGroupMembershipState::Banned,
            ], true);
    }

    public function invite(User $user, ForumGroup $group): bool
    {
        return $user->isActive()
            && ($this->managesPlatform($user)
                || $this->role($user, $group)?->canInvite() === true);
    }

    public function reviewMembership(User $user, ForumGroup $group): bool
    {
        return $user->isActive()
            && ($this->managesPlatform($user)
                || $this->role($user, $group)?->canManageMembership() === true);
    }

    public function manageMember(
        User $user,
        ForumGroup $group,
        ForumGroupMembership $membership,
    ): bool {
        if (! $user->isActive()
            || $membership->forum_group_id !== $group->id
            || $membership->role === ForumGroupRole::Owner
        ) {
            return false;
        }

        return $this->managesPlatform($user)
            || $this->role($user, $group)?->canManageMembership() === true;
    }

    public function transferOwnership(User $user, ForumGroup $group): bool
    {
        return $user->isActive() && $group->owner_user_id === $user->id;
    }

    public function close(User $user, ForumGroup $group): bool
    {
        return $user->isActive()
            && ($this->managesPlatform($user)
                || in_array($this->role($user, $group), [
                    ForumGroupRole::Owner,
                    ForumGroupRole::Administrator,
                ], true));
    }

    public function archive(User $user, ForumGroup $group): bool
    {
        return $user->isActive()
            && ($this->managesPlatform($user)
                || $group->owner_user_id === $user->id);
    }

    public function report(User $user, ForumGroup $group): bool
    {
        return $this->view($user, $group);
    }

    public function viewAudit(User $user, ForumGroup $group): bool
    {
        return $this->reviewMembership($user, $group);
    }

    public function createContent(User $user, ForumGroup $group): bool
    {
        if (! $user->isActive() || $group->status !== ForumGroupStatus::Active) {
            return false;
        }

        if ($this->managesPlatform($user) || $group->owner_user_id === $user->id) {
            return true;
        }

        $role = $this->role($user, $group);

        return $role !== null && $role !== ForumGroupRole::RestrictedMember;
    }

    public function manageContent(User $user, ForumGroup $group): bool
    {
        if (! $user->isActive() || $group->status !== ForumGroupStatus::Active) {
            return false;
        }

        return $this->managesPlatform($user)
            || in_array($this->role($user, $group), [
                ForumGroupRole::Owner,
                ForumGroupRole::Administrator,
                ForumGroupRole::Moderator,
                ForumGroupRole::Steward,
            ], true);
    }

    public function publishAnnouncement(User $user, ForumGroup $group): bool
    {
        if (! $user->isActive() || $group->status !== ForumGroupStatus::Active) {
            return false;
        }

        return $this->managesPlatform($user)
            || in_array($this->role($user, $group), [
                ForumGroupRole::Owner,
                ForumGroupRole::Administrator,
                ForumGroupRole::Moderator,
            ], true);
    }

    public function uploadFile(User $user, ForumGroup $group): bool
    {
        return $this->createContent($user, $group);
    }

    public function createPoll(User $user, ForumGroup $group): bool
    {
        return $this->createContent($user, $group);
    }

    private function role(User $user, ForumGroup $group): ?ForumGroupRole
    {
        if ($group->owner_user_id === $user->id) {
            return ForumGroupRole::Owner;
        }

        $role = $group->memberships()
            ->where('user_id', $user->id)
            ->where('state', ForumGroupMembershipState::Active->value)
            ->value('role');

        return is_string($role) ? ForumGroupRole::tryFrom($role) : null;
    }

    private function managesPlatform(User $user): bool
    {
        return $user->isAdministrator();
    }
}

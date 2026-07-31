<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\User;
use App\Services\ForumGroupAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ChangeForumGroupMemberRole
{
    public function __construct(
        private Gate $gate,
        private ForumGroupAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumGroupMembership $membership,
        ForumGroupRole $role,
        int $expectedLockVersion,
        string $reason,
        string $idempotencyKey,
    ): ForumGroupMembership {
        Validator::make([
            'role' => $role->value,
            'expected_lock_version' => $expectedLockVersion,
            'reason' => $reason,
            'idempotency_key' => $idempotencyKey,
        ], [
            'role' => [
                'required',
                Rule::notIn([ForumGroupRole::Owner->value]),
                Rule::enum(ForumGroupRole::class),
            ],
            'expected_lock_version' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $group = $membership->group;
        $this->gate->forUser($actor)->authorize('manageMember', [$group, $membership]);

        return DB::transaction(function () use (
            $actor,
            $expectedLockVersion,
            $idempotencyKey,
            $membership,
            $reason,
            $role,
        ): ForumGroupMembership {
            $lockedMembership = ForumGroupMembership::query()
                ->with(['group', 'user'])
                ->lockForUpdate()
                ->findOrFail($membership->id);
            $group = ForumGroup::query()
                ->lockForUpdate()
                ->findOrFail($lockedMembership->forum_group_id);
            $this->gate->forUser($actor)->authorize(
                'manageMember',
                [$group, $lockedMembership],
            );
            $this->authorizeRoleChange($actor, $group, $lockedMembership, $role);

            if ($lockedMembership->last_idempotency_key === $idempotencyKey) {
                return $lockedMembership;
            }

            if ($lockedMembership->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'membership' => __('forum_groups.validation.membership_changed'),
                ]);
            }

            if ($lockedMembership->state !== ForumGroupMembershipState::Active) {
                throw ValidationException::withMessages([
                    'membership' => __('forum_groups.validation.member_not_active'),
                ]);
            }

            $previousRole = $lockedMembership->role;
            $lockedMembership->forceFill([
                'role' => $role,
                'reviewed_by_user_id' => $actor->id,
                'review_reason' => trim($reason),
                'reviewed_at' => now(),
                'last_idempotency_key' => $idempotencyKey,
                'lock_version' => $lockedMembership->lock_version + 1,
            ])->save();

            $this->audit->record(
                group: $group,
                actor: $actor,
                eventType: ForumGroupEventType::RoleChanged,
                reasonCode: 'member-role-changed',
                summaryTranslationKey: 'forum_groups.events.role-changed',
                subject: $lockedMembership->user,
                metadata: [
                    'from_role' => $previousRole->value,
                    'to_role' => $role->value,
                    'reason' => trim($reason),
                ],
                idempotencyKey: "group:{$group->id}:role:{$idempotencyKey}",
            );

            return $lockedMembership->refresh();
        }, 3);
    }

    private function authorizeRoleChange(
        User $actor,
        ForumGroup $group,
        ForumGroupMembership $target,
        ForumGroupRole $newRole,
    ): void {
        if ($actor->isAdministrator() || $group->owner_user_id === $actor->id) {
            return;
        }

        $actorRole = $group->memberships()
            ->where('user_id', $actor->id)
            ->where('state', ForumGroupMembershipState::Active->value)
            ->value('role');
        $allowed = match ($actorRole) {
            ForumGroupRole::Administrator->value => [
                ForumGroupRole::Moderator,
                ForumGroupRole::Steward,
                ForumGroupRole::Member,
                ForumGroupRole::RestrictedMember,
            ],
            ForumGroupRole::Moderator->value => [
                ForumGroupRole::Member,
                ForumGroupRole::RestrictedMember,
            ],
            default => [],
        };

        if (! in_array($newRole, $allowed, true)
            || ! in_array($target->role, $allowed, true)
        ) {
            throw new AuthorizationException;
        }
    }
}

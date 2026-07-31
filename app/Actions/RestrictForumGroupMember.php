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
use Illuminate\Validation\ValidationException;

final readonly class RestrictForumGroupMember
{
    public function __construct(
        private Gate $gate,
        private ForumGroupAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumGroupMembership $membership,
        bool $ban,
        string $reason,
        int $expectedLockVersion,
        string $idempotencyKey,
    ): ForumGroupMembership {
        Validator::make(compact(
            'reason',
            'expectedLockVersion',
            'idempotencyKey',
        ), [
            'reason' => ['required', 'string', 'min:3', 'max:2000'],
            'expectedLockVersion' => ['required', 'integer', 'min:0'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $this->gate->forUser($actor)->authorize(
            'manageMember',
            [$membership->group, $membership],
        );

        return DB::transaction(function () use (
            $actor,
            $ban,
            $expectedLockVersion,
            $idempotencyKey,
            $membership,
            $reason,
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
            $this->authorizeHierarchy($actor, $group, $lockedMembership);

            if ($lockedMembership->last_idempotency_key === $idempotencyKey) {
                return $lockedMembership;
            }

            if ($lockedMembership->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'membership' => __('forum_groups.validation.membership_changed'),
                ]);
            }

            $wasActive = $lockedMembership->state === ForumGroupMembershipState::Active;
            $state = $ban
                ? ForumGroupMembershipState::Banned
                : ForumGroupMembershipState::Removed;
            $lockedMembership->forceFill([
                'state' => $state,
                'restriction_reason' => trim($reason),
                'reviewed_by_user_id' => $actor->id,
                'reviewed_at' => now(),
                'ended_at' => now(),
                'last_idempotency_key' => $idempotencyKey,
                'lock_version' => $lockedMembership->lock_version + 1,
            ])->save();

            if ($wasActive) {
                $group->decrement('active_member_count');
            }

            $eventType = $ban
                ? ForumGroupEventType::MemberBanned
                : ForumGroupEventType::MemberRemoved;
            $this->audit->record(
                group: $group,
                actor: $actor,
                eventType: $eventType,
                reasonCode: $eventType->value,
                summaryTranslationKey: "forum_groups.events.{$eventType->value}",
                subject: $lockedMembership->user,
                metadata: ['reason' => trim($reason)],
                idempotencyKey: "group:{$group->id}:restriction:{$idempotencyKey}",
            );

            return $lockedMembership->refresh();
        }, 3);
    }

    private function authorizeHierarchy(
        User $actor,
        ForumGroup $group,
        ForumGroupMembership $target,
    ): void {
        if ($actor->isAdministrator() || $group->owner_user_id === $actor->id) {
            return;
        }

        $actorRole = $group->memberships()
            ->where('user_id', $actor->id)
            ->where('state', ForumGroupMembershipState::Active->value)
            ->value('role');
        $allowedTargetRoles = match ($actorRole) {
            ForumGroupRole::Administrator->value => [
                ForumGroupRole::Moderator,
                ForumGroupRole::Steward,
                ForumGroupRole::Member,
                ForumGroupRole::RestrictedMember,
            ],
            ForumGroupRole::Moderator->value => [
                ForumGroupRole::Steward,
                ForumGroupRole::Member,
                ForumGroupRole::RestrictedMember,
            ],
            default => [],
        };

        if (! in_array($target->role, $allowedTargetRoles, true)) {
            throw new AuthorizationException;
        }
    }
}

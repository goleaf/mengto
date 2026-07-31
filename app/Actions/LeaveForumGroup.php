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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class LeaveForumGroup
{
    public function __construct(private ForumGroupAudit $audit) {}

    public function handle(
        User $user,
        ForumGroupMembership $membership,
        int $expectedLockVersion,
        string $idempotencyKey,
    ): ForumGroupMembership {
        Validator::make(compact('expectedLockVersion', 'idempotencyKey'), [
            'expectedLockVersion' => ['required', 'integer', 'min:0'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if ($membership->user_id !== $user->id
            || $membership->role === ForumGroupRole::Owner
        ) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use (
            $expectedLockVersion,
            $idempotencyKey,
            $membership,
            $user,
        ): ForumGroupMembership {
            $lockedMembership = ForumGroupMembership::query()
                ->with(['group', 'user'])
                ->lockForUpdate()
                ->findOrFail($membership->id);
            $group = ForumGroup::query()
                ->lockForUpdate()
                ->findOrFail($lockedMembership->forum_group_id);

            if ($lockedMembership->user_id !== $user->id
                || $lockedMembership->role === ForumGroupRole::Owner
            ) {
                throw new AuthorizationException;
            }

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

            $lockedMembership->forceFill([
                'state' => ForumGroupMembershipState::Left,
                'ended_at' => now(),
                'last_idempotency_key' => $idempotencyKey,
                'lock_version' => $lockedMembership->lock_version + 1,
            ])->save();
            $group->decrement('active_member_count');
            $this->audit->record(
                group: $group,
                actor: $user,
                eventType: ForumGroupEventType::MemberLeft,
                reasonCode: 'member-left',
                summaryTranslationKey: 'forum_groups.events.member-left',
                subject: $user,
                idempotencyKey: "group:{$group->id}:leave:{$idempotencyKey}",
            );

            return $lockedMembership->refresh();
        }, 3);
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupMembershipState;
use App\Models\ForumGroup;
use App\Models\ForumGroupMembership;
use App\Models\User;
use App\Services\ForumGroupAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class ReviewForumGroupMembership
{
    public function __construct(
        private Gate $gate,
        private ForumGroupAudit $audit,
    ) {}

    public function handle(
        User $reviewer,
        ForumGroupMembership $membership,
        bool $approve,
        string $reason,
        int $expectedLockVersion,
        string $idempotencyKey,
    ): ForumGroupMembership {
        Validator::make(compact(
            'reason',
            'expectedLockVersion',
            'idempotencyKey',
        ), [
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
            'expectedLockVersion' => ['required', 'integer', 'min:0'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $this->gate->forUser($reviewer)->authorize(
            'reviewMembership',
            $membership->group,
        );

        return DB::transaction(function () use (
            $approve,
            $expectedLockVersion,
            $idempotencyKey,
            $membership,
            $reason,
            $reviewer,
        ): ForumGroupMembership {
            $lockedMembership = ForumGroupMembership::query()
                ->with(['group', 'user'])
                ->lockForUpdate()
                ->findOrFail($membership->id);
            $group = ForumGroup::query()
                ->lockForUpdate()
                ->findOrFail($lockedMembership->forum_group_id);
            $this->gate->forUser($reviewer)->authorize('reviewMembership', $group);

            if ($lockedMembership->last_idempotency_key === $idempotencyKey) {
                return $lockedMembership;
            }

            if ($lockedMembership->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'membership' => __('forum_groups.validation.membership_changed'),
                ]);
            }

            if ($lockedMembership->state !== ForumGroupMembershipState::Pending) {
                throw ValidationException::withMessages([
                    'membership' => __('forum_groups.validation.request_not_pending'),
                ]);
            }

            $state = $approve
                ? ForumGroupMembershipState::Active
                : ForumGroupMembershipState::Rejected;
            $lockedMembership->forceFill([
                'state' => $state,
                'reviewed_by_user_id' => $reviewer->id,
                'review_reason' => trim($reason),
                'reviewed_at' => now(),
                'joined_at' => $approve ? now() : null,
                'ended_at' => $approve ? null : now(),
                'last_idempotency_key' => $idempotencyKey,
                'lock_version' => $lockedMembership->lock_version + 1,
            ])->save();

            if ($approve) {
                $group->increment('active_member_count');
            }

            $this->audit->record(
                group: $group,
                actor: $reviewer,
                eventType: $approve
                    ? ForumGroupEventType::MembershipApproved
                    : ForumGroupEventType::MembershipRejected,
                reasonCode: $approve ? 'membership-approved' : 'membership-rejected',
                summaryTranslationKey: $approve
                    ? 'forum_groups.events.membership-approved'
                    : 'forum_groups.events.membership-rejected',
                subject: $lockedMembership->user,
                metadata: ['reason' => trim($reason)],
                idempotencyKey: "group:{$group->id}:review:{$idempotencyKey}",
            );

            return $lockedMembership->refresh();
        }, 3);
    }
}

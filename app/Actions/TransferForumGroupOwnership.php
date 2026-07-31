<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupMembershipState;
use App\Enums\ForumGroupRole;
use App\Models\ForumGroup;
use App\Models\ForumGroupEvent;
use App\Models\ForumGroupMembership;
use App\Models\User;
use App\Services\ForumGroupAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class TransferForumGroupOwnership
{
    public function __construct(
        private Gate $gate,
        private ForumGroupAudit $audit,
    ) {}

    public function handle(
        User $owner,
        ForumGroup $group,
        ForumGroupMembership $newOwnerMembership,
        int $expectedGroupVersion,
        string $reason,
        string $idempotencyKey,
    ): ForumGroup {
        Validator::make([
            'expected_group_version' => $expectedGroupVersion,
            'reason' => $reason,
            'idempotency_key' => $idempotencyKey,
        ], [
            'expected_group_version' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:3', 'max:2000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $this->gate->forUser($owner)->authorize('transferOwnership', $group);

        return DB::transaction(function () use (
            $expectedGroupVersion,
            $group,
            $idempotencyKey,
            $newOwnerMembership,
            $owner,
            $reason,
        ): ForumGroup {
            $lockedGroup = ForumGroup::query()->lockForUpdate()->findOrFail($group->id);
            $this->gate->forUser($owner)->authorize(
                'transferOwnership',
                $lockedGroup,
            );

            if (ForumGroupEvent::query()
                ->where('idempotency_key', "group:{$lockedGroup->id}:ownership:{$idempotencyKey}")
                ->exists()
            ) {
                return $lockedGroup;
            }

            if ($lockedGroup->lock_version !== $expectedGroupVersion) {
                throw ValidationException::withMessages([
                    'group' => __('forum_groups.validation.group_changed'),
                ]);
            }

            $currentOwnerMembership = ForumGroupMembership::query()
                ->where('forum_group_id', $lockedGroup->id)
                ->where('user_id', $owner->id)
                ->lockForUpdate()
                ->firstOrFail();
            $target = ForumGroupMembership::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($newOwnerMembership->id);

            if ($target->forum_group_id !== $lockedGroup->id
                || $target->user_id === $owner->id
                || $target->state !== ForumGroupMembershipState::Active
            ) {
                throw new AuthorizationException;
            }

            $currentOwnerMembership->forceFill([
                'role' => ForumGroupRole::Administrator,
                'lock_version' => $currentOwnerMembership->lock_version + 1,
            ])->save();
            $target->forceFill([
                'role' => ForumGroupRole::Owner,
                'lock_version' => $target->lock_version + 1,
            ])->save();
            $lockedGroup->forceFill([
                'owner_user_id' => $target->user_id,
                'lock_version' => $lockedGroup->lock_version + 1,
            ])->save();
            $this->audit->record(
                group: $lockedGroup,
                actor: $owner,
                eventType: ForumGroupEventType::OwnershipTransferred,
                reasonCode: 'ownership-transferred',
                summaryTranslationKey: 'forum_groups.events.ownership-transferred',
                subject: $target->user,
                metadata: [
                    'previous_owner_user_id' => $owner->id,
                    'reason' => trim($reason),
                ],
                idempotencyKey: "group:{$lockedGroup->id}:ownership:{$idempotencyKey}",
            );

            return $lockedGroup->refresh();
        }, 3);
    }
}

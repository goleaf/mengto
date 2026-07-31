<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupEventType;
use App\Enums\ForumGroupStatus;
use App\Models\ForumGroup;
use App\Models\User;
use App\Services\ForumGroupAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class TransitionForumGroup
{
    public function __construct(
        private Gate $gate,
        private ForumGroupAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumGroup $group,
        ForumGroupStatus $status,
        int $expectedLockVersion,
        string $reason,
        string $idempotencyKey,
    ): ForumGroup {
        Validator::make([
            'status' => $status->value,
            'expected_lock_version' => $expectedLockVersion,
            'reason' => $reason,
            'idempotency_key' => $idempotencyKey,
        ], [
            'status' => ['required', Rule::enum(ForumGroupStatus::class)],
            'expected_lock_version' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'min:3', 'max:2000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
        $ability = $status === ForumGroupStatus::Archived ? 'archive' : 'close';
        $this->gate->forUser($actor)->authorize($ability, $group);

        return DB::transaction(function () use (
            $ability,
            $actor,
            $expectedLockVersion,
            $group,
            $idempotencyKey,
            $reason,
            $status,
        ): ForumGroup {
            $lockedGroup = ForumGroup::query()->lockForUpdate()->findOrFail($group->id);
            $this->gate->forUser($actor)->authorize($ability, $lockedGroup);

            if ($lockedGroup->status === $status) {
                return $lockedGroup;
            }

            if ($lockedGroup->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'group' => __('forum_groups.validation.group_changed'),
                ]);
            }

            if ($lockedGroup->status === ForumGroupStatus::Archived) {
                throw ValidationException::withMessages([
                    'group' => __('forum_groups.validation.archived_terminal'),
                ]);
            }

            if ($status === ForumGroupStatus::Active
                && $lockedGroup->status !== ForumGroupStatus::Closed
            ) {
                throw ValidationException::withMessages([
                    'group' => __('forum_groups.validation.transition_invalid'),
                ]);
            }

            $eventType = match ($status) {
                ForumGroupStatus::Active => ForumGroupEventType::Reopened,
                ForumGroupStatus::Closed => ForumGroupEventType::Closed,
                ForumGroupStatus::Archived => ForumGroupEventType::Archived,
            };
            $lockedGroup->forceFill([
                'status' => $status,
                'closed_at' => match ($status) {
                    ForumGroupStatus::Active => null,
                    default => $lockedGroup->closed_at ?? now(),
                },
                'archived_at' => $status === ForumGroupStatus::Archived ? now() : null,
                'lock_version' => $lockedGroup->lock_version + 1,
            ])->save();
            $this->audit->record(
                group: $lockedGroup,
                actor: $actor,
                eventType: $eventType,
                reasonCode: $eventType->value,
                summaryTranslationKey: "forum_groups.events.{$eventType->value}",
                metadata: ['reason' => trim($reason)],
                idempotencyKey: "group:{$lockedGroup->id}:transition:{$idempotencyKey}",
            );

            return $lockedGroup->refresh();
        }, 3);
    }
}

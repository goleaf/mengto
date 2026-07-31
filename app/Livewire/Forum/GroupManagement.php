<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\ChangeForumGroupMemberRole;
use App\Actions\InviteForumGroupMember;
use App\Actions\RestrictForumGroupMember;
use App\Actions\ReviewForumGroupMembership;
use App\Actions\RevokeForumGroupInvitation;
use App\Actions\TransferForumGroupOwnership;
use App\Actions\TransitionForumGroup;
use App\Data\ForumGroupInvitationData;
use App\Enums\ForumGroupRole;
use App\Enums\ForumGroupStatus;
use App\Models\ForumGroup;
use App\Models\ForumGroupEvent;
use App\Models\ForumGroupInvitation;
use App\Models\ForumGroupMembership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class GroupManagement extends Component
{
    #[Locked]
    public int $groupId;

    public string $inviteEmail = '';

    public string $inviteRole = 'member';

    public string $inviteMessage = '';

    public string $reviewReason = '';

    public string $memberReason = '';

    public string $transferReason = '';

    public string $lifecycleReason = '';

    public string $feedback = '';

    public function mount(int $groupId): void
    {
        $this->groupId = $groupId;
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function workspace(): array
    {
        $user = $this->requireUser();
        $group = $this->groupModel();

        if (Gate::forUser($user)->denies('viewAudit', $group)) {
            return ['authorized' => false];
        }

        $group->load([
            'memberships' => fn (HasMany $query): HasMany => $query
                ->select([
                    'id',
                    'forum_group_id',
                    'user_id',
                    'role',
                    'state',
                    'answers',
                    'requested_at',
                    'joined_at',
                    'restriction_reason',
                    'lock_version',
                ])
                ->with('user:id,name,email')
                ->orderBy('state')
                ->orderBy('id')
                ->limit(100),
            'invitations' => fn (HasMany $query): HasMany => $query
                ->select([
                    'id',
                    'forum_group_id',
                    'invited_user_id',
                    'invited_by_user_id',
                    'role',
                    'state',
                    'message',
                    'expires_at',
                ])
                ->where('state', 'pending')
                ->where('expires_at', '>', now())
                ->with(['invitee:id,name,email', 'inviter:id,name'])
                ->orderBy('expires_at')
                ->limit(50),
            'events' => fn (HasMany $query): HasMany => $query
                ->select([
                    'id',
                    'forum_group_id',
                    'actor_user_id',
                    'subject_user_id',
                    'event_type',
                    'reason_code',
                    'summary_translation_key',
                    'created_at',
                ])
                ->with(['actor:id,name', 'subjectUser:id,name'])
                ->latest('created_at')
                ->latest('id')
                ->limit(50),
        ]);

        return [
            'authorized' => true,
            'group_id' => $group->id,
            'group_name' => $group->displayName(),
            'status' => $group->status->label(),
            'status_key' => $group->status->value,
            'lock_version' => $group->lock_version,
            'can_invite' => Gate::forUser($user)->allows('invite', $group),
            'can_review' => Gate::forUser($user)->allows('reviewMembership', $group),
            'can_close' => Gate::forUser($user)->allows('close', $group),
            'can_archive' => Gate::forUser($user)->allows('archive', $group),
            'can_transfer' => Gate::forUser($user)->allows('transferOwnership', $group),
            'memberships' => $group->memberships->map(
                static fn (ForumGroupMembership $membership): array => [
                    'id' => $membership->id,
                    'user_id' => $membership->user_id,
                    'name' => $membership->user->name,
                    'email' => $membership->user->email,
                    'role' => $membership->role->label(),
                    'role_key' => $membership->role->value,
                    'state' => $membership->state->label(),
                    'state_key' => $membership->state->value,
                    'answers' => $membership->answers ?? [],
                    'lock_version' => $membership->lock_version,
                    'restriction_reason' => $membership->restriction_reason,
                ],
            )->all(),
            'invitations' => $group->invitations->map(
                static fn (ForumGroupInvitation $invitation): array => [
                    'id' => $invitation->id,
                    'name' => $invitation->invitee->name,
                    'email' => $invitation->invitee->email,
                    'inviter' => $invitation->inviter->name,
                    'role' => $invitation->role->label(),
                    'message' => $invitation->message,
                    'expires_at' => $invitation->expires_at->isoFormat('LLL'),
                ],
            )->all(),
            'events' => $group->events->map(
                static fn (ForumGroupEvent $event): array => [
                    'id' => $event->id,
                    'summary' => __($event->summary_translation_key),
                    'actor' => $event->actor?->name,
                    'subject' => $event->subjectUser?->name,
                    'reason_code' => $event->reason_code,
                    'created_at' => $event->created_at->isoFormat('LLL'),
                ],
            )->all(),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function roleOptions(): array
    {
        return collect(ForumGroupRole::cases())
            ->reject(static fn (ForumGroupRole $role): bool => $role === ForumGroupRole::Owner)
            ->mapWithKeys(static fn (ForumGroupRole $role): array => [
                $role->value => $role->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function inviteRoleOptions(): array
    {
        return [
            ForumGroupRole::Member->value => ForumGroupRole::Member->label(),
            ForumGroupRole::RestrictedMember->value => ForumGroupRole::RestrictedMember->label(),
        ];
    }

    public function invite(InviteForumGroupMember $invite): void
    {
        $validated = $this->validate([
            'inviteEmail' => ['required', 'email:rfc', 'max:255', 'exists:users,email'],
            'inviteRole' => [
                'required',
                Rule::in([
                    ForumGroupRole::Member->value,
                    ForumGroupRole::RestrictedMember->value,
                ]),
            ],
            'inviteMessage' => ['nullable', 'string', 'max:1000'],
        ]);
        $invitee = User::query()
            ->where('email', mb_strtolower(trim((string) $validated['inviteEmail'])))
            ->firstOrFail();
        $invite->handle(
            inviter: $this->requireUser(),
            group: $this->groupModel(),
            invitee: $invitee,
            data: new ForumGroupInvitationData(
                role: ForumGroupRole::from((string) $validated['inviteRole']),
                message: filled($validated['inviteMessage'] ?? null)
                    ? trim((string) $validated['inviteMessage'])
                    : null,
                expiresAt: CarbonImmutable::now()->addDays(14),
                idempotencyKey: $this->token('invite'),
            ),
        );
        $this->reset('inviteEmail', 'inviteMessage');
        $this->feedback = __('forum_groups.feedback.invited');
        unset($this->workspace);
    }

    public function review(
        int $membershipId,
        bool $approve,
        int $expectedLockVersion,
        ReviewForumGroupMembership $review,
    ): void {
        $validated = $this->validate([
            'reviewReason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);
        $review->handle(
            reviewer: $this->requireUser(),
            membership: $this->membership($membershipId),
            approve: $approve,
            reason: (string) $validated['reviewReason'],
            expectedLockVersion: $expectedLockVersion,
            idempotencyKey: $this->token($approve ? 'approve' : 'reject'),
        );
        $this->reset('reviewReason');
        $this->feedback = $approve
            ? __('forum_groups.feedback.approved')
            : __('forum_groups.feedback.rejected');
        unset($this->workspace);
    }

    public function changeRole(
        int $membershipId,
        string $role,
        int $expectedLockVersion,
        ChangeForumGroupMemberRole $changeRole,
    ): void {
        $validated = validator(['role' => $role], [
            'role' => [
                'required',
                Rule::enum(ForumGroupRole::class),
                Rule::notIn([ForumGroupRole::Owner->value]),
            ],
        ])->validate();
        $changeRole->handle(
            actor: $this->requireUser(),
            membership: $this->membership($membershipId),
            role: ForumGroupRole::from((string) $validated['role']),
            expectedLockVersion: $expectedLockVersion,
            reason: __('forum_groups.reasons.role_changed'),
            idempotencyKey: $this->token('role'),
        );
        $this->feedback = __('forum_groups.feedback.role_changed');
        unset($this->workspace);
    }

    public function restrict(
        int $membershipId,
        bool $ban,
        int $expectedLockVersion,
        RestrictForumGroupMember $restrict,
    ): void {
        $validated = $this->validate([
            'memberReason' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
        $restrict->handle(
            actor: $this->requireUser(),
            membership: $this->membership($membershipId),
            ban: $ban,
            reason: (string) $validated['memberReason'],
            expectedLockVersion: $expectedLockVersion,
            idempotencyKey: $this->token($ban ? 'ban' : 'remove'),
        );
        $this->reset('memberReason');
        $this->feedback = $ban
            ? __('forum_groups.feedback.banned')
            : __('forum_groups.feedback.removed');
        unset($this->workspace);
    }

    public function transfer(
        int $membershipId,
        int $expectedGroupVersion,
        TransferForumGroupOwnership $transfer,
    ): void {
        $validated = $this->validate([
            'transferReason' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
        $transfer->handle(
            owner: $this->requireUser(),
            group: $this->groupModel(),
            newOwnerMembership: $this->membership($membershipId),
            expectedGroupVersion: $expectedGroupVersion,
            reason: (string) $validated['transferReason'],
            idempotencyKey: $this->token('ownership'),
        );
        $this->reset('transferReason');
        $this->feedback = __('forum_groups.feedback.ownership_transferred');
        unset($this->workspace);
    }

    public function changeStatus(
        string $status,
        int $expectedLockVersion,
        TransitionForumGroup $transition,
    ): void {
        $validated = $this->validate([
            'lifecycleReason' => ['required', 'string', 'min:3', 'max:2000'],
        ]);
        $target = ForumGroupStatus::tryFrom($status);
        abort_unless($target instanceof ForumGroupStatus, 422);
        $transition->handle(
            actor: $this->requireUser(),
            group: $this->groupModel(),
            status: $target,
            expectedLockVersion: $expectedLockVersion,
            reason: (string) $validated['lifecycleReason'],
            idempotencyKey: $this->token('transition'),
        );
        $this->reset('lifecycleReason');
        $this->feedback = __('forum_groups.feedback.status_changed');
        unset($this->workspace);
    }

    public function revoke(
        int $invitationId,
        RevokeForumGroupInvitation $revoke,
    ): void {
        $invitation = ForumGroupInvitation::query()
            ->where('forum_group_id', $this->groupId)
            ->findOrFail($invitationId);
        $revoke->handle($this->requireUser(), $invitation);
        $this->feedback = __('forum_groups.feedback.invitation_revoked');
        unset($this->workspace);
    }

    public function render()
    {
        return view('livewire.forum.group-management');
    }

    private function membership(int $membershipId): ForumGroupMembership
    {
        return ForumGroupMembership::query()
            ->where('forum_group_id', $this->groupId)
            ->findOrFail($membershipId);
    }

    private function groupModel(): ForumGroup
    {
        return ForumGroup::query()->findOrFail($this->groupId);
    }

    private function requireUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function token(string $operation): string
    {
        return "group:{$this->groupId}:{$operation}:".(string) str()->uuid();
    }
}

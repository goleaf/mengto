<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\LeaveForumGroup;
use App\Actions\RequestForumGroupMembership;
use App\Actions\RespondToForumGroupInvitation;
use App\Actions\SubmitForumReport;
use App\Data\ForumGroupMembershipRequestData;
use App\Models\ForumGroup;
use App\Models\ForumGroupInvitation;
use App\Models\ForumReportReason;
use App\Models\Taxon;
use App\Models\User;
use App\Services\ForumReportReasonCatalog;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class GroupWorkspace extends Component
{
    #[Locked]
    public int $groupId;

    #[Locked]
    public string $requestToken;

    #[Locked]
    public string $leaveToken;

    /** @var array<string, string> */
    public array $answers = [];

    public string $reportReason = '';

    public string $reportDetails = '';

    public bool $reportTruthfulnessConfirmed = false;

    public bool $reportImmediateSafety = false;

    public bool $reportBlockOwner = false;

    public string $feedback = '';

    public function mount(int $groupId): void
    {
        $this->groupId = $groupId;
        $this->requestToken = $this->token('request');
        $this->leaveToken = $this->token('leave');
        Gate::authorize('view', $this->groupModel());
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function group(): array
    {
        $user = $this->requireUser();
        $group = ForumGroup::query()
            ->select([
                'id',
                'owner_user_id',
                'stable_key',
                'is_system_managed',
                'name',
                'name_translation_key',
                'description',
                'description_translation_key',
                'rules',
                'visibility',
                'status',
                'default_locale',
                'location_scope',
                'membership_questions',
                'active_member_count',
                'lock_version',
                'closed_at',
                'archived_at',
            ])
            ->with([
                'owner:id,name',
                'taxa:id,stable_key',
                'taxa.activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
                'memberships' => fn (HasMany $query): HasMany => $query
                    ->select([
                        'id',
                        'forum_group_id',
                        'user_id',
                        'role',
                        'state',
                        'lock_version',
                    ])
                    ->where('user_id', $user->id),
                'invitations' => fn (HasMany $query): HasMany => $query
                    ->select([
                        'id',
                        'forum_group_id',
                        'invited_user_id',
                        'role',
                        'state',
                        'message',
                        'expires_at',
                    ])
                    ->where('invited_user_id', $user->id)
                    ->where('state', 'pending')
                    ->where('expires_at', '>', now()),
            ])
            ->findOrFail($this->groupId);
        Gate::authorize('view', $group);
        $membership = $group->memberships->first();
        $invitation = $group->invitations->first();

        return [
            'id' => $group->id,
            'stable_key' => $group->stable_key,
            'name' => $group->displayName(),
            'description' => $group->displayDescription(),
            'rules' => $group->displayRules(),
            'visibility' => $group->visibility->label(),
            'visibility_key' => $group->visibility->value,
            'status' => $group->status->label(),
            'status_key' => $group->status->value,
            'language' => __('forum_groups.locales.'.$group->default_locale),
            'location_scope' => $group->location_scope,
            'membership_questions' => $group->displayMembershipQuestions(),
            'member_count' => $group->active_member_count,
            'owner_name' => $group->owner?->name ?? __('forum_groups.system.platform_managed'),
            'taxa' => $group->taxa->map(static fn (Taxon $taxon): array => [
                'id' => $taxon->id,
                'scientific_name' => $taxon->activeVersion?->scientific_name
                    ?? __('taxonomy.unidentified'),
                'rank' => $taxon->activeVersion?->rank
                    ?? __('taxonomy.unknown_rank'),
            ])->all(),
            'membership_id' => $membership?->id,
            'membership_state' => $membership?->state->label(),
            'membership_state_key' => $membership?->state->value,
            'membership_role' => $membership?->role->label(),
            'membership_role_key' => $membership?->role->value,
            'membership_lock_version' => $membership?->lock_version,
            'invitation_id' => $invitation?->id,
            'invitation_role' => $invitation?->role->label(),
            'invitation_message' => $invitation?->message,
            'can_request' => Gate::forUser($user)->allows('requestMembership', $group),
            'can_view_content' => Gate::forUser($user)->allows('viewMemberContent', $group),
            'can_report' => Gate::forUser($user)->allows('report', $group),
            'can_manage' => Gate::forUser($user)->allows('viewAudit', $group),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function reportReasonOptions(): array
    {
        return ForumReportReason::query()
            ->select(['stable_key', 'translation_key'])
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('stable_key')
            ->get()
            ->mapWithKeys(static fn (ForumReportReason $reason): array => [
                $reason->stable_key => __($reason->translation_key),
            ])
            ->all();
    }

    public function requestMembership(
        RequestForumGroupMembership $requestMembership,
    ): void {
        $group = $this->groupModel();
        $requestMembership->handle(
            $this->requireUser(),
            $group,
            new ForumGroupMembershipRequestData(
                answers: $this->answers,
                idempotencyKey: $this->requestToken,
            ),
        );
        $this->feedback = $group->visibility->value === 'public'
            ? __('forum_groups.feedback.joined')
            : __('forum_groups.feedback.requested');
        $this->requestToken = $this->token('request');
        unset($this->group);
    }

    public function respondToInvitation(
        int $invitationId,
        bool $accept,
        RespondToForumGroupInvitation $respond,
    ): void {
        $invitation = ForumGroupInvitation::query()
            ->where('forum_group_id', $this->groupId)
            ->where('invited_user_id', $this->requireUser()->id)
            ->findOrFail($invitationId);
        $respond->handle($this->requireUser(), $invitation, $accept);
        $this->feedback = $accept
            ? __('forum_groups.feedback.invitation_accepted')
            : __('forum_groups.feedback.invitation_declined');
        unset($this->group);
    }

    public function leave(LeaveForumGroup $leave): void
    {
        $user = $this->requireUser();
        $membership = $this->groupModel()->membershipFor($user);
        abort_unless($membership !== null, 404);
        $leave->handle(
            $user,
            $membership,
            $membership->lock_version,
            $this->leaveToken,
        );
        $this->leaveToken = $this->token('leave');
        $this->feedback = __('forum_groups.feedback.left');
        unset($this->group);
    }

    public function report(
        ForumReportReasonCatalog $reasonCatalog,
        SubmitForumReport $submitReport,
    ): void {
        $validated = $this->validate([
            'reportReason' => [
                'required',
                Rule::in($reasonCatalog->acceptedInputKeys()),
            ],
            'reportDetails' => ['nullable', 'string', 'max:1200'],
            'reportTruthfulnessConfirmed' => ['accepted'],
            'reportImmediateSafety' => ['boolean'],
            'reportBlockOwner' => ['boolean'],
        ]);
        $submitReport->handle(
            reporter: $this->requireUser(),
            subject: $this->groupModel(),
            reasonKey: (string) $validated['reportReason'],
            details: filled($validated['reportDetails'] ?? null)
                ? trim((string) $validated['reportDetails'])
                : null,
            truthfulnessConfirmed: (bool) $validated['reportTruthfulnessConfirmed'],
            immediateSafety: (bool) $validated['reportImmediateSafety'],
            blockAffectedUser: (bool) $validated['reportBlockOwner'],
        );
        $this->reset(
            'reportReason',
            'reportDetails',
            'reportTruthfulnessConfirmed',
            'reportImmediateSafety',
            'reportBlockOwner',
        );
        $this->feedback = __('forum_groups.feedback.reported');
    }

    public function render()
    {
        return view('livewire.forum.group-workspace');
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

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
use App\Models\SocialActor;
use App\Models\Taxon;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Services\CommunityMembershipActorEligibility;
use App\Services\ForumReportReasonCatalog;
use App\Services\SocialActorPresenter;
use Illuminate\Database\Eloquent\Relations\Relation;
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

    public string $selectedActorKey = '';

    /** @var array<string, string> */
    public array $answers = [];

    public string $reportReason = '';

    public string $reportDetails = '';

    public bool $reportTruthfulnessConfirmed = false;

    public bool $reportImmediateSafety = false;

    public bool $reportBlockOwner = false;

    public string $feedback = '';

    private CommunityMembershipActorEligibility $actorEligibility;

    private SocialActorPresenter $actorPresenter;

    public function boot(
        CommunityMembershipActorEligibility $actorEligibility,
        SocialActorPresenter $actorPresenter,
    ): void {
        $this->actorEligibility = $actorEligibility;
        $this->actorPresenter = $actorPresenter;
    }

    public function mount(int $groupId): void
    {
        $this->groupId = $groupId;
        $this->requestToken = $this->token('request');
        $this->leaveToken = $this->token('leave');
        $group = $this->groupModel();
        Gate::authorize('view', $group);
        $this->selectedActorKey = $this->actorEligibility
            ->defaultFor($this->requireUser(), $group)
            ->actor_key;
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
                'rules_version',
                'visibility',
                'status',
                'default_locale',
                'location_scope',
                'membership_questions',
                'allowed_actor_types',
                'active_member_count',
                'lock_version',
                'closed_at',
                'archived_at',
            ])
            ->with([
                'owner:id,name',
                'taxa:id,stable_key',
                'taxa.activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
                'memberships' => function (Relation $query) use ($user): void {
                    $query->select([
                        'id',
                        'forum_group_id',
                        'user_id',
                        'social_actor_id',
                        'role',
                        'state',
                        'accepted_rules_version',
                        'lock_version',
                    ])
                        ->where('user_id', $user->id);
                },
                'invitations' => function (Relation $query) use ($user): void {
                    $query->select([
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
                        ->where('expires_at', '>', now());
                },
            ])
            ->findOrFail($this->groupId);
        Gate::authorize('view', $group);
        $selectedActor = $this->actorEligibility->resolveFor(
            $user,
            $group,
            $this->selectedActorKey,
        );
        $membership = $group->memberships->firstWhere('social_actor_id', $selectedActor->id);
        $invitation = $group->invitations->first();
        $owner = $group->getRelation('owner');

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
            'owner_name' => $owner instanceof User
                ? $owner->name
                : __('forum_groups.system.platform_managed'),
            'taxa' => $group->taxa->map($this->presentTaxon(...))->all(),
            'membership_id' => $membership?->id,
            'membership_state' => $membership?->state->label(),
            'membership_state_key' => $membership?->state->value,
            'membership_role' => $membership?->role->label(),
            'membership_role_key' => $membership?->role->value,
            'membership_lock_version' => $membership?->lock_version,
            'membership_rules_version' => $membership?->accepted_rules_version,
            'participating_as' => $this->actorPresenter->present($selectedActor),
            'invitation_id' => $invitation?->id,
            'invitation_role' => $invitation?->role->label(),
            'invitation_message' => $invitation?->message,
            'can_request' => Gate::forUser($user)->allows(
                'requestMembership',
                [$group, $selectedActor],
            ),
            'can_view_content' => Gate::forUser($user)->allows('viewMemberContent', $group),
            'can_report' => Gate::forUser($user)->allows('report', $group),
            'can_manage' => Gate::forUser($user)->allows('viewAudit', $group),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function actorOptions(): array
    {
        return $this->actorEligibility
            ->availableTo($this->requireUser(), $this->groupModel())
            ->mapWithKeys(function (SocialActor $actor): array {
                $presented = $this->actorPresenter->present($actor);

                return [
                    $presented['key'] => __('forum_groups.labels.actor_option', [
                        'name' => $presented['name'],
                        'type' => $presented['type_label'],
                    ]),
                ];
            })
            ->all();
    }

    /** @return array{id: int, scientific_name: string, rank: string} */
    private function presentTaxon(Taxon $taxon): array
    {
        $activeVersion = $taxon->getRelation('activeVersion');

        return [
            'id' => $taxon->id,
            'scientific_name' => $activeVersion instanceof TaxonVersion
                ? $activeVersion->scientific_name
                : __('taxonomy.unidentified'),
            'rank' => $activeVersion instanceof TaxonVersion
                ? $activeVersion->rank
                : __('taxonomy.unknown_rank'),
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
                socialActorKey: $this->selectedActorKey,
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
        $respond->handle(
            $this->requireUser(),
            $invitation,
            $accept,
            $accept ? $this->selectedActor() : null,
        );
        $this->feedback = $accept
            ? __('forum_groups.feedback.invitation_accepted')
            : __('forum_groups.feedback.invitation_declined');
        unset($this->group);
    }

    public function leave(LeaveForumGroup $leave): void
    {
        $user = $this->requireUser();
        $membership = $this->groupModel()->membershipForActor(
            $user,
            $this->selectedActor(),
        );
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
        return ForumGroup::query()
            ->select([
                'id',
                'owner_user_id',
                'stable_key',
                'creation_idempotency_key',
                'is_system_managed',
                'name',
                'name_translation_key',
                'description',
                'description_translation_key',
                'rules',
                'rules_version',
                'visibility',
                'status',
                'default_locale',
                'location_scope',
                'membership_questions',
                'allowed_actor_types',
                'active_member_count',
                'lock_version',
                'closed_at',
                'archived_at',
            ])
            ->findOrFail($this->groupId);
    }

    private function selectedActor(): SocialActor
    {
        return $this->actorEligibility->resolveFor(
            $this->requireUser(),
            $this->groupModel(),
            $this->selectedActorKey,
        );
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

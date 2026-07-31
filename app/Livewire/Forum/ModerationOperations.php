<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\ApplyForumModerationAction;
use App\Actions\AssignForumModerationCase;
use App\Actions\OpenForumModerationCase;
use App\Actions\RecuseForumModerator;
use App\Actions\ReviewForumModerationAppeal;
use App\Models\AdoptionCase;
use App\Models\ForumAnswer;
use App\Models\ForumComment;
use App\Models\ForumModerationAction;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumModerationAppeal;
use App\Models\ForumModerationCase;
use App\Models\ForumModeratorRecusal;
use App\Models\ForumReport;
use App\Models\ForumReportEvent;
use App\Models\ForumTopic;
use App\Models\Listing;
use App\Models\User;
use App\Services\LocaleFormatter;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ModerationOperations extends Component
{
    private ApplyForumModerationAction $applyAction;

    private AssignForumModerationCase $assignCaseAction;

    private AuthFactory $auth;

    private LocaleFormatter $formatter;

    private Gate $gate;

    private OpenForumModerationCase $openCaseAction;

    private RecuseForumModerator $recuseAction;

    private ReviewForumModerationAppeal $reviewAppealAction;

    #[Locked]
    public ?int $selectedCaseId = null;

    #[Locked]
    public ?int $selectedAppealId = null;

    public ?int $assigneeUserId = null;

    public ?int $actionDefinitionId = null;

    public string $actionRuleId = '';

    public string $actionPolicyBasis = '';

    public string $actionInternalReason = '';

    public string $actionEndsAt = '';

    public ?int $seniorApproverId = null;

    public string $recusalReason = 'personally-involved';

    public string $recusalPrivateNote = '';

    public string $appealOutcome = 'upheld';

    public string $appealDecisionReason = '';

    public function boot(
        AuthFactory $auth,
        Gate $gate,
        LocaleFormatter $formatter,
        OpenForumModerationCase $openCaseAction,
        AssignForumModerationCase $assignCaseAction,
        ApplyForumModerationAction $applyAction,
        RecuseForumModerator $recuseAction,
        ReviewForumModerationAppeal $reviewAppealAction,
    ): void {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->formatter = $formatter;
        $this->openCaseAction = $openCaseAction;
        $this->assignCaseAction = $assignCaseAction;
        $this->applyAction = $applyAction;
        $this->recuseAction = $recuseAction;
        $this->reviewAppealAction = $reviewAppealAction;
    }

    public function mount(): void
    {
        $this->administrator();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function reports(): array
    {
        $administrator = $this->administrator();
        $this->gate->forUser($administrator)->authorize('triage', ForumReport::class);

        return ForumReport::query()
            ->select([
                'id',
                'subject_type',
                'subject_id',
                'reason',
                'forum_report_reason_id',
                'priority',
                'status',
                'created_at',
            ])
            ->with('reasonDefinition:id,translation_key')
            ->whereDoesntHave('moderationCases')
            ->whereIn('status', ['submitted', 'received', 'triaged'])
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(fn (ForumReport $report): array => [
                'id' => $report->id,
                'subject' => $this->subjectLabel((string) $report->subject_type),
                'subject_id' => $report->subject_id,
                'reason' => $this->translation(
                    $report->reasonDefinition?->translation_key,
                    'forum_moderation.reasons.'.$report->reason,
                ),
                'priority' => $this->moderationLabel('priority', $report->priority),
                'status' => $this->moderationLabel('status', $report->status),
                'created' => $this->formatter->dateTime($report->created_at),
            ])
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function cases(): array
    {
        $administrator = $this->administrator();
        $this->gate->forUser($administrator)
            ->authorize('viewAny', ForumModerationCase::class);

        return ForumModerationCase::query()
            ->select([
                'id',
                'case_number',
                'status',
                'priority',
                'assigned_to_user_id',
                'review_due_at',
                'created_at',
            ])
            ->with('assignee:id,name')
            ->withCount(['reports', 'actions'])
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (ForumModerationCase $case): array => [
                'id' => $case->id,
                'number' => $case->case_number,
                'status' => $this->moderationLabel('status', $case->status),
                'priority' => $this->moderationLabel('priority', $case->priority),
                'assignee' => $case->assignee?->name,
                'reports' => $case->reports_count,
                'actions' => $case->actions_count,
                'review_due' => $this->formattedDateTime($case->review_due_at),
            ])
            ->all();
    }

    /** @return array<string, mixed>|null */
    #[Computed]
    public function selectedCase(): ?array
    {
        if ($this->selectedCaseId === null) {
            return null;
        }

        $case = ForumModerationCase::query()->findOrFail($this->selectedCaseId);
        $this->gate->forUser($this->administrator())->authorize('view', $case);
        $case->load([
            'assignee:id,name',
            'reports' => fn ($query) => $query
                ->select([
                    'forum_reports.id',
                    'forum_reports.reason',
                    'forum_reports.forum_report_reason_id',
                    'forum_reports.details',
                    'forum_reports.priority',
                    'forum_reports.status',
                    'forum_reports.subject_type',
                    'forum_reports.subject_id',
                    'forum_reports.affected_user_id',
                    'forum_reports.created_at',
                ])
                ->with([
                    'reasonDefinition:id,translation_key',
                    'events:id,forum_report_id,event_type,from_status,to_status,user_message_translation_key,created_at',
                ])
                ->latest('forum_reports.id')
                ->limit(100),
            'actions' => fn ($query) => $query
                ->select([
                    'id',
                    'forum_moderation_case_id',
                    'forum_moderation_action_definition_id',
                    'actor_user_id',
                    'target_user_id',
                    'rule_id',
                    'policy_basis',
                    'user_reason_translation_key',
                    'internal_reason',
                    'starts_at',
                    'ends_at',
                    'appeal_available',
                    'reversed_at',
                ])
                ->with([
                    'definition:id,stable_key,translation_key',
                    'actor:id,name',
                    'targetUser:id,name',
                ])
                ->latest('id')
                ->limit(50),
            'recusals' => fn ($query) => $query
                ->select([
                    'id',
                    'forum_moderation_case_id',
                    'moderator_user_id',
                    'reason_code',
                    'created_at',
                ])
                ->with('moderator:id,name')
                ->latest('id')
                ->limit(50),
        ]);

        return [
            'id' => $case->id,
            'number' => $case->case_number,
            'status' => $this->moderationLabel('status', $case->status),
            'priority' => $this->moderationLabel('priority', $case->priority),
            'assignee' => $case->assignee?->name,
            'reports' => $case->reports->map(fn (ForumReport $report): array => [
                'id' => $report->id,
                'reason' => $this->translation(
                    $report->reasonDefinition?->translation_key,
                    'forum_moderation.reasons.'.$report->reason,
                ),
                'details' => $report->details,
                'priority' => $this->moderationLabel('priority', $report->priority),
                'status' => $this->moderationLabel('status', $report->status),
                'subject' => $this->subjectLabel((string) $report->subject_type),
                'subject_id' => $report->subject_id,
                'events' => $report->events->map(fn (ForumReportEvent $event): array => [
                    'id' => $event->id,
                    'type' => $this->moderationLabel('event', $event->event_type),
                    'created' => $this->formattedDateTime($event->created_at),
                ])->all(),
            ])->all(),
            'actions' => $case->actions->map(fn (ForumModerationAction $action): array => [
                'id' => $action->id,
                'definition' => $this->translation(
                    $action->definition->translation_key,
                    'forum_moderation.actions.'.$action->definition->stable_key,
                ),
                'actor' => $action->actor->name,
                'target' => $action->targetUser?->name,
                'rule' => $action->rule_id,
                'policy' => $action->policy_basis,
                'internal_reason' => $action->internal_reason,
                'started' => $this->formattedDateTime($action->starts_at),
                'ends' => $this->formattedDateTime($action->ends_at),
                'appealable' => $action->appeal_available,
                'reversed' => $action->reversed_at !== null,
            ])->all(),
            'recusals' => $case->recusals->map(fn (ForumModeratorRecusal $recusal): array => [
                'id' => $recusal->id,
                'moderator' => $recusal->moderator->name,
                'reason' => $this->moderationLabel('recusal', $recusal->reason_code),
                'created' => $this->formattedDateTime($recusal->created_at),
            ])->all(),
        ];
    }

    /** @return array<int, string> */
    #[Computed]
    public function administrators(): array
    {
        $this->administrator();

        return User::query()
            ->select(['id', 'name'])
            ->where('is_admin', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function actionDefinitions(): array
    {
        $this->administrator();

        return ForumModerationActionDefinition::query()
            ->select(['id', 'stable_key', 'translation_key'])
            ->where('is_active', true)
            ->orderBy('position')
            ->get()
            ->mapWithKeys(fn (ForumModerationActionDefinition $definition): array => [
                $definition->id => $this->translation(
                    $definition->translation_key,
                    'forum_moderation.actions.'.$definition->stable_key,
                ),
            ])
            ->all();
    }

    /** @return list<array<string, int|string>> */
    #[Computed]
    public function appeals(): array
    {
        $this->administrator();

        return ForumModerationAppeal::query()
            ->select([
                'id',
                'forum_moderation_action_id',
                'appellant_user_id',
                'status',
                'reason',
                'submitted_at',
            ])
            ->with([
                'appellant:id,name',
                'moderationAction:id,forum_moderation_case_id,forum_moderation_action_definition_id,actor_user_id',
                'moderationAction.definition:id,stable_key,translation_key',
                'moderationAction.moderationCase:id,case_number',
            ])
            ->whereIn('status', ['submitted', 'appeal-review'])
            ->oldest('submitted_at')
            ->limit(50)
            ->get()
            ->map(fn (ForumModerationAppeal $appeal): array => [
                'id' => $appeal->id,
                'case' => $appeal->moderationAction->moderationCase->case_number,
                'appellant' => $appeal->appellant->name,
                'action' => $this->translation(
                    $appeal->moderationAction->definition->translation_key,
                    'forum_moderation.actions.'.$appeal->moderationAction->definition->stable_key,
                ),
                'reason' => $appeal->reason,
                'submitted' => $this->formattedDateTime($appeal->submitted_at),
            ])
            ->all();
    }

    public function openReport(int $reportId): void
    {
        $administrator = $this->administrator();
        $this->gate->forUser($administrator)->authorize('triage', ForumReport::class);
        $report = ForumReport::query()->findOrFail($reportId);
        $case = $this->openCaseAction->handle($administrator, $report);
        $this->selectedCaseId = $case->id;
        $this->assigneeUserId = $administrator->id;
        unset($this->reports, $this->cases, $this->selectedCase);
        session()->flash('feedback', __('forum_admin.feedback.case_opened'));
    }

    public function selectCase(int $caseId): void
    {
        $case = $this->authorizedCase($caseId, 'view');
        $this->selectedCaseId = $case->id;
        $this->assigneeUserId = $case->assigned_to_user_id ?? $this->administrator()->id;
        $this->resetActionForm();
        unset($this->selectedCase);
    }

    public function assignCase(): void
    {
        $validated = $this->validate([
            'selectedCaseId' => ['required', 'integer', 'exists:forum_moderation_cases,id'],
            'assigneeUserId' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('is_admin', true)->where('status', 'active'),
            ],
        ]);
        $case = $this->authorizedCase((int) $validated['selectedCaseId'], 'update');
        $assignee = User::query()->findOrFail((int) $validated['assigneeUserId']);
        $this->assignCaseAction->handle($this->administrator(), $case, $assignee);
        unset($this->reports, $this->cases, $this->selectedCase);
        session()->flash('feedback', __('forum_admin.feedback.case_assigned'));
    }

    public function applyModerationAction(): void
    {
        $validated = $this->validate([
            'selectedCaseId' => ['required', 'integer', 'exists:forum_moderation_cases,id'],
            'actionDefinitionId' => [
                'required',
                'integer',
                Rule::exists('forum_moderation_action_definitions', 'id')
                    ->where('is_active', true),
            ],
            'actionRuleId' => ['required', 'string', 'regex:/^[a-z0-9][a-z0-9._-]{2,119}$/'],
            'actionPolicyBasis' => ['required', 'string', 'min:3', 'max:180'],
            'actionInternalReason' => ['required', 'string', 'min:20', 'max:2000'],
            'actionEndsAt' => ['nullable', 'date', 'after:now'],
            'seniorApproverId' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('is_admin', true)->where('status', 'active'),
            ],
        ]);
        $administrator = $this->administrator();
        $case = $this->authorizedCase((int) $validated['selectedCaseId'], 'update');
        $definition = ForumModerationActionDefinition::query()
            ->where('is_active', true)
            ->findOrFail((int) $validated['actionDefinitionId']);
        $targetUserId = $case->reports()
            ->whereNotNull('affected_user_id')
            ->value('affected_user_id');
        $target = is_numeric($targetUserId)
            ? User::query()->find((int) $targetUserId)
            : null;
        $seniorApprover = isset($validated['seniorApproverId'])
            ? User::query()->findOrFail((int) $validated['seniorApproverId'])
            : null;
        $endsAt = filled($validated['actionEndsAt'] ?? null)
            ? CarbonImmutable::parse((string) $validated['actionEndsAt'])
            : null;

        $this->applyAction->handle(
            actor: $administrator,
            case: $case,
            definition: $definition,
            ruleId: $validated['actionRuleId'],
            policyBasis: $validated['actionPolicyBasis'],
            userReasonTranslationKey: 'forum_moderation.messages.action_applied',
            internalReason: $validated['actionInternalReason'],
            targetUser: $target,
            seniorApprover: $seniorApprover,
            endsAt: $endsAt,
        );
        $this->resetActionForm();
        unset($this->reports, $this->cases, $this->selectedCase, $this->appeals);
        session()->flash('feedback', __('forum_admin.feedback.action_recorded'));
    }

    public function recuseFromCase(): void
    {
        $validated = $this->validate([
            'selectedCaseId' => ['required', 'integer', 'exists:forum_moderation_cases,id'],
            'recusalReason' => [
                'required',
                Rule::in([
                    'personally-involved',
                    'connected-party',
                    'organization-conflict',
                    'financial-interest',
                    'prior-public-dispute',
                    'responsible-for-content',
                    'unable-to-remain-impartial',
                ]),
            ],
            'recusalPrivateNote' => ['nullable', 'string', 'max:2000'],
        ]);
        $case = $this->authorizedCase((int) $validated['selectedCaseId'], 'update');
        $this->recuseAction->handle(
            $this->administrator(),
            $case,
            $validated['recusalReason'],
            filled($validated['recusalPrivateNote'])
                ? $validated['recusalPrivateNote']
                : null,
        );
        $this->selectedCaseId = null;
        $this->assigneeUserId = null;
        $this->recusalPrivateNote = '';
        unset($this->reports, $this->cases, $this->selectedCase);
        session()->flash('feedback', __('forum_admin.feedback.recusal_recorded'));
    }

    public function selectAppeal(int $appealId): void
    {
        $this->administrator();
        $appeal = ForumModerationAppeal::query()
            ->whereIn('status', ['submitted', 'appeal-review'])
            ->findOrFail($appealId);
        $this->selectedAppealId = $appeal->id;
        $this->appealOutcome = 'upheld';
        $this->appealDecisionReason = '';
    }

    public function reviewAppeal(): void
    {
        $validated = $this->validate([
            'selectedAppealId' => [
                'required',
                'integer',
                Rule::exists('forum_moderation_appeals', 'id')
                    ->whereIn('status', ['submitted', 'appeal-review']),
            ],
            'appealOutcome' => ['required', Rule::in(['upheld', 'modified', 'reversed', 'new-review'])],
            'appealDecisionReason' => ['required', 'string', 'min:20', 'max:2000'],
        ]);
        $appeal = ForumModerationAppeal::query()
            ->findOrFail((int) $validated['selectedAppealId']);
        $this->reviewAppealAction->handle(
            $this->administrator(),
            $appeal,
            $validated['appealOutcome'],
            $validated['appealDecisionReason'],
        );
        $this->selectedAppealId = null;
        $this->appealDecisionReason = '';
        unset($this->reports, $this->cases, $this->selectedCase, $this->appeals);
        session()->flash('feedback', __('forum_admin.feedback.appeal_reviewed'));
    }

    private function authorizedCase(int $caseId, string $ability): ForumModerationCase
    {
        $case = ForumModerationCase::query()->findOrFail($caseId);
        $this->gate->forUser($this->administrator())->authorize($ability, $case);

        return $case;
    }

    private function administrator(): User
    {
        $user = $this->auth->guard()->user();

        abort_unless($user instanceof User && $user->isAdministrator() && $user->isActive(), 403);

        return $user;
    }

    private function resetActionForm(): void
    {
        $this->actionDefinitionId = null;
        $this->actionRuleId = '';
        $this->actionPolicyBasis = '';
        $this->actionInternalReason = '';
        $this->actionEndsAt = '';
        $this->seniorApproverId = null;
    }

    private function translation(?string $preferredKey, string $fallbackKey): string
    {
        return $preferredKey !== null && trans()->has($preferredKey)
            ? __($preferredKey)
            : __($fallbackKey);
    }

    private function moderationLabel(string $group, string $value): string
    {
        $key = 'forum_admin.moderation_operations.'.$group.'.'.$value;

        return trans()->has($key)
            ? __($key)
            : __('forum_admin.moderation_operations.unknown');
    }

    private function subjectLabel(string $subjectType): string
    {
        $key = match ($subjectType) {
            AdoptionCase::class => 'adoption_case',
            ForumAnswer::class => 'answer',
            ForumComment::class => 'comment',
            ForumTopic::class => 'topic',
            Listing::class => 'listing',
            default => 'unknown',
        };

        return __('forum_admin.moderation_operations.subject.'.$key);
    }

    private function formattedDateTime(DateTimeInterface|string|null $value): ?string
    {
        return $this->formatter->dateTime(
            is_string($value) ? CarbonImmutable::parse($value) : $value,
        );
    }

    public function render(): View
    {
        return view('livewire.forum.moderation-operations');
    }
}

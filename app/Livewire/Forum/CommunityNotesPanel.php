<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\AppealForumReviewPanel;
use App\Actions\ModerateCommunityNote;
use App\Actions\ProposeCommunityNote;
use App\Actions\RespondToCommunityNote;
use App\Actions\StartCommunityNoteReview;
use App\Actions\SubmitForumPanelReview;
use App\Enums\ForumCommunityNoteStatus;
use App\Enums\ForumCommunityNoteType;
use App\Enums\ForumReviewAssignmentState;
use App\Enums\ForumReviewDecision;
use App\Livewire\Forms\CommunityNoteForm;
use App\Models\ForumAnswer;
use App\Models\ForumCommunityNote;
use App\Models\ForumReviewAssignment;
use App\Models\ForumReviewPanel;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\CommunityReviewEligibility;
use App\Services\LocaleFormatter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class CommunityNotesPanel extends Component
{
    public CommunityNoteForm $form;

    #[Locked]
    public int $topicId;

    public string $authorResponse = '';

    public string $reviewDecision = 'support';

    public string $reviewReasoning = '';

    public bool $reviewHasConflict = false;

    public string $reviewConflictType = '';

    public string $moderationStatus = 'published';

    public string $moderationReason = '';

    public string $appealReason = '';

    public string $feedback = '';

    private AppealForumReviewPanel $appealAction;

    private AuthFactory $auth;

    private CommunityReviewEligibility $eligibility;

    private Gate $gate;

    private LocaleFormatter $formatter;

    private ModerateCommunityNote $moderateAction;

    private ProposeCommunityNote $proposeAction;

    private RespondToCommunityNote $respondAction;

    private StartCommunityNoteReview $startReviewAction;

    private SubmitForumPanelReview $submitReviewAction;

    public function boot(
        AuthFactory $auth,
        Gate $gate,
        LocaleFormatter $formatter,
        CommunityReviewEligibility $eligibility,
        ProposeCommunityNote $proposeAction,
        StartCommunityNoteReview $startReviewAction,
        RespondToCommunityNote $respondAction,
        SubmitForumPanelReview $submitReviewAction,
        ModerateCommunityNote $moderateAction,
        AppealForumReviewPanel $appealAction,
    ): void {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->formatter = $formatter;
        $this->eligibility = $eligibility;
        $this->proposeAction = $proposeAction;
        $this->startReviewAction = $startReviewAction;
        $this->respondAction = $respondAction;
        $this->submitReviewAction = $submitReviewAction;
        $this->moderateAction = $moderateAction;
        $this->appealAction = $appealAction;
    }

    public function mount(int $topicId): void
    {
        $this->topicId = $topicId;
        $this->gate->forUser($this->auth->user())->authorize('view', $this->topic());
    }

    /** @return array<string, string> */
    #[Computed]
    public function noteTypeOptions(): array
    {
        return collect(ForumCommunityNoteType::cases())
            ->mapWithKeys(static fn (ForumCommunityNoteType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function decisionOptions(): array
    {
        return collect(ForumReviewDecision::cases())
            ->mapWithKeys(static fn (ForumReviewDecision $decision): array => [
                $decision->value => $decision->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function moderationOptions(): array
    {
        return collect([
            ForumCommunityNoteStatus::ModeratorReview,
            ForumCommunityNoteStatus::Published,
            ForumCommunityNoteStatus::Revised,
            ForumCommunityNoteStatus::Rejected,
            ForumCommunityNoteStatus::Archived,
            ForumCommunityNoteStatus::RevalidationDue,
        ])->mapWithKeys(static fn (ForumCommunityNoteStatus $status): array => [
            $status->value => $status->label(),
        ])->all();
    }

    #[Computed]
    public function canPropose(): bool
    {
        $authenticatedUser = $this->auth->user();
        $user = $authenticatedUser instanceof User ? $authenticatedUser : null;

        return $user instanceof User && $this->eligibility->canPropose($user);
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function notes(): array
    {
        $authenticatedUser = $this->auth->user();
        $user = $authenticatedUser instanceof User ? $authenticatedUser : null;
        $topicId = $this->topicId;
        $answerIds = ForumAnswer::query()
            ->select('id')
            ->where('topic_id', $topicId);

        return ForumCommunityNote::query()
            ->select([
                'id',
                'subject_type',
                'subject_id',
                'proposer_user_id',
                'subject_author_user_id',
                'note_type',
                'status',
                'body',
                'evidence',
                'author_response',
                'jurisdiction',
                'species_context',
                'is_safety_notice',
                'forum_review_panel_id',
                'current_version',
                'published_at',
                'revalidation_due_at',
            ])
            ->with('reviewPanel:id,state,panel_type')
            ->where(static function (Builder $query) use ($answerIds, $topicId): void {
                $query
                    ->where(static function (Builder $topicQuery) use ($topicId): void {
                        $topicQuery
                            ->where('subject_type', 'forum-topic')
                            ->where('subject_id', $topicId);
                    })
                    ->orWhere(static function (Builder $answerQuery) use ($answerIds): void {
                        $answerQuery
                            ->where('subject_type', 'forum-answer')
                            ->whereIn('subject_id', $answerIds);
                    });
            })
            ->when(
                ! $user instanceof User,
                fn (Builder $query) => $query->publiclyVisible(),
            )
            ->when(
                $user instanceof User && ! $user->isAdministrator(),
                fn (Builder $query) => $query->where(static function (Builder $visibilityQuery) use ($user): void {
                    $visibilityQuery
                        ->whereIn('status', [
                            ForumCommunityNoteStatus::Published->value,
                            ForumCommunityNoteStatus::Revised->value,
                            ForumCommunityNoteStatus::RevalidationDue->value,
                        ])
                        ->orWhere('proposer_user_id', $user->id)
                        ->orWhere('subject_author_user_id', $user->id)
                        ->orWhereHas(
                            'reviewPanel.assignments',
                            fn (Builder $assignmentQuery) => $assignmentQuery
                                ->where('reviewer_user_id', $user->id),
                        );
                }),
            )
            ->latest('published_at')
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn (ForumCommunityNote $note): array => [
                'id' => $note->id,
                'type' => $note->note_type->label(),
                'status' => $note->status->label(),
                'status_value' => $note->status->value,
                'body' => $note->body,
                'evidence' => $note->evidence ?? [],
                'author_response' => $note->author_response,
                'jurisdiction' => $note->jurisdiction,
                'species_context' => $note->species_context,
                'is_safety_notice' => $note->is_safety_notice,
                'subject_label' => $note->subject_type === 'forum-answer'
                    ? __('forum_review.subject.answer')
                    : __('forum_review.subject.topic'),
                'version' => $note->current_version,
                'published' => $this->formatter->date($note->published_at),
                'revalidation_due' => $this->formatter->date($note->revalidation_due_at),
                'can_respond' => $user instanceof User
                    && $note->subject_author_user_id === $user->id
                    && ! $note->status->isPublic(),
                'can_moderate' => $user?->isAdministrator() === true,
                'can_appeal' => $user instanceof User
                    && $note->reviewPanel !== null
                    && (
                        $note->proposer_user_id === $user->id
                        || $note->subject_author_user_id === $user->id
                    ),
                'panel_id' => $note->forum_review_panel_id,
            ])
            ->all();
    }

    /** @return list<array<string, int|string>> */
    #[Computed]
    public function assignments(): array
    {
        $authenticatedUser = $this->auth->user();
        $user = $authenticatedUser instanceof User ? $authenticatedUser : null;

        if (! $user instanceof User) {
            return [];
        }

        $topicId = $this->topicId;
        $noteIds = ForumCommunityNote::query()
            ->select('id')
            ->where(static function (Builder $query) use ($topicId): void {
                $query
                    ->where(static function (Builder $topicQuery) use ($topicId): void {
                        $topicQuery
                            ->where('subject_type', 'forum-topic')
                            ->where('subject_id', $topicId);
                    })
                    ->orWhere(static function (Builder $answerQuery) use ($topicId): void {
                        $answerQuery
                            ->where('subject_type', 'forum-answer')
                            ->whereIn(
                                'subject_id',
                                ForumAnswer::query()
                                    ->select('id')
                                    ->where('topic_id', $topicId),
                            );
                    });
            });

        return ForumReviewAssignment::query()
            ->select([
                'id',
                'forum_review_panel_id',
                'reviewer_user_id',
                'state',
                'anonymous_reviewer_key',
                'review_deadline_at',
            ])
            ->where('reviewer_user_id', $user->id)
            ->where('state', ForumReviewAssignmentState::Assigned->value)
            ->whereHas(
                'panel',
                fn (Builder $query) => $query
                    ->where('subject_type', 'community-note')
                    ->whereIn('subject_id', $noteIds),
            )
            ->with('panel:id,panel_type,state,public_context,review_deadline_at')
            ->oldest('review_deadline_at')
            ->limit(20)
            ->get()
            ->map(fn (ForumReviewAssignment $assignment): array => [
                'id' => $assignment->id,
                'panel_id' => $assignment->forum_review_panel_id,
                'type' => $assignment->panel->panel_type->label(),
                'state' => $assignment->panel->state->label(),
                'title' => (string) data_get(
                    $assignment->panel->public_context,
                    'title',
                    __('forum_review.subject.note'),
                ),
                'excerpt' => (string) data_get(
                    $assignment->panel->public_context,
                    'excerpt',
                    '',
                ),
                'reviewer_key' => substr($assignment->anonymous_reviewer_key, 0, 12),
                'deadline' => $this->formatter->date($assignment->review_deadline_at),
            ])
            ->all();
    }

    public function propose(): void
    {
        $note = $this->proposeAction->handle(
            $this->user(),
            $this->form->data($this->topicId),
        );
        $this->startReviewAction->handle($this->user(), $note);
        $this->form->reset();
        $this->feedback = __('forum_review.feedback.proposed');
        unset($this->notes, $this->assignments);
    }

    public function respond(int $noteId): void
    {
        $validated = $this->validate([
            'authorResponse' => ['required', 'string', 'min:20', 'max:2000'],
        ]);
        $note = ForumCommunityNote::query()->findOrFail($noteId);
        $this->respondAction->handle(
            $this->user(),
            $note,
            (string) $validated['authorResponse'],
        );
        $this->authorResponse = '';
        $this->feedback = __('forum_review.feedback.responded');
        unset($this->notes);
    }

    public function submitReview(int $assignmentId): void
    {
        $validated = $this->validate([
            'reviewDecision' => ['required', Rule::enum(ForumReviewDecision::class)],
            'reviewReasoning' => ['required', 'string', 'min:20', 'max:2000'],
            'reviewHasConflict' => ['boolean'],
            'reviewConflictType' => [
                Rule::requiredIf($this->reviewHasConflict),
                'nullable',
                'string',
                'max:100',
            ],
        ]);
        $assignment = ForumReviewAssignment::query()
            ->findOrFail($assignmentId);
        $this->submitReviewAction->handle(
            $this->user(),
            $assignment,
            ForumReviewDecision::from((string) $validated['reviewDecision']),
            (string) $validated['reviewReasoning'],
            (bool) $validated['reviewHasConflict'],
            filled($validated['reviewConflictType'] ?? null)
                ? (string) $validated['reviewConflictType']
                : null,
        );
        $this->resetReviewForm();
        $this->feedback = __('forum_review.feedback.reviewed');
        unset($this->notes, $this->assignments);
    }

    public function moderate(int $noteId): void
    {
        $validated = $this->validate([
            'moderationStatus' => ['required', Rule::in(array_keys($this->moderationOptions()))],
            'moderationReason' => ['required', 'string', 'min:20', 'max:2000'],
        ]);
        $note = ForumCommunityNote::query()->findOrFail($noteId);
        $this->moderateAction->handle(
            $this->user(),
            $note,
            ForumCommunityNoteStatus::from((string) $validated['moderationStatus']),
            (string) $validated['moderationReason'],
        );
        $this->moderationReason = '';
        $this->feedback = __('forum_review.feedback.moderated');
        unset($this->notes);
    }

    public function appeal(int $panelId): void
    {
        $validated = $this->validate([
            'appealReason' => ['required', 'string', 'min:20', 'max:2000'],
        ]);
        $panel = ForumReviewPanel::query()->findOrFail($panelId);
        $this->appealAction->handle(
            $this->user(),
            $panel,
            (string) $validated['appealReason'],
        );
        $this->appealReason = '';
        $this->feedback = __('forum_review.feedback.appealed');
        unset($this->notes);
    }

    public function render(): View
    {
        return view('livewire.forum.community-notes-panel');
    }

    private function topic(): ForumTopic
    {
        return ForumTopic::query()
            ->select([
                'id',
                'author_id',
                'author_key',
                'status',
                'visibility',
                'is_locked',
            ])
            ->findOrFail($this->topicId);
    }

    private function user(): User
    {
        $user = $this->auth->user();

        if (! $user instanceof User || ! $user->isActive()) {
            throw new AuthorizationException;
        }

        return $user;
    }

    private function resetReviewForm(): void
    {
        $this->reviewDecision = ForumReviewDecision::Support->value;
        $this->reviewReasoning = '';
        $this->reviewHasConflict = false;
        $this->reviewConflictType = '';
    }
}

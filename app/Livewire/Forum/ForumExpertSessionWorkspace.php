<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\ArchiveForumExpertSession;
use App\Actions\CorrectForumExpertSessionAnswer;
use App\Actions\ModerateForumExpertSessionQuestion;
use App\Actions\PublishForumExpertSessionAnswer;
use App\Actions\SubmitForumExpertSessionQuestion;
use App\Actions\SubmitForumReport;
use App\Actions\WithdrawForumExpertSessionQuestion;
use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Livewire\Forms\ForumExpertAnswerForm;
use App\Livewire\Forms\ForumExpertCorrectionForm;
use App\Livewire\Forms\ForumExpertModerationForm;
use App\Livewire\Forms\ForumExpertQuestionForm;
use App\Livewire\Forms\ForumExpertSessionReportForm;
use App\Models\ForumExpertSession;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionQuestion;
use App\Models\ForumReportReason;
use App\Models\User;
use App\Services\ForumReportReasonCatalog;
use App\Services\LocaleFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ForumExpertSessionWorkspace extends Component
{
    #[Locked]
    public int $sessionId;

    public ForumExpertQuestionForm $questionForm;

    public ForumExpertAnswerForm $answerForm;

    public ForumExpertCorrectionForm $correctionForm;

    public ForumExpertModerationForm $moderationForm;

    public ForumExpertSessionReportForm $reportForm;

    public ?int $answerQuestionId = null;

    public ?int $correctionAnswerId = null;

    public ?int $moderationQuestionId = null;

    public string $reportSubjectType = 'session';

    public ?int $reportSubjectId = null;

    public string $archiveReasonCode = 'host-archived';

    public string $feedback = '';

    private ?ForumExpertSession $resolvedSession = null;

    protected LocaleFormatter $formatter;

    public function boot(LocaleFormatter $formatter): void
    {
        $this->formatter = $formatter;
    }

    public function mount(int $sessionId): void
    {
        $this->sessionId = $sessionId;
        Gate::authorize('view', $this->sessionModel());
        $this->initializeForms();
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function session(): array
    {
        $session = $this->sessionModel();
        Gate::authorize('view', $session);
        $user = Auth::user();

        return [
            'id' => $session->id,
            'title' => $session->title,
            'summary' => $session->summary,
            'host_name' => $session->host_name_snapshot,
            'host_url' => route('experts.show', $session->expertProfile),
            'scope' => $this->scopeLabel($session->professional_scope),
            'jurisdiction' => $session->jurisdiction,
            'phase' => __('forum_expert_sessions.phases.'.$session->phase()),
            'phase_key' => $session->phase(),
            'question_opens_at' => $this->formatter->dateTime(
                $session->question_opens_at,
                $session->timezone,
            ),
            'question_closes_at' => $this->formatter->dateTime(
                $session->question_closes_at,
                $session->timezone,
            ),
            'starts_at' => $this->formatter->dateTime(
                $session->starts_at,
                $session->timezone,
            ),
            'ends_at' => $this->formatter->dateTime(
                $session->ends_at,
                $session->timezone,
            ),
            'timezone' => $session->timezone,
            'status' => $session->status->label(),
            'disclaimer' => __('forum_expert_sessions.disclaimers.'.$session->disclaimer_version),
            'can_ask' => Gate::forUser($user)->allows('submitQuestion', $session),
            'can_moderate' => Gate::forUser($user)->allows('moderate', $session),
            'can_answer' => Gate::forUser($user)->allows('answer', $session),
            'can_archive' => Gate::forUser($user)->allows('archive', $session),
            'can_report' => Gate::forUser($user)->allows('report', $session),
            'lock_version' => $session->lock_version,
        ];
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function questions(): array
    {
        $session = $this->sessionModel();
        Gate::authorize('view', $session);
        $user = Auth::user();
        $canModerate = Gate::forUser($user)->allows('moderate', $session);

        return ForumExpertSessionQuestion::query()
            ->select([
                'id',
                'forum_expert_session_id',
                'author_user_id',
                'body',
                'status',
                'moderation_status',
                'queue_position',
                'moderation_reason',
                'lock_version',
                'created_at',
            ])
            ->with([
                'author:id,name',
                'answer:id,forum_expert_session_id,forum_expert_session_question_id,author_user_id,stable_key,body,source_links,status,current_version,answered_at',
                'answer.author:id,name',
                'answer.corrections:id,forum_expert_session_id,forum_expert_session_answer_id,actor_user_id,version,reason,created_at',
            ])
            ->where('forum_expert_session_id', $session->id)
            ->when(! $canModerate, function (Builder $questions) use ($user): void {
                $questions->where(function (Builder $visible) use ($user): void {
                    $visible
                        ->where(function (Builder $approved): void {
                            $approved
                                ->where(
                                    'moderation_status',
                                    ForumExpertQuestionModerationStatus::Approved->value,
                                )
                                ->whereNotIn('status', [
                                    ForumExpertQuestionStatus::Withdrawn->value,
                                    ForumExpertQuestionStatus::Removed->value,
                                ]);
                        });

                    if ($user instanceof User) {
                        $visible->orWhere('author_user_id', $user->id);
                    }
                });
            })
            ->orderBy('queue_position')
            ->limit(100)
            ->get()
            ->map(function (ForumExpertSessionQuestion $question) use ($canModerate, $session, $user): array {
                $question->setRelation('session', $session);
                $answer = $question->answer;

                if ($answer !== null) {
                    $answer->setRelation('session', $session);
                    $answer->setRelation('question', $question);
                }

                return [
                    'id' => $question->id,
                    'body' => $question->body,
                    'author_name' => $question->author->name,
                    'status' => $question->status->label(),
                    'status_key' => $question->status->value,
                    'moderation_status' => $question->moderation_status->label(),
                    'moderation_reason' => $question->moderation_reason,
                    'queue_position' => $question->queue_position,
                    'lock_version' => $question->lock_version,
                    'is_unanswered' => $question->status->isUnanswered(),
                    'can_withdraw' => Gate::forUser($user)->allows('withdraw', $question),
                    'can_moderate' => $canModerate,
                    'answer' => $answer === null ? null : [
                        'id' => $answer->id,
                        'body' => $answer->body,
                        'status' => $answer->status->label(),
                        'status_key' => $answer->status->value,
                        'source_links' => $answer->source_links,
                        'version' => $answer->current_version,
                        'author_name' => $answer->author->name,
                        'answered_at' => $this->formatter->dateTime($answer->answered_at),
                        'corrections' => $answer->corrections
                            ->sortByDesc('version')
                            ->map(fn ($correction): array => [
                                'version' => $correction->version,
                                'reason' => $correction->reason,
                                'created_at' => $this->formatter->dateTime(
                                    $correction->created_at,
                                ),
                            ])
                            ->values()
                            ->all(),
                        'can_correct' => Gate::forUser($user)->allows('correct', $answer),
                        'can_report' => Gate::forUser($user)->allows('report', $answer),
                    ],
                    'can_answer' => $canModerate
                        && $answer === null
                        && $question->moderation_status === ForumExpertQuestionModerationStatus::Approved
                        && in_array($question->status, [
                            ForumExpertQuestionStatus::Queued,
                            ForumExpertQuestionStatus::Selected,
                        ], true),
                    'can_report' => Gate::forUser($user)->allows('report', $question),
                ];
            })
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function moderationDecisionOptions(): array
    {
        return collect(['approve', 'select', 'decline', 'remove'])
            ->mapWithKeys(static fn (string $decision): array => [
                $decision => __('forum_expert_sessions.moderation_decisions.'.$decision),
            ])
            ->all();
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

    public function submitQuestion(SubmitForumExpertSessionQuestion $submit): void
    {
        $data = $this->questionForm->data();
        $submit->handle(
            $this->requireUser(),
            $this->sessionModel(),
            $data['body'],
            $data['idempotency_key'],
        );
        $this->questionForm->reset();
        $this->questionForm->idempotencyKey = (string) str()->uuid();
        $this->feedback = __('forum_expert_sessions.feedback.question_submitted');
        $this->refreshComputed();
    }

    public function withdrawQuestion(
        int $questionId,
        WithdrawForumExpertSessionQuestion $withdraw,
    ): void {
        $question = $this->question($questionId);
        $withdraw->handle($this->requireUser(), $question);
        $this->feedback = __('forum_expert_sessions.feedback.question_withdrawn');
        $this->refreshComputed();
    }

    public function prepareModeration(int $questionId): void
    {
        $question = $this->question($questionId);
        Gate::authorize('moderate', $question->session);
        $this->moderationQuestionId = $question->id;
        $this->moderationForm->expectedLockVersion = $question->lock_version;
        $this->moderationForm->decision = 'approve';
        $this->moderationForm->reason = '';
    }

    public function moderate(ModerateForumExpertSessionQuestion $moderate): void
    {
        $data = $this->moderationForm->data();
        $question = $this->question($this->requiredTarget($this->moderationQuestionId));
        $moderate->handle(
            $this->requireUser(),
            $question,
            $data['decision'],
            $data['reason'],
            $data['expected_lock_version'],
        );
        $this->moderationQuestionId = null;
        $this->moderationForm->reset();
        $this->feedback = __('forum_expert_sessions.feedback.question_moderated');
        $this->refreshComputed();
    }

    public function prepareAnswer(int $questionId): void
    {
        $question = $this->question($questionId);
        Gate::authorize('answer', $question->session);
        $this->answerQuestionId = $question->id;
        $this->answerForm->reset();
        $this->answerForm->idempotencyKey = (string) str()->uuid();
    }

    public function answer(PublishForumExpertSessionAnswer $publish): void
    {
        $data = $this->answerForm->data();
        $question = $this->question($this->requiredTarget($this->answerQuestionId));
        $publish->handle(
            $this->requireUser(),
            $question,
            $data['body'],
            $data['source_links'],
            $data['idempotency_key'],
        );
        $this->answerQuestionId = null;
        $this->answerForm->reset();
        $this->feedback = __('forum_expert_sessions.feedback.answer_published');
        $this->refreshComputed();
    }

    public function prepareCorrection(int $answerId): void
    {
        $answer = $this->answerModel($answerId);
        Gate::authorize('correct', $answer);
        $this->correctionAnswerId = $answer->id;
        $this->correctionForm->body = $answer->body;
        $this->correctionForm->sourceUrls = collect($answer->source_links)
            ->pluck('url')
            ->implode("\n");
        $this->correctionForm->expectedVersion = $answer->current_version;
        $this->correctionForm->reason = '';
    }

    public function correct(CorrectForumExpertSessionAnswer $correct): void
    {
        $data = $this->correctionForm->data();
        $answer = $this->answerModel($this->requiredTarget($this->correctionAnswerId));
        $correct->handle(
            $this->requireUser(),
            $answer,
            $data['body'],
            $data['source_links'],
            $data['reason'],
            $data['expected_version'],
        );
        $this->correctionAnswerId = null;
        $this->correctionForm->reset();
        $this->feedback = __('forum_expert_sessions.feedback.answer_corrected');
        $this->refreshComputed();
    }

    public function archive(ArchiveForumExpertSession $archive): void
    {
        $session = $this->sessionModel();
        $archive->handle(
            $this->requireUser(),
            $session,
            $this->archiveReasonCode,
            $session->lock_version,
        );
        $this->feedback = __('forum_expert_sessions.feedback.archived');
        $this->refreshComputed();
    }

    public function prepareReport(string $subjectType, ?int $subjectId): void
    {
        $subject = $this->reportSubject($subjectType, $subjectId);
        Gate::authorize('report', $subject);
        $this->reportSubjectType = $subjectType;
        $this->reportSubjectId = $subjectId;
    }

    public function report(
        SubmitForumReport $submit,
        ForumReportReasonCatalog $reasons,
    ): void {
        $data = $this->reportForm->data($reasons);
        $subject = $this->reportSubject(
            $this->reportSubjectType,
            $this->reportSubjectId,
        );
        $submit->handle(
            reporter: $this->requireUser(),
            subject: $subject,
            reasonKey: $data['reason'],
            details: $data['description'],
            truthfulnessConfirmed: $data['truthfulness_confirmed'],
            immediateSafety: $data['immediate_safety'],
            metadata: ['expert_session_id' => $this->sessionId],
        );
        $this->reportForm->reset();
        $this->reportSubjectType = 'session';
        $this->reportSubjectId = null;
        $this->feedback = __('forum_expert_sessions.feedback.report_submitted');
    }

    public function render()
    {
        return view('livewire.forum.forum-expert-session-workspace');
    }

    /** @param list<string> $relations */
    private function sessionModel(array $relations = []): ForumExpertSession
    {
        if ($this->resolvedSession === null) {
            $this->resolvedSession = ForumExpertSession::query()
                ->with([
                    'expertProfile' => static fn ($profiles) => $profiles
                        ->select([
                            'id',
                            'slug',
                            'owner_id',
                            'public_name',
                            'primary_type',
                            'specializations',
                            'country',
                            'status',
                            'verification_status',
                            'verification_expires_at',
                        ])
                        ->with([
                            'credentials' => static fn ($credentials) => $credentials
                                ->select([
                                    'id',
                                    'expert_profile_id',
                                    'status',
                                    'jurisdiction',
                                    'scope',
                                    'expires_at',
                                    'renewal_due_at',
                                    'suspended_at',
                                    'revoked_at',
                                ]),
                        ]),
                    ...$relations,
                ])
                ->findOrFail($this->sessionId);
        } elseif ($relations !== []) {
            $this->resolvedSession->loadMissing($relations);
        }

        return $this->resolvedSession;
    }

    private function question(int $questionId): ForumExpertSessionQuestion
    {
        return ForumExpertSessionQuestion::query()
            ->with('session.expertProfile')
            ->where('forum_expert_session_id', $this->sessionId)
            ->findOrFail($questionId);
    }

    private function answerModel(int $answerId): ForumExpertSessionAnswer
    {
        return ForumExpertSessionAnswer::query()
            ->with('session.expertProfile')
            ->where('forum_expert_session_id', $this->sessionId)
            ->findOrFail($answerId);
    }

    private function reportSubject(string $subjectType, ?int $subjectId): Model
    {
        validator([
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
        ], [
            'subject_type' => ['required', Rule::in(['session', 'question', 'answer'])],
            'subject_id' => [
                Rule::requiredIf($subjectType !== 'session'),
                'nullable',
                'integer',
                'min:1',
            ],
        ])->validate();

        return match ($subjectType) {
            'question' => $this->question((int) $subjectId),
            'answer' => $this->answerModel((int) $subjectId),
            default => $this->sessionModel(),
        };
    }

    private function initializeForms(): void
    {
        $this->questionForm->idempotencyKey = (string) str()->uuid();
        $this->answerForm->idempotencyKey = (string) str()->uuid();
        $this->reportForm->reason = (string) array_key_first($this->reportReasonOptions());
    }

    private function refreshComputed(): void
    {
        $this->resolvedSession = null;
        unset($this->session, $this->questions);
    }

    private function scopeLabel(string $scope): string
    {
        $key = 'forum_expert_sessions.scopes.'.$scope;

        return Lang::has($key) ? __($key) : str($scope)->replace('-', ' ')->headline()->toString();
    }

    private function requiredTarget(?int $target): int
    {
        abort_if($target === null, 404);

        return $target;
    }

    private function requireUser(): User
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 401);

        return $user;
    }
}

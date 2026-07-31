<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\EndMentorship;
use App\Actions\RespondToMentorship;
use App\Actions\SendMentorshipMessage;
use App\Actions\SubmitForumReport;
use App\Actions\SubmitMentorshipFeedback;
use App\Actions\ValidateMentorshipCompletion;
use App\Models\ForumMentorship;
use App\Models\ForumMentorshipFeedback;
use App\Models\ForumMentorshipMessage;
use App\Models\ForumReportReason;
use App\Models\User;
use App\Services\LocaleFormatter;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class MentorshipInbox extends Component
{
    public string $mentorResponse = '';

    public bool $responseSafetyAcknowledged = false;

    public string $messageBody = '';

    #[Locked]
    public string $messageIdempotencyKey;

    public string $endReason = '';

    public bool $markCompleted = false;

    public bool $blockCounterpart = false;

    public int $feedbackRating = 5;

    public string $feedbackSummary = '';

    public bool $wouldRecommend = true;

    public string $privateFeedbackNote = '';

    public string $reportDetails = '';

    public string $reportReason = 'harassment';

    public bool $reportImmediateSafety = false;

    public bool $reportAndBlock = false;

    public bool $reportTruthfulnessConfirmed = false;

    public string $feedback = '';

    private AuthFactory $auth;

    private EndMentorship $endAction;

    private Gate $gate;

    private LocaleFormatter $formatter;

    private RespondToMentorship $respondAction;

    private SendMentorshipMessage $sendMessageAction;

    private SubmitForumReport $reportAction;

    private SubmitMentorshipFeedback $feedbackAction;

    private ValidateMentorshipCompletion $validateAction;

    public function boot(
        AuthFactory $auth,
        Gate $gate,
        LocaleFormatter $formatter,
        RespondToMentorship $respondAction,
        SendMentorshipMessage $sendMessageAction,
        EndMentorship $endAction,
        SubmitMentorshipFeedback $feedbackAction,
        SubmitForumReport $reportAction,
        ValidateMentorshipCompletion $validateAction,
    ): void {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->formatter = $formatter;
        $this->respondAction = $respondAction;
        $this->sendMessageAction = $sendMessageAction;
        $this->endAction = $endAction;
        $this->feedbackAction = $feedbackAction;
        $this->reportAction = $reportAction;
        $this->validateAction = $validateAction;
    }

    public function mount(): void
    {
        $this->requireUser();
        $this->messageIdempotencyKey = (string) str()->uuid();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function mentorships(): array
    {
        $user = $this->requireUser();
        $query = ForumMentorship::query()
            ->select([
                'id',
                'forum_mentor_scope_id',
                'mentor_user_id',
                'mentee_user_id',
                'mentorship_type',
                'state',
                'language',
                'location_scope',
                'communication_preference',
                'request_message',
                'mentor_response',
                'requested_at',
                'accepted_at',
                'completed_at',
                'ended_at',
                'completion_validated_at',
                'validated_by_user_id',
                'end_reason',
                'lock_version',
                'updated_at',
            ])
            ->with([
                'mentor:id,name',
                'mentee:id,name',
                'scope:id,forum_category_id,taxon_id,experience_summary',
            ])
            ->where(function (Builder $builder) use ($user): void {
                $builder
                    ->where('mentor_user_id', $user->id)
                    ->orWhere('mentee_user_id', $user->id);

                if ($user->isAdministrator()) {
                    $builder->orWhere(function (Builder $review): void {
                        $review
                            ->where('state', 'completed')
                            ->whereNull('completion_validated_at');
                    });
                }
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(25);
        $mentorships = $query->get();
        $ids = $mentorships->pluck('id')->all();
        $messages = ForumMentorshipMessage::query()
            ->select([
                'id',
                'forum_mentorship_id',
                'sender_user_id',
                'body',
                'created_at',
            ])
            ->with('sender:id,name')
            ->whereIn('forum_mentorship_id', $ids)
            ->orderBy('created_at')
            ->orderBy('id')
            ->limit(300)
            ->get()
            ->groupBy('forum_mentorship_id');
        $feedbackAuthorIds = ForumMentorshipFeedback::query()
            ->whereIn('forum_mentorship_id', $ids)
            ->where('author_user_id', $user->id)
            ->pluck('forum_mentorship_id')
            ->all();

        return $mentorships->map(function (ForumMentorship $mentorship) use (
            $feedbackAuthorIds,
            $messages,
            $user,
        ): array {
            $thread = $messages->get($mentorship->id, collect());

            return [
                'id' => $mentorship->id,
                'state' => $mentorship->state->label(),
                'state_key' => $mentorship->state->value,
                'type' => $mentorship->mentorship_type->label(),
                'mentor_name' => $mentorship->mentor->name,
                'mentee_name' => $mentorship->mentee->name,
                'is_mentor' => $mentorship->mentor_user_id === $user->id,
                'is_mentee' => $mentorship->mentee_user_id === $user->id,
                'language' => $mentorship->language,
                'location_scope' => $mentorship->location_scope,
                'request_message' => $mentorship->request_message,
                'mentor_response' => $mentorship->mentor_response,
                'requested_at' => $this->formatter->dateTime($mentorship->requested_at),
                'ended_at' => $this->formatter->dateTime($mentorship->ended_at),
                'end_reason' => $mentorship->end_reason,
                'completion_validated' => $mentorship->completion_validated_at !== null,
                'lock_version' => $mentorship->lock_version,
                'messages' => $thread->map(fn (ForumMentorshipMessage $message): array => [
                    'id' => $message->id,
                    'sender' => $message->sender->name,
                    'is_own' => $message->sender_user_id === $user->id,
                    'body' => $message->body,
                    'created_at' => $this->formatter->dateTime($message->created_at),
                ])->all(),
                'can_respond' => $this->gate->forUser($user)->allows('respond', $mentorship),
                'can_message' => $this->gate->forUser($user)->allows('message', $mentorship),
                'can_end' => $this->gate->forUser($user)->allows('end', $mentorship),
                'can_feedback' => $this->gate->forUser($user)->allows('feedback', $mentorship)
                    && ! in_array($mentorship->id, $feedbackAuthorIds, true),
                'can_validate' => $this->gate->forUser($user)
                    ->allows('validateCompletion', $mentorship),
                'can_report' => $mentorship->isParticipant($user),
            ];
        })->all();
    }

    public function respond(
        int $mentorshipId,
        bool $accept,
        int $expectedLockVersion,
    ): void {
        $validated = $this->validate([
            'mentorResponse' => ['required', 'string', 'min:2', 'max:2000'],
            'responseSafetyAcknowledged' => [Rule::requiredIf($accept), 'boolean'],
        ]);
        $this->respondAction->handle(
            $this->requireUser(),
            $this->mentorship($mentorshipId),
            $accept,
            (string) $validated['mentorResponse'],
            (bool) $validated['responseSafetyAcknowledged'],
            $expectedLockVersion,
        );
        $this->reset('mentorResponse', 'responseSafetyAcknowledged');
        $this->feedback = $accept
            ? __('forum_mentorship.feedback.request_accepted')
            : __('forum_mentorship.feedback.request_declined');
        $this->refreshWorkspace();
    }

    public function sendMessage(int $mentorshipId): void
    {
        $validated = $this->validate([
            'messageBody' => ['required', 'string', 'min:2', 'max:4000'],
        ]);
        $this->sendMessageAction->handle(
            $this->requireUser(),
            $this->mentorship($mentorshipId),
            (string) $validated['messageBody'],
            $this->messageIdempotencyKey,
        );
        $this->reset('messageBody');
        $this->messageIdempotencyKey = (string) str()->uuid();
        $this->feedback = __('forum_mentorship.feedback.message_sent');
        $this->refreshWorkspace();
    }

    public function end(int $mentorshipId, int $expectedLockVersion): void
    {
        $validated = $this->validate([
            'endReason' => ['required', 'string', 'min:2', 'max:2000'],
            'markCompleted' => ['boolean'],
            'blockCounterpart' => ['boolean'],
        ]);
        $this->endAction->handle(
            $this->requireUser(),
            $this->mentorship($mentorshipId),
            (bool) $validated['markCompleted'],
            (string) $validated['endReason'],
            (bool) $validated['blockCounterpart'],
            $expectedLockVersion,
        );
        $this->reset('endReason', 'markCompleted', 'blockCounterpart');
        $this->feedback = __('forum_mentorship.feedback.mentorship_ended');
        $this->refreshWorkspace();
    }

    public function submitFeedback(int $mentorshipId): void
    {
        $validated = $this->validate([
            'feedbackRating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedbackSummary' => ['required', 'string', 'min:2', 'max:1000'],
            'wouldRecommend' => ['boolean'],
            'privateFeedbackNote' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->feedbackAction->handle(
            $this->requireUser(),
            $this->mentorship($mentorshipId),
            (int) $validated['feedbackRating'],
            (string) $validated['feedbackSummary'],
            (bool) $validated['wouldRecommend'],
            filled($validated['privateFeedbackNote'])
                ? (string) $validated['privateFeedbackNote']
                : null,
        );
        $this->reset('feedbackSummary', 'privateFeedbackNote');
        $this->feedbackRating = 5;
        $this->wouldRecommend = true;
        $this->feedback = __('forum_mentorship.feedback.feedback_submitted');
        $this->refreshWorkspace();
    }

    public function report(int $mentorshipId): void
    {
        $validated = $this->validate([
            'reportReason' => [
                'required',
                'string',
                Rule::exists('forum_report_reasons', 'stable_key')
                    ->where('is_active', true),
            ],
            'reportDetails' => ['required', 'string', 'min:10', 'max:3000'],
            'reportImmediateSafety' => ['boolean'],
            'reportAndBlock' => ['boolean'],
            'reportTruthfulnessConfirmed' => ['accepted'],
        ]);
        $this->reportAction->handle(
            reporter: $this->requireUser(),
            subject: $this->mentorship($mentorshipId),
            reasonKey: (string) $validated['reportReason'],
            details: (string) $validated['reportDetails'],
            truthfulnessConfirmed: (bool) $validated['reportTruthfulnessConfirmed'],
            immediateSafety: (bool) $validated['reportImmediateSafety'],
            blockAffectedUser: (bool) $validated['reportAndBlock'],
        );
        $this->reset(
            'reportDetails',
            'reportImmediateSafety',
            'reportAndBlock',
            'reportTruthfulnessConfirmed',
        );
        $this->reportReason = 'harassment';
        $this->feedback = __('forum_mentorship.feedback.report_submitted');
        $this->refreshWorkspace();
    }

    /** @return list<array{key: string, label: string, allows_immediate_safety: bool}> */
    #[Computed]
    public function reportReasonOptions(): array
    {
        return ForumReportReason::query()
            ->select([
                'stable_key',
                'translation_key',
                'allows_immediate_safety',
                'position',
            ])
            ->where('is_active', true)
            ->orderBy('position')
            ->limit(100)
            ->get()
            ->map(static fn (ForumReportReason $reason): array => [
                'key' => $reason->stable_key,
                'label' => __($reason->translation_key),
                'allows_immediate_safety' => $reason->allows_immediate_safety,
            ])
            ->all();
    }

    public function validateCompletion(int $mentorshipId): void
    {
        $this->validateAction->handle(
            $this->requireUser(),
            $this->mentorship($mentorshipId),
        );
        $this->feedback = __('forum_mentorship.feedback.completion_validated');
        $this->refreshWorkspace();
    }

    #[On('mentorship-updated')]
    public function refreshWorkspace(): void
    {
        unset($this->mentorships);
    }

    public function render(): View
    {
        return view('livewire.forum.mentorship-inbox');
    }

    private function mentorship(int $id): ForumMentorship
    {
        return ForumMentorship::query()->findOrFail($id);
    }

    private function requireUser(): User
    {
        $user = $this->auth->user();

        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }
}

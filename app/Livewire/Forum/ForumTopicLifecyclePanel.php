<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\BumpForumTopic;
use App\Actions\ChangeForumTopicState;
use App\Actions\RedirectForumTopic;
use App\Actions\RequestForumTopicUpdate;
use App\Actions\ReviewForumTopicUpdateRequest;
use App\Actions\SetForumTopicLegalHold;
use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicUpdateRequestKind;
use App\Enums\ForumTopicUpdateRequestStatus;
use App\Livewire\Forms\ForumTopicLifecycleForm;
use App\Models\ForumTopic;
use App\Models\ForumTopicLifecycleEvent;
use App\Models\ForumTopicUpdateRequest;
use App\Models\User;
use App\Services\ForumTopicLifecycleProjection;
use App\Services\LocaleFormatter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ForumTopicLifecyclePanel extends Component
{
    public ForumTopicLifecycleForm $form;

    #[Locked]
    public int $topicId;

    #[Locked]
    public int $lockVersion = 0;

    public string $moderationStatus = 'open';

    public string $feedback = '';

    private AuthFactory $auth;

    private BumpForumTopic $bumpAction;

    private ChangeForumTopicState $changeStateAction;

    private Gate $gate;

    private LocaleFormatter $formatter;

    private ForumTopicLifecycleProjection $projection;

    private RedirectForumTopic $redirectAction;

    private RequestForumTopicUpdate $requestUpdateAction;

    private ReviewForumTopicUpdateRequest $reviewRequestAction;

    private SetForumTopicLegalHold $legalHoldAction;

    public function boot(
        AuthFactory $auth,
        Gate $gate,
        LocaleFormatter $formatter,
        ForumTopicLifecycleProjection $projection,
        ChangeForumTopicState $changeStateAction,
        RequestForumTopicUpdate $requestUpdateAction,
        ReviewForumTopicUpdateRequest $reviewRequestAction,
        BumpForumTopic $bumpAction,
        RedirectForumTopic $redirectAction,
        SetForumTopicLegalHold $legalHoldAction,
    ): void {
        $this->auth = $auth;
        $this->gate = $gate;
        $this->formatter = $formatter;
        $this->projection = $projection;
        $this->changeStateAction = $changeStateAction;
        $this->requestUpdateAction = $requestUpdateAction;
        $this->reviewRequestAction = $reviewRequestAction;
        $this->bumpAction = $bumpAction;
        $this->redirectAction = $redirectAction;
        $this->legalHoldAction = $legalHoldAction;
    }

    public function mount(int $topicId): void
    {
        $this->topicId = $topicId;
        $topic = $this->topic();
        $this->gate->forUser($this->auth->user())->authorize('view', $topic);
        $this->lockVersion = $topic->lock_version;
        $this->moderationStatus = $topic->status->canonical()->value;
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function lifecycle(): array
    {
        $topic = $this->topic();
        $snapshot = $this->projection->snapshot($topic);

        return [
            'status' => $snapshot->status->value,
            'status_label' => $snapshot->status->label(),
            'is_stale' => $snapshot->isStale,
            'shows_necropost_warning' => $snapshot->showsNecropostWarning,
            'archive_review_due' => $snapshot->archiveReviewDue,
            'retention_review_due' => $snapshot->retentionReviewDue,
            'can_bump_now' => $snapshot->canBump,
            'next_bump_at' => $this->formatter->date($snapshot->nextBumpAt),
            'reference_at' => $this->formatter->date($snapshot->referenceAt),
            'has_legal_hold' => $snapshot->hasLegalHold,
        ];
    }

    /** @return array<string, bool> */
    #[Computed]
    public function abilities(): array
    {
        $topic = $this->topic();
        $user = $this->authenticatedUser();

        return [
            'request_update' => $this->gate->forUser($user)->allows('requestUpdate', $topic),
            'review_requests' => $this->gate->forUser($user)->allows('reviewUpdateRequests', $topic),
            'reopen' => $this->gate->forUser($user)->allows('reopen', $topic),
            'bump' => $this->gate->forUser($user)->allows('bump', $topic),
            'archive' => $this->gate->forUser($user)->allows('archive', $topic),
            'remove' => $this->gate->forUser($user)->allows('remove', $topic),
            'moderate' => $this->gate->forUser($user)->allows('moderateLifecycle', $topic),
            'legal_hold' => $this->gate->forUser($user)->allows('manageLegalHold', $topic),
            'redirect' => $this->gate->forUser($user)->allows('redirect', $topic),
            'restore' => $this->gate->forUser($user)->allows('restore', $topic),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function requestKindOptions(): array
    {
        return collect(ForumTopicUpdateRequestKind::cases())
            ->mapWithKeys(static fn (ForumTopicUpdateRequestKind $kind): array => [
                $kind->value => $kind->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function reviewDecisionOptions(): array
    {
        return collect([
            ForumTopicUpdateRequestStatus::Accepted,
            ForumTopicUpdateRequestStatus::Rejected,
        ])->mapWithKeys(static fn (ForumTopicUpdateRequestStatus $status): array => [
            $status->value => $status->label(),
        ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function moderationStatusOptions(): array
    {
        return collect(ForumTopicStatus::cases())
            ->filter(static fn (ForumTopicStatus $status): bool => $status === $status->canonical())
            ->reject(static fn (ForumTopicStatus $status): bool => in_array($status, [
                ForumTopicStatus::Merged,
                ForumTopicStatus::Redirected,
            ], true))
            ->mapWithKeys(static fn (ForumTopicStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function history(): array
    {
        $user = $this->authenticatedUser();
        $canModerate = $this->gate->forUser($user)
            ->allows('moderateLifecycle', $this->topic());
        $publicEvents = [
            ForumTopicLifecycleEventType::StateChanged->value,
            ForumTopicLifecycleEventType::AuthorUpdated->value,
            ForumTopicLifecycleEventType::Bumped->value,
            ForumTopicLifecycleEventType::Merged->value,
            ForumTopicLifecycleEventType::Redirected->value,
        ];

        return ForumTopicLifecycleEvent::query()
            ->select([
                'id',
                'event_type',
                'from_status',
                'to_status',
                'reason_translation_key',
                'occurred_at',
            ])
            ->where('forum_topic_id', $this->topicId)
            ->when(
                ! $canModerate,
                fn (Builder $query): Builder => $query->whereIn('event_type', $publicEvents),
            )
            ->latest('occurred_at')
            ->latest('id')
            ->limit((int) config('forum.lifecycle.visible_history_limit'))
            ->get()
            ->map(fn (ForumTopicLifecycleEvent $event): array => [
                'id' => $event->id,
                'event' => $event->event_type->label(),
                'from' => $this->statusLabel($event->from_status),
                'to' => $this->statusLabel($event->to_status),
                'reason' => $event->reason_translation_key !== null
                    ? __($event->reason_translation_key)
                    : null,
                'occurred' => $this->formatter->date($event->occurred_at),
            ])
            ->all();
    }

    /** @return list<array<string, int|string|null>> */
    #[Computed]
    public function updateRequests(): array
    {
        $user = $this->authenticatedUser();

        if (! $user instanceof User) {
            return [];
        }

        $topic = $this->topic();
        $canReview = $this->gate->forUser($user)->allows('reviewUpdateRequests', $topic);

        return ForumTopicUpdateRequest::query()
            ->select([
                'id',
                'requester_user_id',
                'kind',
                'status',
                'reason',
                'proposed_body',
                'resolution_reason',
                'lock_version',
                'created_at',
                'reviewed_at',
            ])
            ->where('forum_topic_id', $this->topicId)
            ->when(
                ! $canReview,
                fn (Builder $query): Builder => $query->where('requester_user_id', $user->id),
            )
            ->latest('created_at')
            ->latest('id')
            ->limit((int) config('forum.lifecycle.visible_request_limit'))
            ->get()
            ->map(fn (ForumTopicUpdateRequest $request): array => [
                'id' => $request->id,
                'kind' => $request->kind->label(),
                'status' => $request->status->label(),
                'status_value' => $request->status->value,
                'reason' => $request->reason,
                'proposed_body' => $request->proposed_body,
                'resolution_reason' => $request->resolution_reason,
                'lock_version' => $request->lock_version,
                'created' => $this->formatter->date($request->created_at),
                'reviewed' => $this->formatter->date($request->reviewed_at),
            ])
            ->all();
    }

    public function requestUpdate(): void
    {
        $data = $this->form->updateRequestData();
        $this->requestUpdateAction->handle(
            actor: $this->user(),
            topic: $this->topic(),
            kind: $data['kind'],
            reason: $data['reason'],
            proposedBody: $data['proposed_body'],
        );
        $this->form->reset();
        $this->feedback = __('forum_topic_lifecycle.feedback.update_requested');
        $this->refreshState();
    }

    public function reviewRequest(int $requestId, int $expectedLockVersion): void
    {
        $data = $this->form->reviewData();
        $request = ForumTopicUpdateRequest::query()
            ->where('forum_topic_id', $this->topicId)
            ->findOrFail($requestId);
        $this->reviewRequestAction->handle(
            actor: $this->user(),
            request: $request,
            decision: $data['decision'],
            resolutionReason: $data['reason'],
            expectedLockVersion: $expectedLockVersion,
        );
        $this->form->reviewReason = '';
        $this->feedback = __('forum_topic_lifecycle.feedback.request_reviewed');
        $this->refreshState();
    }

    public function changeState(string $status): void
    {
        $target = ForumTopicStatus::tryFrom($status);

        if (! $target instanceof ForumTopicStatus || $target !== $target->canonical()) {
            throw ValidationException::withMessages([
                'status' => __('forum_topic_lifecycle.validation.unsupported_state'),
            ]);
        }

        $this->changeStateAction->handle(
            actor: $this->user(),
            topic: $this->topic(),
            target: $target,
            reasonCode: "manual-{$target->value}",
            expectedLockVersion: $this->lockVersion,
        );
        $this->feedback = __('forum_topic_lifecycle.feedback.state_changed');
        $this->refreshState();
    }

    public function moderateState(): void
    {
        $this->changeState($this->moderationStatus);
    }

    public function bump(): void
    {
        $this->bumpAction->handle(
            actor: $this->user(),
            topic: $this->topic(),
            expectedLockVersion: $this->lockVersion,
        );
        $this->feedback = __('forum_topic_lifecycle.feedback.bumped');
        $this->refreshState();
    }

    public function redirectTopic(string $status): void
    {
        $targetStatus = ForumTopicStatus::tryFrom($status);

        if (! in_array($targetStatus, [
            ForumTopicStatus::Merged,
            ForumTopicStatus::Redirected,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => __('forum_topic_lifecycle.validation.redirect_state'),
            ]);
        }

        $data = $this->form->redirectData();
        $target = ForumTopic::query()
            ->where('slug', $data['slug'])
            ->firstOrFail();
        $this->redirectAction->handle(
            actor: $this->user(),
            source: $this->topic(),
            target: $target,
            redirectStatus: $targetStatus,
            reasonCode: $targetStatus === ForumTopicStatus::Merged
                ? 'moderator-merged'
                : 'moderator-redirected',
            expectedLockVersion: $this->lockVersion,
        );
        $this->feedback = __('forum_topic_lifecycle.feedback.redirected');
        $this->refreshState();
    }

    public function applyLegalHold(): void
    {
        $data = $this->form->legalHoldData();
        $this->legalHoldAction->apply(
            actor: $this->user(),
            topic: $this->topic(),
            reasonCode: $data['reason_code'],
            privateReason: $data['private_reason'],
            reviewAt: $data['review_at'],
        );
        $this->form->legalHoldPrivateReason = '';
        $this->feedback = __('forum_topic_lifecycle.feedback.hold_applied');
        $this->refreshState();
    }

    public function releaseLegalHold(): void
    {
        $this->legalHoldAction->release(
            actor: $this->user(),
            topic: $this->topic(),
            releaseReason: $this->form->legalHoldReleaseReason(),
        );
        $this->form->legalHoldReleaseReason = '';
        $this->feedback = __('forum_topic_lifecycle.feedback.hold_released');
        $this->refreshState();
    }

    public function render(): View
    {
        return view('livewire.forum.forum-topic-lifecycle-panel');
    }

    private function topic(): ForumTopic
    {
        return ForumTopic::query()->findOrFail($this->topicId);
    }

    private function user(): User
    {
        $user = $this->authenticatedUser();

        if (! $user instanceof User) {
            throw new AuthorizationException(__('forum_topic_lifecycle.validation.authentication'));
        }

        return $user;
    }

    private function authenticatedUser(): ?User
    {
        $user = $this->auth->user();

        return $user instanceof User ? $user : null;
    }

    private function refreshState(): void
    {
        $topic = $this->topic();
        $this->lockVersion = $topic->lock_version;
        $this->moderationStatus = $topic->status->canonical()->value;
        unset(
            $this->lifecycle,
            $this->abilities,
            $this->history,
            $this->updateRequests,
        );
    }

    private function statusLabel(?string $status): ?string
    {
        return $status === null
            ? null
            : ForumTopicStatus::tryFrom($status)?->label() ?? $status;
    }
}

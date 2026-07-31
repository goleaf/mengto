<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumExpertQuestionModerationStatus;
use App\Enums\ForumExpertQuestionStatus;
use App\Models\ForumExpertSessionQuestion;
use App\Models\User;
use App\Services\ForumExpertSessionAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LogicException;

final readonly class ModerateForumExpertSessionQuestion
{
    public function __construct(
        private Gate $gate,
        private ForumExpertSessionAudit $audit,
    ) {}

    public function handle(
        User $actor,
        ForumExpertSessionQuestion $question,
        string $decision,
        ?string $reason,
        int $expectedLockVersion,
    ): ForumExpertSessionQuestion {
        $session = $question->session;
        $this->gate->forUser($actor)->authorize('moderate', $session);

        $validated = validator([
            'decision' => $decision,
            'reason' => filled($reason) ? trim((string) $reason) : null,
            'lock_version' => $expectedLockVersion,
        ], [
            'decision' => ['required', Rule::in(['approve', 'select', 'decline', 'remove'])],
            'reason' => [
                Rule::requiredIf(in_array($decision, ['decline', 'remove'], true)),
                'nullable',
                'string',
                'max:1000',
            ],
            'lock_version' => ['required', 'integer', 'min:0'],
        ])->validate();

        return DB::transaction(function () use ($actor, $expectedLockVersion, $question, $validated): ForumExpertSessionQuestion {
            $locked = ForumExpertSessionQuestion::query()
                ->with('session.expertProfile')
                ->lockForUpdate()
                ->findOrFail($question->id);
            $this->gate->forUser($actor)->authorize('moderate', $locked->session);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'moderationForm.decision' => __('forum_expert_sessions.validation.concurrent_change'),
                ]);
            }

            $fromStatus = $locked->status->value;
            $decision = (string) $validated['decision'];

            if (
                in_array($locked->status, [
                    ForumExpertQuestionStatus::Answered,
                    ForumExpertQuestionStatus::Withdrawn,
                    ForumExpertQuestionStatus::Removed,
                ], true)
            ) {
                throw ValidationException::withMessages([
                    'moderationForm.decision' => __('forum_expert_sessions.validation.invalid_transition'),
                ]);
            }

            if (
                in_array($decision, ['select', 'decline'], true)
                && $locked->moderation_status !== ForumExpertQuestionModerationStatus::Approved
            ) {
                throw ValidationException::withMessages([
                    'moderationForm.decision' => __('forum_expert_sessions.validation.approval_required'),
                ]);
            }

            $attributes = match ($decision) {
                'approve' => [
                    'status' => ForumExpertQuestionStatus::Queued,
                    'moderation_status' => ForumExpertQuestionModerationStatus::Approved,
                    'moderation_reason_code' => null,
                    'moderation_reason' => null,
                ],
                'select' => [
                    'status' => ForumExpertQuestionStatus::Selected,
                    'selected_at' => now(),
                ],
                'decline' => [
                    'status' => ForumExpertQuestionStatus::Declined,
                    'declined_at' => now(),
                    'moderation_reason_code' => 'host-declined',
                    'moderation_reason' => $validated['reason'],
                ],
                'remove' => [
                    'status' => ForumExpertQuestionStatus::Removed,
                    'moderation_status' => ForumExpertQuestionModerationStatus::Rejected,
                    'removed_at' => now(),
                    'moderation_reason_code' => 'moderation-removed',
                    'moderation_reason' => $validated['reason'],
                ],
                default => throw new LogicException(
                    __('forum_expert_sessions.validation.unsupported_moderation_decision'),
                ),
            };

            $locked->forceFill([
                ...$attributes,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->audit->record(
                session: $locked->session,
                actor: $actor,
                eventType: 'question-'.$decision,
                reasonCode: 'question-'.$decision,
                summaryTranslationKey: 'forum_expert_sessions.history.question_'.$decision,
                question: $locked,
                fromStatus: $fromStatus,
                toStatus: $locked->status->value,
                idempotencyKey: "expert-session:question:{$locked->id}:{$decision}:{$locked->lock_version}",
            );

            return $locked;
        }, 3);
    }
}

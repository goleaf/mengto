<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumExpertQuestionStatus;
use App\Models\ForumExpertSessionQuestion;
use App\Models\User;
use App\Services\ForumExpertSessionAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;

final readonly class WithdrawForumExpertSessionQuestion
{
    public function __construct(
        private Gate $gate,
        private ForumExpertSessionAudit $audit,
    ) {}

    public function handle(User $actor, ForumExpertSessionQuestion $question): ForumExpertSessionQuestion
    {
        $this->gate->forUser($actor)->authorize('withdraw', $question);

        return DB::transaction(function () use ($actor, $question): ForumExpertSessionQuestion {
            $locked = ForumExpertSessionQuestion::query()
                ->with('session')
                ->lockForUpdate()
                ->findOrFail($question->id);
            $this->gate->forUser($actor)->authorize('withdraw', $locked);
            $fromStatus = $locked->status->value;

            $locked->forceFill([
                'status' => ForumExpertQuestionStatus::Withdrawn,
                'withdrawn_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->audit->record(
                session: $locked->session,
                actor: $actor,
                eventType: 'question-withdrawn',
                reasonCode: 'author-withdrew',
                summaryTranslationKey: 'forum_expert_sessions.history.question_withdrawn',
                question: $locked,
                fromStatus: $fromStatus,
                toStatus: ForumExpertQuestionStatus::Withdrawn->value,
                idempotencyKey: "expert-session:question:{$locked->id}:withdrawn",
            );

            return $locked;
        }, 3);
    }
}

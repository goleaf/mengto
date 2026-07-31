<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\KnowledgeCorrectionStatus;
use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeCorrection;
use App\Models\User;
use App\Services\KnowledgeGuideHistory;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReviewKnowledgeCorrection
{
    public function __construct(
        private readonly Gate $gate,
        private readonly KnowledgeGuideHistory $history,
    ) {}

    public function handle(
        User $actor,
        KnowledgeCorrection $correction,
        KnowledgeCorrectionStatus $decision,
        string $reason,
    ): KnowledgeCorrection {
        $correction->loadMissing('article');
        $article = $correction->article;
        abort_if($article === null, 404);

        $this->gate->forUser($actor)->authorize('reviewCorrection', $article);

        if (! in_array($decision, [
            KnowledgeCorrectionStatus::Accepted,
            KnowledgeCorrectionStatus::Rejected,
            KnowledgeCorrectionStatus::Applied,
        ], true)) {
            throw ValidationException::withMessages([
                'correctionDecision' => __('knowledge.validation.invalid_correction_decision'),
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $correction,
            $decision,
            $reason,
        ): KnowledgeCorrection {
            $locked = KnowledgeCorrection::query()
                ->lockForUpdate()
                ->findOrFail($correction->id);
            $lockedArticle = $locked->article()
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($locked->status, [
                KnowledgeCorrectionStatus::Submitted,
                KnowledgeCorrectionStatus::Accepted,
            ], true)) {
                throw ValidationException::withMessages([
                    'correctionDecision' => __('knowledge.validation.correction_already_reviewed'),
                ]);
            }

            $locked->update([
                'status' => $decision,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_at' => now(),
                'decision_reason' => trim($reason),
            ]);

            $fromStatus = $lockedArticle->status;

            if (
                $decision === KnowledgeCorrectionStatus::Accepted
                && $lockedArticle->status->isPublic()
            ) {
                $lockedArticle->update([
                    'status' => KnowledgeStatus::CorrectionRequested,
                    'lock_version' => $lockedArticle->lock_version + 1,
                ]);
            }

            $this->history->record(
                $lockedArticle,
                $actor,
                KnowledgeWorkflowEventType::CorrectionReviewed,
                'correction-reviewed',
                'knowledge.events.correction_reviewed',
                [
                    'correction_id' => $locked->id,
                    'decision' => $decision->value,
                    'reason' => trim($reason),
                ],
                fromStatus: $fromStatus->value,
                toStatus: $lockedArticle->status->value,
                versionNumber: $lockedArticle->current_version,
            );

            return $locked->refresh();
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\KnowledgeStatus;
use App\Enums\KnowledgeWorkflowEventType;
use App\Models\KnowledgeArticle;
use App\Models\User;
use App\Policies\KnowledgeArticlePolicy;
use App\Services\KnowledgeGuideHistory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TransitionKnowledgeGuide
{
    public function __construct(
        private readonly Gate $gate,
        private readonly KnowledgeGuideHistory $history,
        private readonly KnowledgeArticlePolicy $policy,
    ) {}

    public function handle(
        User $actor,
        KnowledgeArticle $article,
        KnowledgeStatus $target,
        string $reason,
        int $expectedLockVersion,
        ?KnowledgeArticle $replacement = null,
    ): KnowledgeArticle {
        $this->authorize($actor, $article, $target);

        return DB::transaction(function () use (
            $actor,
            $article,
            $target,
            $reason,
            $expectedLockVersion,
            $replacement,
        ): KnowledgeArticle {
            $locked = KnowledgeArticle::query()
                ->lockForUpdate()
                ->findOrFail($article->id);

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'workflowExpectedLockVersion' => __('knowledge.validation.edit_conflict'),
                ]);
            }

            if (! in_array($target, $locked->status->allowedTransitions(), true)) {
                throw ValidationException::withMessages([
                    'workflowStatus' => __('knowledge.validation.invalid_transition', [
                        'from' => $locked->status->label(),
                        'to' => $target->label(),
                    ]),
                ]);
            }

            if ($target === KnowledgeStatus::Replaced) {
                if ($replacement === null || $replacement->is($locked)) {
                    throw ValidationException::withMessages([
                        'replacementArticleId' => __('knowledge.validation.replacement_required'),
                    ]);
                }

                if (! $replacement->status->isPublic()) {
                    throw ValidationException::withMessages([
                        'replacementArticleId' => __('knowledge.validation.replacement_must_be_public'),
                    ]);
                }
            }

            $from = $locked->status;
            $reviewed = in_array($target, [
                KnowledgeStatus::CommunityReviewed,
                KnowledgeStatus::ExpertReviewed,
                KnowledgeStatus::Published,
            ], true);

            $locked->update([
                'status' => $target,
                'replaced_by_article_id' => $target === KnowledgeStatus::Replaced
                    ? $replacement?->id
                    : $locked->replaced_by_article_id,
                'last_reviewed_at' => $reviewed ? now() : $locked->last_reviewed_at,
                'next_review_at' => $target === KnowledgeStatus::Published
                    ? now()->addMonths(6)
                    : $locked->next_review_at,
                'published_at' => $target === KnowledgeStatus::Published
                    ? ($locked->published_at ?? now())
                    : $locked->published_at,
                'lock_version' => $locked->lock_version + 1,
            ]);

            $this->history->record(
                $locked,
                $actor,
                KnowledgeWorkflowEventType::StatusChanged,
                'workflow-transition',
                'knowledge.events.status_changed',
                [
                    'reason' => trim($reason),
                    'replacement_article_id' => $replacement?->id,
                ],
                $from->value,
                $target->value,
                $locked->current_version,
            );

            return $locked->refresh();
        });
    }

    private function authorize(
        User $actor,
        KnowledgeArticle $article,
        KnowledgeStatus $target,
    ): void {
        if (
            $target === KnowledgeStatus::CommunityReviewed
            && ! $this->policy->communityReview($actor, $article)
        ) {
            throw new AuthorizationException;
        }

        if (
            $target === KnowledgeStatus::ExpertReviewed
            && ! $this->policy->expertReview($actor, $article)
        ) {
            throw new AuthorizationException;
        }

        $ability = match ($target) {
            KnowledgeStatus::SubmittedForReview => 'update',
            KnowledgeStatus::CommunityReviewed, KnowledgeStatus::ExpertReviewed => null,
            default => 'manageWorkflow',
        };

        if ($ability !== null) {
            $this->gate->forUser($actor)->authorize($ability, $article);
        }
    }
}

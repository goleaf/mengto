<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumReviewPanelState;
use App\Models\ForumReviewPanel;
use App\Models\User;
use App\Services\ForumReviewAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AppealForumReviewPanel
{
    public function __construct(private ForumReviewAudit $audit) {}

    public function handle(
        User $appellant,
        ForumReviewPanel $panel,
        string $reason,
    ): ForumReviewPanel {
        $subjectAuthorId = data_get($panel->public_context, 'author_user_id');

        if (
            ! $appellant->isActive()
            || (
                $panel->requested_by_user_id !== $appellant->id
                && (int) $subjectAuthorId !== $appellant->id
            )
        ) {
            throw new AuthorizationException;
        }

        if (mb_strlen(trim($reason)) < 20 || mb_strlen(trim($reason)) > 2_000) {
            throw ValidationException::withMessages([
                'reason' => __('forum_review.validation.appeal_reason'),
            ]);
        }

        return DB::transaction(function () use ($appellant, $panel, $reason): ForumReviewPanel {
            $panel = ForumReviewPanel::query()
                ->lockForUpdate()
                ->findOrFail($panel->id);

            if (! in_array($panel->state, [
                ForumReviewPanelState::Decided,
                ForumReviewPanelState::Overridden,
                ForumReviewPanelState::Closed,
            ], true)) {
                throw ValidationException::withMessages([
                    'appeal' => __('forum_review.validation.appeal_unavailable'),
                ]);
            }

            $fromState = $panel->state;
            $panel->forceFill([
                'state' => ForumReviewPanelState::Appealed,
                'appealed_by_user_id' => $appellant->id,
                'appeal_reason' => trim($reason),
                'appealed_at' => now(),
                'closed_at' => null,
                'active_key' => implode(':', [
                    $panel->subject_type,
                    $panel->subject_id,
                    $panel->panel_type->value,
                ]),
            ])->save();
            $this->audit->panelEvent(
                $panel,
                $appellant,
                'appealed',
                $fromState->value,
                ForumReviewPanelState::Appealed->value,
                'panel-decision-appealed',
                [],
                "panel:{$panel->id}:appeal",
            );

            return $panel->refresh();
        }, 3);
    }
}

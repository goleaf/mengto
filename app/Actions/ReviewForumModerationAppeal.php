<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumModerationAppeal;
use App\Models\ForumReportEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ReviewForumModerationAppeal
{
    private const OUTCOMES = ['upheld', 'modified', 'reversed', 'new-review'];

    public function __construct(private Gate $gate) {}

    public function handle(
        User $reviewer,
        ForumModerationAppeal $appeal,
        string $outcome,
        string $decisionReason,
    ): ForumModerationAppeal {
        $action = $appeal->moderationAction()->firstOrFail();
        $case = $action->moderationCase()->firstOrFail();
        $this->gate->forUser($reviewer)->authorize('update', $case);

        if (
            ! $reviewer->isAdministrator()
            || $reviewer->id === $action->actor_user_id
        ) {
            throw new AuthorizationException;
        }

        if (! in_array($outcome, self::OUTCOMES, true)) {
            throw ValidationException::withMessages([
                'outcome' => __('forum_moderation.validation.invalid_appeal_outcome'),
            ]);
        }

        return DB::transaction(function () use (
            $appeal,
            $decisionReason,
            $outcome,
            $reviewer,
        ): ForumModerationAppeal {
            $appeal = ForumModerationAppeal::query()
                ->lockForUpdate()
                ->findOrFail($appeal->id);
            $action = $appeal->moderationAction()
                ->lockForUpdate()
                ->firstOrFail();
            $case = $action->moderationCase()
                ->lockForUpdate()
                ->firstOrFail();
            $this->gate->forUser($reviewer)->authorize('update', $case);

            if ($reviewer->id === $action->actor_user_id) {
                throw new AuthorizationException;
            }

            if (! in_array($appeal->status, ['submitted', 'appeal-review'], true)) {
                throw ValidationException::withMessages([
                    'appeal' => __('forum_moderation.validation.appeal_already_decided'),
                ]);
            }

            $appeal->forceFill([
                'reviewer_user_id' => $reviewer->id,
                'status' => $outcome,
                'decision_reason' => $decisionReason,
                'decided_at' => now(),
            ])->save();

            $caseStatus = match ($outcome) {
                'new-review' => 'reopened',
                default => 'resolved',
            };

            if ($outcome === 'reversed') {
                $action->forceFill(['reversed_at' => now()])->save();
            }
            $case->forceFill([
                'status' => $caseStatus,
                'resolved_at' => $caseStatus === 'resolved' ? now() : null,
            ])->save();

            $reports = $case->reports()
                ->select(['forum_reports.id', 'forum_reports.status'])
                ->limit(101)
                ->get();

            if ($reports->count() > 100) {
                throw ValidationException::withMessages([
                    'appeal' => __('forum_moderation.validation.case_report_limit'),
                ]);
            }

            foreach ($reports as $report) {
                $previousStatus = $report->status;
                $reportStatus = $caseStatus === 'reopened'
                    ? 'awaiting-review'
                    : 'resolved';
                $report->forceFill(['status' => $reportStatus])->save();
                ForumReportEvent::query()->create([
                    'forum_report_id' => $report->id,
                    'actor_user_id' => $reviewer->id,
                    'event_type' => 'appeal-decided',
                    'from_status' => $previousStatus,
                    'to_status' => $reportStatus,
                    'user_message_translation_key' => 'forum_moderation.messages.appeal_decided',
                    'metadata' => [
                        'appeal_id' => $appeal->id,
                        'outcome' => $outcome,
                    ],
                    'created_at' => now(),
                ]);
            }

            return $appeal;
        }, 3);
    }
}

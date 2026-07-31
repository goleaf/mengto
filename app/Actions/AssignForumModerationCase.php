<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumModerationCase;
use App\Models\ForumReport;
use App\Models\ForumReportEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class AssignForumModerationCase
{
    public function __construct(private Gate $gate) {}

    public function handle(
        User $actor,
        ForumModerationCase $case,
        User $assignee,
    ): ForumModerationCase {
        $this->gate->forUser($actor)->authorize('update', $case);

        if (! $assignee->isAdministrator() || ! $assignee->isActive()) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($actor, $assignee, $case): ForumModerationCase {
            $locked = ForumModerationCase::query()
                ->lockForUpdate()
                ->findOrFail($case->id);
            $this->gate->forUser($actor)->authorize('update', $locked);

            if ($locked->recusals()->where('moderator_user_id', $actor->id)->exists()
                || $locked->recusals()->where('moderator_user_id', $assignee->id)->exists()
            ) {
                throw new AuthorizationException;
            }

            if (in_array($locked->status, [
                'actioned',
                'no-violation-found',
                'resolved',
                'closed',
            ], true)) {
                throw ValidationException::withMessages([
                    'selectedCaseId' => __('forum_moderation.validation.closed_case_assignment'),
                ]);
            }

            $previousAssigneeId = $locked->assigned_to_user_id;
            $locked->forceFill([
                'assigned_to_user_id' => $assignee->id,
                'status' => 'assigned',
            ])->save();

            $reports = $locked->reports()
                ->select(['forum_reports.id', 'forum_reports.status'])
                ->limit(101)
                ->get();

            if ($reports->count() > 100) {
                throw ValidationException::withMessages([
                    'selectedCaseId' => __('forum_moderation.validation.case_report_limit'),
                ]);
            }

            foreach ($reports as $report) {
                $this->recordReportAssignment(
                    $report,
                    $actor,
                    $assignee,
                    $previousAssigneeId,
                );
            }

            return $locked->refresh();
        }, 3);
    }

    private function recordReportAssignment(
        ForumReport $report,
        User $actor,
        User $assignee,
        ?int $previousAssigneeId,
    ): void {
        $previousStatus = $report->status;
        $report->forceFill(['status' => 'assigned'])->save();
        ForumReportEvent::query()->create([
            'forum_report_id' => $report->id,
            'actor_user_id' => $actor->id,
            'event_type' => $previousAssigneeId === null
                ? 'case-assigned'
                : 'case-reassigned',
            'from_status' => $previousStatus,
            'to_status' => 'assigned',
            'user_message_translation_key' => 'forum_moderation.messages.case_assigned',
            'metadata' => [
                'assignee_user_id' => $assignee->id,
                'previous_assignee_user_id' => $previousAssigneeId,
            ],
            'created_at' => now(),
        ]);
    }
}

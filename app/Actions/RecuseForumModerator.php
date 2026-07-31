<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumModerationCase;
use App\Models\ForumModeratorRecusal;
use App\Models\ForumReportEvent;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class RecuseForumModerator
{
    /** @var list<string> */
    private const REASON_CODES = [
        'personally-involved',
        'connected-party',
        'organization-conflict',
        'financial-interest',
        'prior-public-dispute',
        'responsible-for-content',
        'unable-to-remain-impartial',
    ];

    public function __construct(private Gate $gate) {}

    public function handle(
        User $moderator,
        ForumModerationCase $case,
        string $reasonCode,
        ?string $privateNote = null,
    ): ForumModeratorRecusal {
        $this->gate->forUser($moderator)->authorize('update', $case);

        if (! $moderator->isAdministrator()) {
            throw new AuthorizationException;
        }

        if (! in_array($reasonCode, self::REASON_CODES, true)) {
            throw ValidationException::withMessages([
                'reason_code' => __('forum_moderation.validation.invalid_recusal_reason'),
            ]);
        }

        return DB::transaction(function () use (
            $case,
            $moderator,
            $privateNote,
            $reasonCode,
        ): ForumModeratorRecusal {
            $case = ForumModerationCase::query()
                ->lockForUpdate()
                ->findOrFail($case->id);
            $this->gate->forUser($moderator)->authorize('update', $case);
            $recusal = ForumModeratorRecusal::query()->firstOrCreate(
                [
                    'forum_moderation_case_id' => $case->id,
                    'moderator_user_id' => $moderator->id,
                ],
                [
                    'reason_code' => $reasonCode,
                    'private_note' => $privateNote,
                    'created_at' => now(),
                ],
            );

            $caseWasUnassigned = $case->assigned_to_user_id === $moderator->id;

            if ($caseWasUnassigned) {
                $case->forceFill([
                    'assigned_to_user_id' => null,
                    'status' => 'awaiting-review',
                ])->save();
            }

            $reports = $case->reports()
                ->select(['forum_reports.id', 'forum_reports.status'])
                ->limit(101)
                ->get();

            if ($reports->count() > 100) {
                throw ValidationException::withMessages([
                    'selectedCaseId' => __('forum_moderation.validation.case_report_limit'),
                ]);
            }

            foreach ($reports as $report) {
                $previousStatus = $report->status;
                $reportStatus = $caseWasUnassigned ? 'awaiting-review' : $previousStatus;
                $report->forceFill(['status' => $reportStatus])->save();
                ForumReportEvent::query()->create([
                    'forum_report_id' => $report->id,
                    'actor_user_id' => $moderator->id,
                    'event_type' => 'moderator-recused',
                    'from_status' => $previousStatus,
                    'to_status' => $reportStatus,
                    'user_message_translation_key' => 'forum_moderation.messages.moderator_recused',
                    'metadata' => ['reason_code' => $reasonCode],
                    'created_at' => now(),
                ]);
            }

            return $recusal;
        }, 3);
    }
}

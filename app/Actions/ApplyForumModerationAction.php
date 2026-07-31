<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumModerationAction;
use App\Models\ForumModerationActionDefinition;
use App\Models\ForumModerationCase;
use App\Models\ForumReportEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ApplyForumModerationAction
{
    public function __construct(private Gate $gate) {}

    /**
     * @param  array<string, mixed>  $evidence
     */
    public function handle(
        User $actor,
        ForumModerationCase $case,
        ForumModerationActionDefinition $definition,
        string $ruleId,
        string $policyBasis,
        string $userReasonTranslationKey,
        string $internalReason,
        ?User $targetUser = null,
        ?User $seniorApprover = null,
        ?CarbonImmutable $endsAt = null,
        array $evidence = [],
    ): ForumModerationAction {
        $this->gate->forUser($actor)->authorize('update', $case);

        if (
            ! $actor->isAdministrator()
            || $case->recusals()->where('moderator_user_id', $actor->id)->exists()
        ) {
            throw new AuthorizationException;
        }

        if ($definition->requires_end_at && $endsAt === null) {
            throw ValidationException::withMessages([
                'ends_at' => __('forum_moderation.validation.end_required'),
            ]);
        }

        if (
            $definition->requires_senior_review
            && (
                ! $seniorApprover instanceof User
                || ! $seniorApprover->isAdministrator()
                || $seniorApprover->id === $actor->id
            )
        ) {
            throw ValidationException::withMessages([
                'senior_approver' => __('forum_moderation.validation.independent_review_required'),
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $case,
            $definition,
            $endsAt,
            $evidence,
            $internalReason,
            $policyBasis,
            $ruleId,
            $seniorApprover,
            $targetUser,
            $userReasonTranslationKey,
        ): ForumModerationAction {
            $case = ForumModerationCase::query()
                ->lockForUpdate()
                ->findOrFail($case->id);
            $this->gate->forUser($actor)->authorize('update', $case);
            $definition = ForumModerationActionDefinition::query()
                ->where('is_active', true)
                ->findOrFail($definition->id);

            if ($case->recusals()->where('moderator_user_id', $actor->id)->exists()) {
                throw new AuthorizationException;
            }

            if ($definition->requires_end_at && $endsAt === null) {
                throw ValidationException::withMessages([
                    'ends_at' => __('forum_moderation.validation.end_required'),
                ]);
            }

            if (
                $definition->requires_senior_review
                && (
                    ! $seniorApprover instanceof User
                    || ! $seniorApprover->isAdministrator()
                    || $seniorApprover->id === $actor->id
                )
            ) {
                throw ValidationException::withMessages([
                    'senior_approver' => __('forum_moderation.validation.independent_review_required'),
                ]);
            }

            $action = ForumModerationAction::query()->create([
                'forum_moderation_case_id' => $case->id,
                'forum_moderation_action_definition_id' => $definition->id,
                'actor_user_id' => $actor->id,
                'target_user_id' => $targetUser?->id,
                'target_type' => $case->subject_type,
                'target_id' => $case->subject_id,
                'rule_id' => $ruleId,
                'policy_basis' => $policyBasis,
                'scope_type' => 'global',
                'scope_key' => 'global',
                'user_reason_translation_key' => $userReasonTranslationKey,
                'internal_reason' => $internalReason,
                'evidence' => $evidence,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'review_at' => $endsAt,
                'appeal_available' => $definition->is_appealable,
                'metadata' => [
                    'senior_approver_id' => $seniorApprover?->id,
                    'hidden_punishment' => false,
                ],
            ]);
            $case->forceFill([
                'status' => $definition->stable_key === 'no-action'
                    ? 'no-violation-found'
                    : 'actioned',
                'resolved_at' => now(),
            ])->save();
            $reportStatus = $definition->stable_key === 'no-action'
                ? 'no-violation-found'
                : 'actioned';
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
                $report->forceFill(['status' => $reportStatus])->save();
                ForumReportEvent::query()->create([
                    'forum_report_id' => $report->id,
                    'actor_user_id' => $actor->id,
                    'event_type' => 'moderation-action-recorded',
                    'from_status' => $previousStatus,
                    'to_status' => $reportStatus,
                    'user_message_translation_key' => $userReasonTranslationKey,
                    'metadata' => [
                        'moderation_action_id' => $action->id,
                        'action_definition' => $definition->stable_key,
                    ],
                    'created_at' => now(),
                ]);
            }

            return $action;
        }, 3);
    }
}

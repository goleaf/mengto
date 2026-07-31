<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ReputationEventData;
use App\Enums\ForumMentorshipEventType;
use App\Enums\ForumMentorshipState;
use App\Models\ForumBadge;
use App\Models\ForumMentorship;
use App\Models\ForumReport;
use App\Models\ForumUserBadge;
use App\Models\User;
use App\Services\MentorshipAudit;
use App\Services\MentorshipEligibility;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class ValidateMentorshipCompletion
{
    public function __construct(
        private Gate $gate,
        private MentorshipEligibility $eligibility,
        private MentorshipAudit $audit,
        private RecordReputationEvent $recordReputationEvent,
    ) {}

    public function handle(
        User $reviewer,
        ForumMentorship $mentorship,
    ): ForumMentorship {
        return DB::transaction(function () use ($mentorship, $reviewer): ForumMentorship {
            $locked = ForumMentorship::query()
                ->with('scope:id,forum_category_id,taxon_id')
                ->lockForUpdate()
                ->findOrFail($mentorship->id);
            $this->gate->forUser($reviewer)->authorize('validateCompletion', $locked);

            if ($locked->completion_validated_at !== null) {
                return $locked;
            }

            if (
                $locked->mentee_safety_acknowledged_at === null
                || $locked->mentor_safety_acknowledged_at === null
            ) {
                throw ValidationException::withMessages([
                    'mentorship' => __('forum_mentorship.validation.missing_safety_evidence'),
                ]);
            }

            $participantMessageIds = $locked->messages()
                ->whereIn('sender_user_id', [
                    $locked->mentor_user_id,
                    $locked->mentee_user_id,
                ])
                ->distinct()
                ->pluck('sender_user_id');

            if ($participantMessageIds->count() !== 2 || $locked->messages()->count() < 2) {
                throw ValidationException::withMessages([
                    'mentorship' => __('forum_mentorship.validation.insufficient_completion_evidence'),
                ]);
            }

            $mentor = User::query()->findOrFail($locked->mentor_user_id);
            $mentee = User::query()->findOrFail($locked->mentee_user_id);

            if ($this->eligibility->usersBlockEachOther($mentor, $mentee)) {
                throw ValidationException::withMessages([
                    'mentorship' => __('forum_mentorship.validation.blocked_completion'),
                ]);
            }

            if (ForumReport::query()
                ->where('subject_type', ForumMentorship::class)
                ->where('subject_id', (string) $locked->id)
                ->whereNotIn('status', [
                    'resolved',
                    'closed',
                    'no-violation-found',
                ])
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'mentorship' => __('forum_mentorship.validation.open_report'),
                ]);
            }

            $locked->update([
                'completion_validated_at' => now(),
                'validated_by_user_id' => $reviewer->id,
                'lock_version' => $locked->lock_version + 1,
            ]);

            $this->recordReputationEvent->handle(new ReputationEventData(
                recipient: $mentor,
                dimension: 'mentoring',
                eventType: 'validated-mentorship-completion',
                sourceEntityType: ForumMentorship::class,
                sourceEntityId: (string) $locked->id,
                amount: 5,
                reasonCode: 'validated-mentorship-completion',
                explanationTranslationKey: 'forum_mentorship.reputation.completion',
                idempotencyKey: "mentorship:{$locked->id}:reputation",
                actor: $reviewer,
                forumCategoryId: $locked->scope->forum_category_id,
                taxonId: $locked->scope->taxon_id,
                locationScopeKey: $locked->location_scope,
                metadata: [
                    'mentorship_type' => $locked->mentorship_type->value,
                    'validation_method' => 'independent-administrator-review',
                ],
            ));

            $badge = ForumBadge::query()
                ->where('stable_key', 'mentor')
                ->where('is_active', true)
                ->firstOrFail();
            ForumUserBadge::query()->firstOrCreate(
                [
                    'user_id' => $mentor->id,
                    'forum_badge_id' => $badge->id,
                    'scope_key' => 'global',
                ],
                [
                    'granted_by_user_id' => $reviewer->id,
                    'status' => 'active',
                    'reason_code' => 'validated-mentorship-completion',
                    'is_public' => true,
                    'granted_at' => now(),
                    'metadata' => [
                        'criteria_version' => $badge->criteria_version,
                        'source_mentorship_id' => $locked->id,
                    ],
                ],
            );

            $this->audit->record(
                mentorship: $locked,
                actor: $reviewer,
                eventType: ForumMentorshipEventType::CompletionValidated,
                reasonCode: 'completion-validated',
                summaryTranslationKey: 'forum_mentorship.events.completion-validated',
                fromState: ForumMentorshipState::Completed,
                toState: ForumMentorshipState::Completed,
                metadata: [
                    'reputation_amount' => 5,
                    'badge_key' => 'mentor',
                ],
                idempotencyKey: "mentorship:{$locked->id}:completion-validated",
            );

            return $locked->refresh();
        }, 3);
    }
}

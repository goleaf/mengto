<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumGroupEventType;
use App\Enums\ForumMentorshipEventType;
use App\Models\AdoptionCase;
use App\Models\ForumAnswer;
use App\Models\ForumBlock;
use App\Models\ForumComment;
use App\Models\ForumGroup;
use App\Models\ForumMentorship;
use App\Models\ForumReport;
use App\Models\ForumReportEvent;
use App\Models\ForumReportReason;
use App\Models\ForumTopic;
use App\Models\Listing;
use App\Models\SearchCase;
use App\Models\Sighting;
use App\Models\User;
use App\Services\ForumGroupAudit;
use App\Services\ForumReportReasonCatalog;
use App\Services\MentorshipAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class SubmitForumReport
{
    public function __construct(
        private ForumReportReasonCatalog $reasons,
        private MentorshipAudit $mentorshipAudit,
        private ForumGroupAudit $groupAudit,
        private Gate $gate,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        User $reporter,
        Model $subject,
        string $reasonKey,
        ?string $details,
        bool $truthfulnessConfirmed,
        bool $immediateSafety = false,
        bool $blockAffectedUser = false,
        string $contactPreference = 'platform',
        ?string $locationScope = null,
        array $metadata = [],
    ): ForumReport {
        if (! $reporter->isActive()) {
            throw new AuthorizationException;
        }

        if (! $truthfulnessConfirmed) {
            throw ValidationException::withMessages([
                'truthfulness_confirmed' => __('forum_moderation.validation.truthfulness_required'),
            ]);
        }

        if (! in_array($subject::class, [
            ForumTopic::class,
            ForumAnswer::class,
            ForumComment::class,
            Listing::class,
            AdoptionCase::class,
            SearchCase::class,
            Sighting::class,
            ForumMentorship::class,
            ForumGroup::class,
        ], true)) {
            throw ValidationException::withMessages([
                'subject' => __('forum_moderation.validation.unsupported_subject'),
            ]);
        }

        if ($subject instanceof ForumMentorship && ! $subject->isParticipant($reporter)) {
            throw new AuthorizationException;
        }

        if ($subject instanceof ForumGroup) {
            $this->gate->forUser($reporter)->authorize('report', $subject);
        }

        $canonicalReason = $this->reasons->canonicalKey($reasonKey);
        $reason = ForumReportReason::query()
            ->where('stable_key', $canonicalReason)
            ->where('is_active', true)
            ->firstOrFail();

        if ($immediateSafety && ! $reason->allows_immediate_safety) {
            throw ValidationException::withMessages([
                'immediate_safety' => __('forum_moderation.validation.immediate_safety_not_available'),
            ]);
        }

        if (ForumReport::query()
            ->where('reporter_id', $reporter->id)
            ->where('created_at', '>=', now()->subHour())
            ->count() >= 10
        ) {
            throw ValidationException::withMessages([
                'report' => __('forum_moderation.validation.rate_limited'),
            ]);
        }

        return DB::transaction(function () use (
            $blockAffectedUser,
            $contactPreference,
            $details,
            $immediateSafety,
            $locationScope,
            $metadata,
            $reason,
            $reporter,
            $subject,
        ): ForumReport {
            $topic = $this->topicForSubject($subject);
            $affectedUser = $this->authorForSubject($subject, $reporter);
            $deduplicationKey = hash('sha256', implode('|', [
                $subject::class,
                (string) $subject->getKey(),
                $reason->stable_key,
            ]));
            $report = ForumReport::query()->create([
                'topic_id' => $topic?->id,
                'answer_id' => $subject instanceof ForumAnswer ? $subject->id : null,
                'comment_id' => $subject instanceof ForumComment ? $subject->id : null,
                'subject_type' => $subject::class,
                'subject_id' => (string) $subject->getKey(),
                'reporter_id' => $reporter->id,
                'reporter_key' => $reporter->actor_key,
                'reason' => $reason->stable_key,
                'forum_report_reason_id' => $reason->id,
                'details' => $details,
                'priority' => $immediateSafety ? 'critical' : $reason->default_priority,
                'status' => 'received',
                'affected_user_id' => $affectedUser?->id,
                'urgency' => $immediateSafety ? 'critical' : 'standard',
                'location_scope' => $locationScope,
                'contact_preference' => $contactPreference,
                'immediate_safety' => $immediateSafety,
                'truthfulness_confirmed' => true,
                'deduplication_key' => $deduplicationKey,
                'metadata' => $metadata,
            ]);

            ForumReportEvent::query()->create([
                'forum_report_id' => $report->id,
                'actor_user_id' => $reporter->id,
                'event_type' => 'submitted',
                'to_status' => 'received',
                'user_message_translation_key' => 'forum_moderation.messages.report_submitted',
                'metadata' => [
                    'immediate_safety' => $immediateSafety,
                    'specialist_review' => $reason->requires_specialist_review,
                ],
                'created_at' => now(),
            ]);

            if (
                $blockAffectedUser
                && $affectedUser instanceof User
                && $affectedUser->id !== $reporter->id
            ) {
                ForumBlock::query()->updateOrCreate(
                    [
                        'user_key' => $reporter->actor_key,
                        'blocked_author_key' => $affectedUser->actor_key,
                    ],
                    ['reason' => 'reported-content'],
                );
            }

            if ($subject instanceof ForumMentorship) {
                $this->mentorshipAudit->record(
                    mentorship: $subject,
                    actor: $reporter,
                    eventType: ForumMentorshipEventType::Reported,
                    reasonCode: 'mentorship-reported',
                    summaryTranslationKey: 'forum_mentorship.events.reported',
                    metadata: [
                        'report_id' => $report->id,
                        'reason_key' => $reason->stable_key,
                    ],
                    idempotencyKey: "mentorship:{$subject->id}:report:{$report->id}",
                );
            }

            if ($subject instanceof ForumGroup) {
                $this->groupAudit->record(
                    group: $subject,
                    actor: $reporter,
                    eventType: ForumGroupEventType::Reported,
                    reasonCode: 'group-reported',
                    summaryTranslationKey: 'forum_groups.events.reported',
                    metadata: [
                        'report_id' => $report->id,
                        'reason_key' => $reason->stable_key,
                    ],
                    idempotencyKey: "group:{$subject->id}:report:{$report->id}",
                );
            }

            return $report;
        }, 3);
    }

    private function topicForSubject(Model $subject): ?ForumTopic
    {
        return match (true) {
            $subject instanceof ForumTopic => $subject,
            $subject instanceof ForumAnswer => ForumTopic::query()->find($subject->topic_id),
            $subject instanceof ForumComment => ForumTopic::query()->find($subject->topic_id),
            default => null,
        };
    }

    private function authorForSubject(Model $subject, User $reporter): ?User
    {
        $authorId = match (true) {
            $subject instanceof ForumTopic,
            $subject instanceof ForumAnswer,
            $subject instanceof ForumComment => $subject->author_id,
            $subject instanceof Listing => $subject->owner_id,
            $subject instanceof AdoptionCase => $subject->listing()->value('owner_id'),
            $subject instanceof SearchCase => $subject->owner_id,
            $subject instanceof Sighting => $subject->searchCase()->value('owner_id'),
            $subject instanceof ForumMentorship => $subject->counterpartId($reporter),
            $subject instanceof ForumGroup => $subject->owner_user_id,
            default => null,
        };

        return $authorId === null ? null : User::query()->find($authorId);
    }
}

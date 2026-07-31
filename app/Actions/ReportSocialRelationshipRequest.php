<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialRequestStatus;
use App\Models\ForumReport;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use App\Services\ForumActor;
use App\Services\SocialGraphCache;
use App\Services\SocialRelationshipEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReportSocialRelationshipRequest
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SubmitForumReport $reports,
        private readonly BlockSocialAccount $blockAccount,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
    ) {}

    public function handle(
        SocialRelationshipRequest $request,
        string $reasonKey,
        ?string $details,
        bool $truthfulnessConfirmed,
        bool $blockAccount,
        string $idempotencyKey,
    ): ForumReport {
        $reporter = $this->actor->requireUser();

        return DB::transaction(function () use (
            $request,
            $reasonKey,
            $details,
            $truthfulnessConfirmed,
            $blockAccount,
            $idempotencyKey,
            $reporter,
        ): ForumReport {
            $locked = SocialRelationshipRequest::query()
                ->with(['createdBy', 'sourceActor', 'targetActor'])
                ->lockForUpdate()
                ->findOrFail($request->id);
            $existingReport = ForumReport::query()
                ->where('idempotency_key', "{$idempotencyKey}:report")
                ->first();

            if ($existingReport instanceof ForumReport) {
                $this->gate->authorize('view', $locked);

                if ($existingReport->reporter_id !== $reporter->id) {
                    throw ValidationException::withMessages([
                        'idempotency_key' => __('social_relationships.validation.idempotency_conflict'),
                    ]);
                }

                return $existingReport;
            }

            $this->gate->authorize('report', $locked);

            if ($locked->createdBy === null) {
                throw ValidationException::withMessages([
                    'request' => __('social_relationships.validation.request_unavailable'),
                ]);
            }

            $report = $this->reports->handle(
                reporter: $reporter,
                subject: $locked,
                reasonKey: $reasonKey,
                details: $details,
                truthfulnessConfirmed: $truthfulnessConfirmed,
                metadata: ['channel' => 'social-relationship-request'],
                idempotencyKey: "{$idempotencyKey}:report",
            );
            $existingEvent = SocialRelationshipEvent::query()
                ->where('idempotency_key', "{$idempotencyKey}:event")
                ->first();

            if ($existingEvent instanceof SocialRelationshipEvent) {
                return $report;
            }

            if ($blockAccount) {
                $this->blockAccount->handle(
                    source: $locked->targetActor,
                    target: $locked->sourceActor,
                    blockedUser: $locked->createdBy,
                    idempotencyKey: "{$idempotencyKey}:block",
                    reasonCode: 'reported-contact',
                );
                $locked->refresh();
            }

            $fromStatus = $locked->status->value;
            $locked->forceFill([
                'status' => SocialRequestStatus::RemovedAfterReport,
                'active_key' => null,
                'decided_by_user_id' => $reporter->id,
                'reason_code' => 'reported-contact',
                'decided_at' => now(),
                'repeat_after' => null,
                'prevent_repeats' => true,
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->events->record(
                source: $locked->sourceActor,
                target: $locked->targetActor,
                representedActor: $locked->targetActor,
                actor: $reporter,
                eventType: 'request-removed-after-report',
                type: $locked->relationship_type,
                idempotencyKey: "{$idempotencyKey}:event",
                fromStatus: $fromStatus,
                toStatus: SocialRequestStatus::RemovedAfterReport->value,
                reasonCode: $reasonKey,
                request: $locked,
                privateMetadata: ['forum_report_id' => $report->id],
            );
            $this->cache->invalidate($locked->sourceActor, $locked->targetActor);

            return $report;
        }, 3);
    }
}

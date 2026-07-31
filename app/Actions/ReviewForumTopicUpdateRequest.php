<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicUpdateRequestStatus;
use App\Models\ForumTopic;
use App\Models\ForumTopicUpdateRequest;
use App\Models\User;
use App\Services\ForumTopicLifecycle;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class ReviewForumTopicUpdateRequest
{
    public function __construct(
        private Gate $gate,
        private ForumTopicLifecycle $lifecycle,
    ) {}

    public function handle(
        User $actor,
        ForumTopicUpdateRequest $request,
        ForumTopicUpdateRequestStatus $decision,
        string $resolutionReason,
        int $expectedLockVersion,
    ): ForumTopicUpdateRequest {
        Validator::make([
            'decision' => $decision->value,
            'resolution_reason' => trim($resolutionReason),
        ], [
            'decision' => [
                'required',
                Rule::in([
                    ForumTopicUpdateRequestStatus::Accepted->value,
                    ForumTopicUpdateRequestStatus::Rejected->value,
                ]),
            ],
            'resolution_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ])->validate();

        return DB::transaction(function () use (
            $actor,
            $request,
            $decision,
            $resolutionReason,
            $expectedLockVersion,
        ): ForumTopicUpdateRequest {
            $locked = ForumTopicUpdateRequest::query()
                ->lockForUpdate()
                ->findOrFail($request->id);
            $topic = ForumTopic::query()
                ->lockForUpdate()
                ->findOrFail($locked->forum_topic_id);
            $this->gate->forUser($actor)->authorize('reviewUpdateRequests', $topic);

            if ($locked->status !== ForumTopicUpdateRequestStatus::Pending) {
                throw ValidationException::withMessages([
                    'decision' => __('forum_topic_lifecycle.validation.request_final'),
                ]);
            }

            if ($locked->lock_version !== $expectedLockVersion) {
                throw ValidationException::withMessages([
                    'decision' => __('forum_topic_lifecycle.validation.stale_request'),
                ]);
            }

            $locked->forceFill([
                'status' => $decision,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_at' => now(),
                'resolution_reason' => trim($resolutionReason),
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $this->lifecycle->record(
                topic: $topic,
                type: ForumTopicLifecycleEventType::UpdateReviewed,
                actor: $actor,
                reasonCode: "update-{$decision->value}",
                metadata: ['update_request_id' => $locked->id],
                idempotencyKey: "topic-update-review:{$locked->id}:{$locked->lock_version}",
            );

            return $locked->refresh();
        }, 3);
    }
}

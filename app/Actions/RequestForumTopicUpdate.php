<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicLifecycleEventType;
use App\Enums\ForumTopicUpdateRequestKind;
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

final readonly class RequestForumTopicUpdate
{
    public function __construct(
        private Gate $gate,
        private ForumTopicLifecycle $lifecycle,
    ) {}

    public function handle(
        User $actor,
        ForumTopic $topic,
        ForumTopicUpdateRequestKind $kind,
        string $reason,
        ?string $proposedBody = null,
    ): ForumTopicUpdateRequest {
        $this->gate->forUser($actor)->authorize(
            $kind === ForumTopicUpdateRequestKind::CommunityProposal
                ? 'proposeUpdate'
                : 'requestUpdate',
            $topic,
        );
        $reason = trim($reason);
        $proposedBody = filled($proposedBody) ? trim((string) $proposedBody) : null;
        Validator::make([
            'kind' => $kind->value,
            'reason' => $reason,
            'proposed_body' => $proposedBody,
        ], [
            'kind' => ['required', Rule::enum(ForumTopicUpdateRequestKind::class)],
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
            'proposed_body' => [
                Rule::requiredIf($kind === ForumTopicUpdateRequestKind::CommunityProposal),
                'nullable',
                'string',
                'min:40',
                'max:10000',
            ],
        ])->validate();
        $idempotencyKey = hash('sha256', implode('|', [
            (string) $topic->id,
            (string) $actor->id,
            $kind->value,
            $reason,
            $proposedBody ?? '',
        ]));

        return DB::transaction(function () use (
            $actor,
            $topic,
            $kind,
            $reason,
            $proposedBody,
            $idempotencyKey,
        ): ForumTopicUpdateRequest {
            $existing = ForumTopicUpdateRequest::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing instanceof ForumTopicUpdateRequest) {
                return $existing;
            }

            $locked = ForumTopic::query()->lockForUpdate()->findOrFail($topic->id);
            User::query()->lockForUpdate()->findOrFail($actor->id);
            $recentCount = ForumTopicUpdateRequest::query()
                ->where('requester_user_id', $actor->id)
                ->where('created_at', '>=', now()->subDay())
                ->count();

            if ($recentCount >= (int) config('forum.lifecycle.update_requests_per_day')) {
                throw ValidationException::withMessages([
                    'reason' => __('forum_topic_lifecycle.validation.request_limit'),
                ]);
            }

            $request = ForumTopicUpdateRequest::query()->create([
                'forum_topic_id' => $locked->id,
                'requester_user_id' => $actor->id,
                'kind' => $kind,
                'status' => ForumTopicUpdateRequestStatus::Pending,
                'reason' => $reason,
                'proposed_body' => $proposedBody,
                'lock_version' => 1,
                'idempotency_key' => $idempotencyKey,
                'metadata' => [],
            ]);
            $locked->forceFill([
                'stale_review_requested_at' => $locked->stale_review_requested_at ?? now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();
            $this->lifecycle->record(
                topic: $locked,
                type: $kind === ForumTopicUpdateRequestKind::CommunityProposal
                    ? ForumTopicLifecycleEventType::UpdateProposed
                    : ForumTopicLifecycleEventType::UpdateRequested,
                actor: $actor,
                reasonCode: $kind->value,
                metadata: ['update_request_id' => $request->id],
                idempotencyKey: "topic-update-request:{$request->id}",
            );

            return $request;
        }, 3);
    }
}

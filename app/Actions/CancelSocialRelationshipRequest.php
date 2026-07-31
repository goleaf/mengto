<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialRequestStatus;
use App\Models\SocialRelationshipEvent;
use App\Models\SocialRelationshipRequest;
use App\Services\ForumActor;
use App\Services\SocialGraphCache;
use App\Services\SocialIdempotencyGuard;
use App\Services\SocialRelationshipEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CancelSocialRelationshipRequest
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
        private readonly SocialIdempotencyGuard $idempotency,
    ) {}

    public function handle(
        SocialRelationshipRequest $request,
        string $idempotencyKey,
    ): SocialRelationshipRequest {
        $user = $this->actor->requireUser();

        return DB::transaction(function () use ($request, $idempotencyKey, $user): SocialRelationshipRequest {
            $locked = SocialRelationshipRequest::query()
                ->with(['sourceActor', 'targetActor'])
                ->lockForUpdate()
                ->findOrFail($request->id);
            $this->gate->authorize('cancel', $locked);

            $existingEvent = SocialRelationshipEvent::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingEvent instanceof SocialRelationshipEvent) {
                $this->idempotency->assertEventMatches(
                    $existingEvent,
                    $locked->sourceActor,
                    $locked->targetActor,
                    $locked->relationship_type,
                    $locked,
                );

                return $locked;
            }

            if (! $locked->status->isOpen()) {
                throw ValidationException::withMessages([
                    'request' => __('social_relationships.validation.request_unavailable'),
                ]);
            }

            $fromStatus = $locked->status->value;
            $locked->forceFill([
                'status' => SocialRequestStatus::Cancelled,
                'active_key' => null,
                'decided_by_user_id' => $user->id,
                'decided_at' => now(),
                'repeat_after' => now()->addDays((int) config('social_relationships.repeat_cooldown_days', 30)),
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->events->record(
                source: $locked->sourceActor,
                target: $locked->targetActor,
                representedActor: $locked->sourceActor,
                actor: $user,
                eventType: 'request-cancelled',
                type: $locked->relationship_type,
                idempotencyKey: $idempotencyKey,
                fromStatus: $fromStatus,
                toStatus: SocialRequestStatus::Cancelled->value,
                request: $locked,
            );
            $this->cache->invalidate($locked->sourceActor, $locked->targetActor);

            return $locked->refresh();
        }, 3);
    }
}

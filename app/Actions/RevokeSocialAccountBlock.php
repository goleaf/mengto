<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SocialAccountBlockStatus;
use App\Enums\SocialRelationshipType;
use App\Models\SocialAccountBlock;
use App\Models\SocialRelationshipEvent;
use App\Services\ForumActor;
use App\Services\SocialAccountActorQuery;
use App\Services\SocialGraphCache;
use App\Services\SocialRelationshipEventRecorder;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RevokeSocialAccountBlock
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
        private readonly SocialAccountActorQuery $accountActors,
        private readonly SocialRelationshipEventRecorder $events,
        private readonly SocialGraphCache $cache,
    ) {}

    public function handle(SocialAccountBlock $block, string $idempotencyKey): SocialAccountBlock
    {
        $user = $this->actor->requireUser();

        return DB::transaction(function () use ($block, $idempotencyKey, $user): SocialAccountBlock {
            $locked = SocialAccountBlock::query()
                ->with(['blockerUser', 'blockedUser', 'sourceActor', 'targetActor'])
                ->lockForUpdate()
                ->findOrFail($block->id);
            $this->gate->authorize('revoke', $locked);

            $existingEvent = SocialRelationshipEvent::query()
                ->where('idempotency_key', "{$idempotencyKey}:event")
                ->first();

            if ($existingEvent instanceof SocialRelationshipEvent) {
                return $locked;
            }

            if ($locked->blockerUser === null
                || $locked->blockedUser === null
                || $locked->sourceActor === null
                || $locked->targetActor === null) {
                throw ValidationException::withMessages([
                    'block' => __('social_relationships.validation.account_block_unavailable'),
                ]);
            }

            $locked->forceFill([
                'status' => SocialAccountBlockStatus::Revoked,
                'active_key' => null,
                'revoked_by_user_id' => $user->id,
                'revoked_at' => now(),
                'lock_version' => $locked->lock_version + 1,
            ])->save();

            $this->events->record(
                source: $locked->sourceActor,
                target: $locked->targetActor,
                representedActor: $locked->sourceActor,
                actor: $user,
                eventType: 'account-block-revoked',
                type: SocialRelationshipType::Block,
                idempotencyKey: "{$idempotencyKey}:event",
                fromStatus: SocialAccountBlockStatus::Active->value,
                toStatus: SocialAccountBlockStatus::Revoked->value,
                accountBlock: $locked,
                publicMetadata: ['relationships_restored' => false],
            );
            $this->cache->invalidate(
                ...$this->accountActors->controlledBy($locked->blockerUser),
                ...$this->accountActors->controlledBy($locked->blockedUser),
            );

            return $locked->refresh();
        }, 3);
    }
}

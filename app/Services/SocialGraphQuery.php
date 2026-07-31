<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialRelationshipType;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

final class SocialGraphQuery
{
    public function __construct(private readonly SocialActorAccess $access) {}

    /** @return Collection<int, SocialRelationshipRequest> */
    public function inbox(SocialActor $actor, User $user): Collection
    {
        $this->assertControlled($actor, $user);

        return SocialRelationshipRequest::query()
            ->select([
                'id', 'request_key', 'source_actor_id', 'target_actor_id',
                'relationship_type', 'direction', 'status', 'created_by_user_id',
                'context_type', 'context_key', 'message', 'sent_at', 'delivered_at',
                'expires_at', 'lock_version', 'created_at', 'updated_at',
            ])
            ->with($this->actorRelations('sourceActor'))
            ->open()
            ->where('target_actor_id', $actor->id)
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit((int) config('social_relationships.inbox_limit', 30))
            ->get();
    }

    /** @return Collection<int, SocialRelationshipRequest> */
    public function outbox(SocialActor $actor, User $user): Collection
    {
        $this->assertControlled($actor, $user);

        return SocialRelationshipRequest::query()
            ->select([
                'id', 'request_key', 'source_actor_id', 'target_actor_id',
                'relationship_type', 'direction', 'status', 'created_by_user_id',
                'context_type', 'context_key', 'sent_at', 'delivered_at',
                'expires_at', 'lock_version', 'created_at', 'updated_at',
            ])
            ->with($this->actorRelations('targetActor'))
            ->open()
            ->where('source_actor_id', $actor->id)
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->limit((int) config('social_relationships.inbox_limit', 30))
            ->get();
    }

    /** @return Collection<int, SocialRelationship> */
    public function relationships(SocialActor $actor, User $user): Collection
    {
        $this->assertControlled($actor, $user);
        $privateDirectedTypes = [
            SocialRelationshipType::Block->value,
            SocialRelationshipType::Restrict->value,
            SocialRelationshipType::Mute->value,
            SocialRelationshipType::CloseCircle->value,
        ];

        return SocialRelationship::query()
            ->select([
                'id', 'relationship_key', 'source_actor_id', 'target_actor_id',
                'request_id', 'relationship_type', 'direction', 'status',
                'visibility', 'context_type', 'context_key', 'reason_code',
                'lock_version', 'started_at', 'paused_at', 'ends_at', 'ended_at',
                'created_at', 'updated_at',
            ])
            ->with([
                ...$this->actorRelations('sourceActor'),
                ...$this->actorRelations('targetActor'),
            ])
            ->active()
            ->where(function ($query) use ($actor, $privateDirectedTypes): void {
                $query
                    ->where('source_actor_id', $actor->id)
                    ->orWhere(function ($incoming) use ($actor, $privateDirectedTypes): void {
                        $incoming
                            ->where('target_actor_id', $actor->id)
                            ->whereNotIn('relationship_type', $privateDirectedTypes);
                    });
            })
            ->orderByDesc('started_at')
            ->orderByDesc('id')
            ->limit((int) config('social_relationships.relationship_limit', 60))
            ->get();
    }

    /** @return array{relationships: int, incoming_requests: int, outgoing_requests: int} */
    public function counts(SocialActor $actor, User $user): array
    {
        $this->assertControlled($actor, $user);

        return [
            'relationships' => SocialRelationship::query()
                ->active()
                ->where(function ($query) use ($actor): void {
                    $query
                        ->where('source_actor_id', $actor->id)
                        ->orWhere('target_actor_id', $actor->id);
                })
                ->count(),
            'incoming_requests' => SocialRelationshipRequest::query()
                ->open()
                ->where('target_actor_id', $actor->id)
                ->count(),
            'outgoing_requests' => SocialRelationshipRequest::query()
                ->open()
                ->where('source_actor_id', $actor->id)
                ->count(),
        ];
    }

    /** @return array<int|string, mixed> */
    private function actorRelations(string $prefix): array
    {
        return [
            $prefix => fn ($query) => $query->directoryFields(),
            "{$prefix}.user:id,name,actor_key",
            "{$prefix}.petProfile:id,name,profile_key,user_id",
            "{$prefix}.expertProfile:id,public_name,owner_id,owner_key,slug",
            "{$prefix}.forumGroup:id,name,name_translation_key,owner_user_id,stable_key",
        ];
    }

    private function assertControlled(SocialActor $actor, User $user): void
    {
        if (! $this->access->canRepresent($actor, $user)) {
            throw ValidationException::withMessages([
                'actor' => __('social_relationships.validation.actor_unavailable'),
            ]);
        }
    }
}

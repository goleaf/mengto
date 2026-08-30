<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Enums\SocialRelationshipType;
use App\Models\PetProfile;
use App\Models\SocialActor;
use App\Models\SocialRelationship;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class SocialActorDirectory
{
    public function __construct(
        private readonly SocialActorAccess $access,
        private readonly SocialActorPresenter $presenter,
        private readonly SocialBlockService $blocks,
    ) {}

    /**
     * @return list<array{
     *     key: string,
     *     name: string,
     *     type: string,
     *     type_label: string,
     *     can_follow: bool,
     *     friendship_type: string|null
     * }>
     */
    public function search(SocialActor $source, User $user, string $search): array
    {
        if (! $this->access->canRepresent($source, $user)) {
            return [];
        }

        $needle = mb_substr(trim($search), 0, 80);

        if (mb_strlen($needle) < 2) {
            return [];
        }

        $blockedActorIds = array_values(array_unique([
            ...$this->blockedActorIds($source),
            ...$this->blocks->blockedActorIdsFor($user),
        ]));
        $candidates = SocialActor::query()
            ->directoryFields()
            ->with([
                'user:id,name,actor_key',
                'petProfile:id,name,profile_key,user_id',
                'expertProfile:id,public_name,owner_id,owner_key,slug',
                'forumGroup:id,name,name_translation_key,owner_user_id,stable_key',
            ])
            ->whereKeyNot($source->id)
            ->where('status', SocialActorStatus::Active->value)
            ->where('is_discoverable', true)
            ->where(function (Builder $visibility): void {
                $visibility
                    ->where('actor_type', '!=', SocialActorType::Pet->value)
                    ->orWhereIn(
                        'pet_profile_id',
                        PetProfile::query()
                            ->visibleTo(null)
                            ->select('id'),
                    );
            })
            ->when(
                $blockedActorIds !== [],
                fn (Builder $query): Builder => $query->whereNotIn('id', $blockedActorIds),
            )
            ->where(function (Builder $query) use ($needle): void {
                $like = "%{$needle}%";
                $query
                    ->whereHas('user', fn (Builder $userQuery): Builder => $userQuery
                        ->where('name', 'like', $like))
                    ->orWhereHas('petProfile', fn (Builder $petQuery): Builder => $petQuery
                        ->where('name', 'like', $like))
                    ->orWhereHas('expertProfile', fn (Builder $expertQuery): Builder => $expertQuery
                        ->where('public_name', 'like', $like))
                    ->orWhereHas('forumGroup', fn (Builder $groupQuery): Builder => $groupQuery
                        ->where('name', 'like', $like));
            })
            ->orderBy('actor_type')
            ->orderBy('id')
            ->limit((int) config('social_relationships.directory_limit', 20))
            ->get();

        if ($candidates->isEmpty()) {
            return [];
        }

        $candidateIds = $candidates->modelKeys();
        $relationships = SocialRelationship::query()
            ->select([
                'id',
                'source_actor_id',
                'target_actor_id',
                'relationship_type',
                'status',
                'ends_at',
            ])
            ->active()
            ->where(function (Builder $query) use ($candidateIds, $source): void {
                $query
                    ->where(function (Builder $outgoing) use ($candidateIds, $source): void {
                        $outgoing
                            ->where('source_actor_id', $source->id)
                            ->whereIn('target_actor_id', $candidateIds);
                    })
                    ->orWhere(function (Builder $incoming) use ($candidateIds, $source): void {
                        $incoming
                            ->where('target_actor_id', $source->id)
                            ->whereIn('source_actor_id', $candidateIds);
                    });
            })
            ->get();
        $requests = SocialRelationshipRequest::query()
            ->select([
                'id',
                'source_actor_id',
                'target_actor_id',
                'relationship_type',
                'status',
                'expires_at',
            ])
            ->open()
            ->where(function (Builder $query) use ($candidateIds, $source): void {
                $query
                    ->where(function (Builder $outgoing) use ($candidateIds, $source): void {
                        $outgoing
                            ->where('source_actor_id', $source->id)
                            ->whereIn('target_actor_id', $candidateIds);
                    })
                    ->orWhere(function (Builder $incoming) use ($candidateIds, $source): void {
                        $incoming
                            ->where('target_actor_id', $source->id)
                            ->whereIn('source_actor_id', $candidateIds);
                    });
            })
            ->get();

        return $candidates
            ->map(function (SocialActor $candidate) use ($relationships, $requests, $source): array {
                $pairRelationships = $relationships->filter(
                    fn (SocialRelationship $relationship): bool => in_array(
                        $candidate->id,
                        [$relationship->source_actor_id, $relationship->target_actor_id],
                        true,
                    ),
                );
                $pairRequests = $requests->filter(
                    fn (SocialRelationshipRequest $request): bool => in_array(
                        $candidate->id,
                        [$request->source_actor_id, $request->target_actor_id],
                        true,
                    ),
                );
                $friendshipType = $this->friendshipType($source, $candidate);
                $hasFriendship = $friendshipType !== null
                    && ($pairRelationships->contains('relationship_type', $friendshipType)
                        || $pairRequests->contains('relationship_type', $friendshipType));
                $hasOutgoingFollow = $pairRelationships->contains(
                    fn (SocialRelationship $relationship): bool => $relationship->relationship_type === SocialRelationshipType::Follow
                        && $relationship->source_actor_id === $source->id,
                ) || $pairRequests->contains(
                    fn (SocialRelationshipRequest $request): bool => $request->relationship_type === SocialRelationshipType::Follow
                        && $request->source_actor_id === $source->id,
                );

                return [
                    ...$this->presenter->present($candidate),
                    'can_follow' => ! $hasOutgoingFollow,
                    'friendship_type' => $hasFriendship ? null : $friendshipType?->value,
                ];
            })
            ->values()
            ->all();
    }

    /** @return list<int> */
    private function blockedActorIds(SocialActor $source): array
    {
        return SocialRelationship::query()
            ->select([
                'id',
                'source_actor_id',
                'target_actor_id',
                'relationship_type',
                'status',
                'ends_at',
            ])
            ->active()
            ->where('relationship_type', SocialRelationshipType::Block->value)
            ->where(function (Builder $query) use ($source): void {
                $query
                    ->where('source_actor_id', $source->id)
                    ->orWhere('target_actor_id', $source->id);
            })
            ->get()
            ->map(static fn (SocialRelationship $relationship): int => $relationship->source_actor_id === $source->id
                    ? $relationship->target_actor_id
                    : $relationship->source_actor_id)
            ->unique()
            ->values()
            ->all();
    }

    private function friendshipType(
        SocialActor $source,
        SocialActor $target,
    ): ?SocialRelationshipType {
        if ($source->actor_type === SocialActorType::User
            && $target->actor_type === SocialActorType::User) {
            return SocialRelationshipType::OwnerFriendship;
        }

        if ($source->actor_type === SocialActorType::Pet
            && $target->actor_type === SocialActorType::Pet) {
            return SocialRelationshipType::PetFriendship;
        }

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetManagerStatus;
use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Models\SocialActor;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class SocialAccountActorQuery
{
    public function __construct(private readonly SocialActorAccess $access) {}

    /** @return Collection<int, SocialActor> */
    public function controlledBy(User $user): Collection
    {
        return $this->controlledByUserIds([$user->id]);
    }

    /**
     * @param  list<int>  $userIds
     * @return Collection<int, SocialActor>
     */
    public function controlledByUserIds(array $userIds): Collection
    {
        $ids = array_values(array_unique(array_filter($userIds, static fn (int $id): bool => $id > 0)));

        if ($ids === []) {
            return new Collection;
        }

        return SocialActor::query()
            ->directoryFields()
            ->where('status', SocialActorStatus::Active->value)
            ->where(function (Builder $query) use ($ids): void {
                $query
                    ->whereIn('user_id', $ids)
                    ->orWhereHas('petProfile', function (Builder $profileQuery) use ($ids): void {
                        $profileQuery
                            ->whereIn('user_id', $ids)
                            ->orWhereHas('managers', fn (Builder $managerQuery): Builder => $managerQuery
                                ->whereIn('user_id', $ids)
                                ->where('status', PetManagerStatus::Active->value));
                    })
                    ->orWhereHas('expertProfile', fn (Builder $expertQuery): Builder => $expertQuery
                        ->whereIn('owner_id', $ids))
                    ->orWhereHas('forumGroup', fn (Builder $groupQuery): Builder => $groupQuery
                        ->whereIn('owner_user_id', $ids));
            })
            ->orderBy('id')
            ->get();
    }

    /** @return list<int> */
    public function controllerUserIds(SocialActor $actor): array
    {
        if ($actor->actor_type === SocialActorType::User) {
            return $actor->user_id === null ? [] : [$actor->user_id];
        }

        if ($actor->actor_type === SocialActorType::Pet) {
            $actor->loadMissing([
                'petProfile:id,user_id',
                'petProfile.managers' => fn ($query) => $query
                    ->select(['id', 'pet_profile_id', 'user_id', 'status'])
                    ->where('status', PetManagerStatus::Active->value),
            ]);
            $profile = $actor->petProfile;

            if ($profile === null) {
                return [];
            }

            return collect([$profile->user_id])
                ->merge($profile->managers
                    ->filter(fn ($manager): bool => $manager->status === PetManagerStatus::Active)
                    ->pluck('user_id'))
                ->filter()
                ->map(static fn (mixed $id): int => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $controllerId = $this->access->primaryControllerUserId($actor);

        return $controllerId === null ? [] : [$controllerId];
    }

    public function controlledByUser(SocialActor $actor, User $user): bool
    {
        return $this->access->canRepresent($actor, $user);
    }
}

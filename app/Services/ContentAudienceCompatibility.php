<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ContentAudienceType;
use App\Enums\ExpertProfileStatus;
use App\Enums\ForumGroupVisibility;
use App\Enums\PetProfileVisibility;
use App\Enums\SocialActorType;
use App\Models\SocialActor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ContentAudienceCompatibility
{
    public function allows(SocialActor $actor, ContentAudienceType $audience): bool
    {
        if (! $this->isBroad($audience)) {
            return true;
        }

        if (! $actor->is_discoverable) {
            return false;
        }

        return match ($actor->actor_type) {
            SocialActorType::User => true,
            SocialActorType::Pet => $this->allowsPet($actor, $audience),
            SocialActorType::Expert => $actor->expertProfile?->status
                === ExpertProfileStatus::Published,
            SocialActorType::Group => in_array(
                $actor->forumGroup?->visibility,
                $this->groupVisibilities($audience),
                true,
            ),
        };
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainActor(
        Builder $query,
        ContentAudienceType $audience,
    ): Builder {
        $petVisibilities = array_map(
            static fn (PetProfileVisibility $visibility): string => $visibility->value,
            self::petVisibilityCases($audience),
        );
        $groupVisibilities = array_map(
            static fn (ForumGroupVisibility $visibility): string => $visibility->value,
            self::groupVisibilityCases($audience),
        );

        return $query
            ->where('is_discoverable', true)
            ->where(function (Builder $types) use ($groupVisibilities, $petVisibilities): void {
                $types
                    ->where('actor_type', SocialActorType::User->value)
                    ->orWhere(function (Builder $pet) use ($petVisibilities): void {
                        $pet->where('actor_type', SocialActorType::Pet->value)
                            ->whereHas('petProfile', fn (Builder $profile): Builder => $profile
                                ->whereIn('visibility', $petVisibilities));
                    })
                    ->orWhere(function (Builder $expert): void {
                        $expert->where('actor_type', SocialActorType::Expert->value)
                            ->whereHas('expertProfile', fn (Builder $profile): Builder => $profile
                                ->where('status', ExpertProfileStatus::Published->value));
                    })
                    ->orWhere(function (Builder $group) use ($groupVisibilities): void {
                        $group->where('actor_type', SocialActorType::Group->value)
                            ->whereHas('forumGroup', fn (Builder $profile): Builder => $profile
                                ->whereIn('visibility', $groupVisibilities));
                    });
            });
    }

    private function isBroad(ContentAudienceType $audience): bool
    {
        return in_array($audience, [
            ContentAudienceType::Everyone,
            ContentAudienceType::Registered,
        ], true);
    }

    private function allowsPet(
        SocialActor $actor,
        ContentAudienceType $audience,
    ): bool {
        $visibility = $actor->petProfile?->visibility;

        return $visibility !== null && in_array(
            PetProfileVisibility::fromStored($visibility),
            $this->petVisibilities($audience),
            true,
        );
    }

    /** @return list<PetProfileVisibility> */
    private function petVisibilities(ContentAudienceType $audience): array
    {
        return self::petVisibilityCases($audience);
    }

    /** @return list<ForumGroupVisibility> */
    private function groupVisibilities(ContentAudienceType $audience): array
    {
        return self::groupVisibilityCases($audience);
    }

    /** @return list<PetProfileVisibility> */
    private static function petVisibilityCases(ContentAudienceType $audience): array
    {
        return $audience === ContentAudienceType::Everyone
            ? [PetProfileVisibility::Public]
            : [PetProfileVisibility::Public, PetProfileVisibility::Authenticated];
    }

    /** @return list<ForumGroupVisibility> */
    private static function groupVisibilityCases(ContentAudienceType $audience): array
    {
        return $audience === ContentAudienceType::Everyone
            ? [ForumGroupVisibility::Public]
            : [ForumGroupVisibility::Public, ForumGroupVisibility::RequestToJoin];
    }
}

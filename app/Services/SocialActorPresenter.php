<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialActorType;
use App\Models\SocialActor;

final class SocialActorPresenter
{
    /** @return array{key: string, name: string, type: string, type_label: string} */
    public function present(SocialActor $actor): array
    {
        return [
            'key' => $actor->actor_key,
            'name' => $this->name($actor),
            'type' => $actor->actor_type->value,
            'type_label' => $actor->actor_type->label(),
        ];
    }

    private function name(SocialActor $actor): string
    {
        return match ($actor->actor_type) {
            SocialActorType::User => $actor->user_id === null
                ? __('social_relationships.deleted_actor')
                : $actor->user->name,
            SocialActorType::Pet => $actor->pet_profile_id === null
                ? __('social_relationships.deleted_actor')
                : $actor->petProfile->name,
            SocialActorType::Expert => $actor->expert_profile_id === null
                ? __('social_relationships.deleted_actor')
                : $actor->expertProfile->public_name,
            SocialActorType::Group => $actor->forum_group_id === null
                ? __('social_relationships.deleted_actor')
                : $actor->forumGroup->displayName(),
        };
    }
}

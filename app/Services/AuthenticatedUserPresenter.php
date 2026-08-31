<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Models\SocialActor;
use App\Models\User;
use LogicException;

final readonly class AuthenticatedUserPresenter
{
    /**
     * @return array{
     *     name: string,
     *     initials: string,
     *     avatar: null,
     *     avatar_alt: string,
     *     location: string,
     *     summary: string,
     *     profile_url: string,
     *     media_target: array{url: string, label: string}
     * }
     */
    public function present(User $user): array
    {
        $actor = $user->relationLoaded('socialActor')
            ? $user->socialActor
            : $user->socialActor()
                ->select(['id', 'actor_key', 'actor_type', 'status', 'user_id'])
                ->first();

        if (! $actor instanceof SocialActor
            || $actor->actor_type !== SocialActorType::User
            || $actor->status !== SocialActorStatus::Active
            || $actor->user_id !== $user->id
        ) {
            throw new LogicException('The authenticated user has no active personal social actor.');
        }

        $profileUrl = route('members.show', ['socialActor' => $actor]);

        return [
            'name' => $user->name,
            'initials' => $this->initials($user->name),
            'avatar' => null,
            'avatar_alt' => __('member_profiles.current.avatar_alt', ['name' => $user->name]),
            'location' => '',
            'summary' => __('member_profiles.current.summary'),
            'profile_url' => $profileUrl,
            'media_target' => [
                'url' => $profileUrl,
                'label' => __('navigation.utility.profile_for', ['name' => $user->name]),
            ],
        ];
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/u', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}

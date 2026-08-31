<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

/**
 * Transitional adapter for pages that still request shell/profile data.
 *
 * Canonical member pages are presented by MemberProfileCatalog. This service
 * exposes only the authenticated account and real managed pets.
 */
final readonly class ProfilePresenter
{
    public function __construct(
        private AuthFactory $auth,
        private AuthenticatedUserPresenter $authenticatedUsers,
        private PetProfileCatalog $pets,
        private ProfileVisibility $visibility,
    ) {}

    /** @return array<string, mixed> */
    public function owner(): array
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $this->authenticatedUsers->present($user) : [];
    }

    /** @return array<string, mixed>|null */
    public function pet(string $key): ?array
    {
        return $this->pets->find($key);
    }

    /** @return array<int, array<string, mixed>> */
    public function pets(): array
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $this->pets->managedBy($user) : [];
    }

    /** @return array<string, string> */
    public function visibilityOptions(): array
    {
        return $this->visibility->options();
    }

    /**
     * @return array{target: string, label: string, route: string, route_parameters: array<string, string>}|null
     */
    public function reportContext(string $target): ?array
    {
        $user = $this->auth->guard()->user();

        if (! $user instanceof User) {
            return null;
        }

        $owner = $this->authenticatedUsers->present($user);
        $actorKey = $owner['profile_route_parameters']['socialActor'];

        if ($target === 'member-'.$actorKey) {
            return [
                'target' => $target,
                'label' => $user->name,
                'route' => 'members.show',
                'route_parameters' => ['socialActor' => $actorKey],
            ];
        }

        if (! str_starts_with($target, 'pet-')) {
            return null;
        }

        $pet = $this->pets->findFor($user, substr($target, 4));

        if ($pet === null) {
            return null;
        }

        return [
            'target' => $target,
            'label' => $pet['name'],
            'route' => 'pets.profile',
            'route_parameters' => $pet['route_parameters'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

final readonly class PetProfileCatalog
{
    public function __construct(
        private AuthFactory $auth,
        private PetSpeciesLabel $speciesLabels,
    ) {}

    /** @return array<string, mixed>|null */
    public function find(string $key): ?array
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $this->findFor($user, $key) : null;
    }

    /** @return array<string, mixed>|null */
    public function findFor(User $user, string $key): ?array
    {
        $profile = PetProfile::query()
            ->managedBy($user)
            ->select([
                'id', 'user_id', 'profile_key', 'slug', 'name', 'species',
                'species_confidence', 'breed', 'visibility', 'status',
                'profile_data', 'created_at',
            ])
            ->with('user:id,name')
            ->where(static function ($query) use ($key): void {
                $query->where('profile_key', $key)->orWhere('slug', $key);
            })
            ->first();

        return $profile instanceof PetProfile ? $this->present($profile) : null;
    }

    /** @return array<int, array<string, mixed>> */
    public function managedBy(User $user, int $limit = 24): array
    {
        return PetProfile::query()
            ->managedBy($user)
            ->select([
                'id', 'user_id', 'profile_key', 'slug', 'name', 'species',
                'species_confidence', 'breed', 'visibility', 'status',
                'profile_data', 'created_at', 'updated_at',
            ])
            ->with('user:id,name')
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (PetProfile $profile): array => $this->present($profile))
            ->all();
    }

    /** @return array<string, mixed> */
    private function present(PetProfile $profile): array
    {
        $profileData = $profile->profile_data ?? [];
        $profileUrl = route('pets.profile', ['petProfile' => $profile->profile_key]);
        $species = $this->speciesLabels->for($profile->species, $profile->species_confidence);
        $imageAlt = __('messages.pet_profile_image_alt', ['pet' => $profile->name]);

        return [
            'key' => $profile->profile_key,
            'slug' => $profile->profile_key,
            'profile_key' => $profile->profile_key,
            'name' => $profile->name,
            'handle' => '',
            'role' => __('messages.pet_profile'),
            'species' => $species,
            'breed' => $profile->breed ?? '',
            'age' => '',
            'location' => (string) ($profileData['location'] ?? ''),
            'member_since' => $profile->created_at?->isoFormat('LL') ?? '',
            'status' => $profile->status->label(),
            'story' => (string) ($profileData['story'] ?? ''),
            'visibility' => $profile->visibility,
            'avatar' => null,
            'profile_image' => null,
            'cover_image' => null,
            'cover_image_small' => null,
            'cover_image_medium' => null,
            'cover_image_alt' => $imageAlt,
            'card_image' => null,
            'card_image_small' => null,
            'card_image_medium' => null,
            'card_image_alt' => $imageAlt,
            'traits' => [],
            'facts' => [],
            'care' => [],
            'compatibility' => [],
            'gallery' => [],
            'route' => 'pets.profile',
            'route_parameters' => ['petProfile' => $profile->profile_key],
            'profile_route' => 'pets.profile',
            'profile_parameters' => ['petProfile' => $profile->profile_key],
            'profile_url' => $profileUrl,
            'owner' => $profile->user->name,
            'neighborhood' => (string) ($profileData['location'] ?? ''),
            'image' => null,
            'image_small' => null,
            'image_medium' => null,
            'image_alt' => $imageAlt,
            'media_target' => [
                'url' => $profileUrl,
                'label' => __('presentation.open_profile', ['name' => $profile->name]),
            ],
        ];
    }
}

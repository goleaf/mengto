<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

/**
 * Authenticated pet-friend identity boundary.
 *
 * Friendship discovery will be populated from canonical social relations;
 * until then it intentionally returns no candidates instead of demo pets.
 */
final readonly class PetFriendCatalog
{
    public function __construct(
        private AuthFactory $auth,
        private PetProfileCatalog $pets,
    ) {}

    /** @return array<string, array<string, mixed>> */
    public function records(): array
    {
        $user = $this->auth->guard()->user();

        if (! $user instanceof User) {
            return [];
        }

        return collect($this->pets->managedBy($user))
            ->mapWithKeys(static function (array $pet): array {
                $id = 'pet-'.$pet['profile_key'];

                return [$id => [
                    'id' => $id,
                    'slug' => $pet['profile_key'],
                    'name' => $pet['name'],
                    'handle' => '',
                    'owner' => $pet['owner'],
                    'owner_handle' => '',
                    'owner_conversation' => '',
                    'species' => $pet['species'],
                    'breed' => $pet['breed'],
                    'age' => $pet['age'],
                    'size' => '',
                    'location' => $pet['location'],
                    'activity' => '',
                    'play_style' => '',
                    'description' => $pet['story'],
                    'image' => null,
                    'image_alt' => $pet['image_alt'],
                    'route_name' => 'pets.profile',
                    'route_parameters' => $pet['route_parameters'],
                    'intents' => [],
                    'private' => $pet['visibility'] ?? true,
                    'verified' => false,
                ]];
            })
            ->all();
    }

    /** @return array<string, mixed>|null */
    public function find(string $id): ?array
    {
        return $this->records()[$id] ?? null;
    }

    /** @return array<string, array<string, mixed>> */
    public function owned(): array
    {
        return $this->records();
    }

    /** @return array<int, array<string, mixed>> */
    public function candidates(string $source): array
    {
        return [];
    }

    /** @return array{reason: string, shared: array<int, string>, cautions: array<int, string>, score: int} */
    public function compatibility(string $source, string $target): array
    {
        return [
            'reason' => __('messages.compatibility_details_are_unavailable'),
            'shared' => [],
            'cautions' => [__('messages.owners_should_review_both_profiles_before_arranging_contact')],
            'score' => 0,
        ];
    }
}

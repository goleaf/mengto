<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PetProfile;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

final class CreatedContentPresenter
{
    public function __construct(
        private readonly PrototypeState $state,
        private readonly AuthFactory $auth,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function posts(): array
    {
        return array_map(static fn (array $post): array => [
            'key' => 'created-post-'.$post['id'],
            'author' => $post['title'],
            'pet' => __('messages.new_moment'),
            'time' => __('messages.just_now'),
            'datetime' => now()->toAtomString(),
            'body' => $post['body'],
            'image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=900&q=85',
            'image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=576&h=432&q=80',
            'image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=900&h=675&q=82',
            'image_alt' => '',
            'tags' => array_values(array_filter([$post['category']])),
            'stats' => ['paws' => '0', 'replies' => '0'],
        ], $this->state->created('posts'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function groups(): array
    {
        $user = $this->auth->guard()->user();

        if (! $user instanceof User) {
            return [];
        }

        return array_map(function (array $group) use ($user): array {
            $key = 'created-group-'.$group['id'];

            return [
                'key' => $key,
                'detail_route' => 'groups.created',
                'detail_parameters' => ['item' => $key],
                'name' => $group['title'],
                'category' => $group['category'] ?: __('messages.local'),
                'members' => __('messages.1_member'),
                'activity' => __('messages.just_created'),
                'topic' => $group['category'] ?: __('messages.new_community'),
                'description' => $group['body'],
                'privacy' => $group['privacy'] ?? 'public',
                'location' => $group['location'] ?: __('messages.location_not_set'),
                'language' => $group['language'] ?? __('messages.english'),
                'rules' => $group['rules'] ?? '',
                'pet_identity' => $group['pet_identity'] ?? $user->actor_key,
                'posting_policy' => $group['posting_policy'] ?? 'members',
                'organizer' => $user->name,
                'organizer_initials' => $this->initials($user->name),
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=900&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=160&h=160&q=80',
                'image_alt' => __('messages.dog_and_cat_resting_together_in_a_welcoming_home'),
                'tags' => array_values(array_filter([
                    $group['category'] ?: __('messages.new_group'),
                    $group['location'] ?: null,
                ])),
            ];
        }, $this->state->created('groups'));
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/u', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pets(): array
    {
        $user = $this->auth->guard()->user();
        $user = $user instanceof User ? $user : null;

        if (! $user instanceof User) {
            return [];
        }

        $profiles = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'name',
                'species',
                'breed',
                'visibility',
                'status',
                'profile_data',
                'created_at',
            ])
            ->with('user:id,name')
            ->visibleTo($user)
            ->where('user_id', $user->id)
            ->where('profile_key', 'like', 'created-pet-%')
            ->latest('id')
            ->limit(8)
            ->get();

        return $profiles->map(static function (PetProfile $profile): array {
            $profileData = $profile->profile_data ?? [];
            $key = $profile->profile_key;
            $profileUrl = route('pets.profile', ['petProfile' => $key]);

            return [
                'key' => $key,
                'name' => $profile->name,
                'species' => $profile->species,
                'breed' => $profile->breed ?: __('messages.mixed_companion'),
                'age' => __('messages.new_profile'),
                'owner' => $profile->user->name,
                'neighborhood' => (string) ($profileData['location'] ?? __('messages.location_kept_private')),
                'status' => (string) ($profileData['status'] ?? $profileData['story'] ?? ''),
                'image' => (string) ($profileData['card_image'] ?? ''),
                'image_small' => (string) ($profileData['card_image_small'] ?? ''),
                'image_medium' => (string) ($profileData['card_image_medium'] ?? ''),
                'image_alt' => (string) ($profileData['card_image_alt'] ?? __('messages.pet_profile_image_alt', ['pet' => $profile->name])),
                'traits' => is_array($profileData['traits'] ?? null)
                    ? array_values($profileData['traits'])
                    : [__('messages.new_to_pawcircle')],
                'profile_route' => 'pets.profile',
                'profile_parameters' => ['petProfile' => $key],
                'media_target' => [
                    'url' => $profileUrl,
                    'label' => __('presentation.open_profile', ['name' => $profile->name]),
                ],
            ];
        })->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $kind, string $key): ?array
    {
        $item = $this->find($kind, $key);

        if ($item === null) {
            return null;
        }

        return match ($kind) {
            'group' => $this->groupDetail($item),
            default => null,
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    public function shareTarget(string $key): ?array
    {
        foreach (['group'] as $kind) {
            $content = $this->detail($kind, $key);

            if ($content === null) {
                continue;
            }

            $hero = $content['hero'];

            return [
                'target' => $key,
                'type' => $content['share_type'],
                'active_section' => $content['active_section'],
                'eyebrow' => $content['share_eyebrow'],
                'title' => $hero['title'],
                'description' => $hero['description'],
                'image' => $hero['image'],
                'image_small' => $hero['image_small'],
                'image_medium' => $hero['image_medium'],
                'image_alt' => $hero['image_alt'],
                'route' => $content['route'],
                'route_parameters' => ['item' => $key],
            ];
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function find(string $kind, string $key): ?array
    {
        $items = match ($kind) {
            'group' => $this->groups(),
            default => [],
        };

        foreach ($items as $item) {
            if ($item['key'] === $key) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    private function groupDetail(array $group): array
    {
        return [
            'kind' => 'group',
            'route' => 'groups.created',
            'page_title' => __('presentation.brand_title', ['title' => $group['name']]),
            'active_section' => 'groups',
            'back_route' => 'groups.index',
            'back_label' => __('messages.back_to_groups'),
            'section' => 'created-group-detail',
            'share_type' => __('messages.community'),
            'share_eyebrow' => __('messages.share_a_community'),
            'summary_label' => __('messages.community_summary'),
            'summary_icons' => ['users', 'messages-square', 'activity'],
            'hero' => [
                ...$this->media($group),
                'key' => $group['key'],
                'eyebrow' => __('messages.new_local_community'),
                'title' => $group['name'],
                'description' => $group['description'],
                'meta' => [
                    ['icon' => 'users-round', 'label' => $group['category']],
                    ['icon' => 'user-round', 'label' => __('messages.created_by').$group['organizer']],
                    ['icon' => 'activity', 'label' => __('messages.just_created')],
                ],
                'tags' => $group['tags'],
                'stats' => [
                    ['label' => __('messages.members'), 'value' => '1', 'detail' => __('messages.founding_neighbor')],
                    ['label' => __('messages.posts'), 'value' => '0', 'detail' => __('messages.ready_for_the_first_update')],
                    ['label' => __('messages.access'), 'value' => __('messages.open'), 'detail' => __('messages.neighbors_may_join')],
                ],
            ],
            'primary' => [
                'label' => __('messages.join_group'),
                'icon' => 'user-plus',
                'active_label' => __('messages.joined'),
                'active_icon' => 'check',
                'active' => $this->state->isActive('groups', $group['key']),
                'action' => 'toggle-group',
            ],
            'about' => [
                'eyebrow' => __('messages.community_purpose'),
                'title' => __('messages.what_neighbors_will_share'),
                'copy' => $group['description'],
            ],
            'guidance' => [
                [
                    'icon' => 'message-circle-heart',
                    'title' => __('messages.start_with_useful_context'),
                    'description' => __('messages.share_routines_locations_and_practical_details_that_help_neighbors_participate'),
                ],
                [
                    'icon' => 'shield-check',
                    'title' => __('messages.protect_private_details'),
                    'description' => __('messages.move_addresses_access_instructions_and_sensitive_care_information_into_direct_messages'),
                ],
                [
                    'icon' => 'users',
                    'title' => __('messages.keep_introductions_calm'),
                    'description' => __('messages.let_every_person_and_pet_choose_their_own_pace_when_meeting_for_the_first_time'),
                ],
            ],
            'facts' => [
                ['label' => __('messages.category'), 'value' => $group['category']],
                ['label' => __('messages.organizer'), 'value' => $group['organizer']],
                ['label' => __('messages.activity'), 'value' => $group['activity']],
            ],
            'notice' => [
                'icon' => 'sparkles',
                'title' => __('messages.ready_for_its_first_neighbors'),
                'description' => __('messages.share_the_community_or_add_the_first_useful_post_to_begin_the_conversation'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{image: string, image_small: string, image_medium: string, image_alt: string}
     */
    private function media(array $item): array
    {
        return [
            'image' => $item['image'],
            'image_small' => $item['image_small'],
            'image_medium' => $item['image_medium'],
            'image_alt' => $item['image_alt'],
        ];
    }
}

<?php

namespace App\Services;

final class ProfilePresenter
{
    public function __construct(
        private readonly PrototypeState $state,
        private readonly ProfileVisibility $visibility,
        private readonly InteractionPresenter $interactions,
        private readonly PetProfileCatalog $pets,
        private readonly CreatedContentPresenter $created,
        private readonly PetFriendCatalog $friendPets,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function owner(): array
    {
        $owner = [
            'name' => 'Mia Carter',
            'handle' => '@mia-carter',
            'location' => 'Richmond, Portland, OR',
            'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
            'summary' => 'Weekend trail walker, foster volunteer, and keeper of two very opinionated companions.',
        ];

        return [
            ...$owner,
            ...array_intersect_key($this->state->profile(), $owner),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function ownerProfile(): array
    {
        $owner = [
            ...$this->owner(),
            'slug' => 'mia-carter',
            'role' => 'Pet parent and foster volunteer',
            'member_since' => 'Member since 2024',
            'status' => 'Open to weekend walks',
            'bio' => 'Mia plans low-pressure neighborhood walks, shares foster setup notes, and keeps a running list of shaded Portland routes. Scout handles field research while Nori supervises from the nearest window.',
            'cover_image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => 'Scout lying in grass behind a tennis ball',
        ];

        return [
            ...$owner,
            ...array_filter($this->state->profile(), static fn (string $value): bool => $value !== ''),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pet(string $slug): ?array
    {
        $pet = $this->pets->find($slug);

        if ($pet === null) {
            return null;
        }

        return [
            ...$pet,
            ...array_filter($this->state->pet($slug), static fn (string $value): bool => $value !== ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function ownerPage(string $tab = 'overview', string $audience = 'owner'): array
    {
        $tab = in_array($tab, ['overview', 'pets', 'posts', 'about'], true) ? $tab : 'overview';
        $audience = $this->audience($audience);
        $identity = $this->ownerProfile();
        $privacy = $this->state->ownerPrivacy();
        $petsVisible = $this->visibility->allows($privacy['pets'], $audience);
        $postsVisible = $this->visibility->allows($privacy['posts'], $audience);
        $locationVisible = $this->visibility->allows($privacy['location'], $audience);

        $identity['location'] = $locationVisible ? $identity['location'] : 'Location kept private';
        $identity['actions'] = $this->ownerActions($audience);
        $identity['stats'] = [
            ['label' => 'Pets', 'value' => '2', 'detail' => 'separate profiles'],
            ['label' => 'Followers', 'value' => '2.4k', 'detail' => 'owner audience'],
            ['label' => 'Following', 'value' => '186', 'detail' => 'people and pets'],
            ['label' => 'Posts', 'value' => '42', 'detail' => 'from Mia'],
        ];

        return [
            'kind' => 'owner',
            'owner' => $this->owner(),
            'identity' => $identity,
            'page_title' => $identity['name'].' '.$identity['handle'].' | PawCircle',
            'active_section' => 'profile',
            'audience' => $audience,
            'audience_options' => $this->audienceOptions(
                routeName: 'profile.mia',
                tab: $tab,
                audience: $audience,
            ),
            'tabs' => $this->ownerTabs($tab, $audience),
            'active_tab' => $tab,
            'pets' => $petsVisible ? $this->pets() : [],
            'pets_restricted' => ! $petsVisible,
            'moments' => $postsVisible ? $this->ownerMoments() : [],
            'posts_restricted' => ! $postsVisible,
            'availability' => [
                ['label' => 'Best time', 'value' => 'Weekend mornings'],
                ['label' => 'Usual pace', 'value' => 'Easy to moderate'],
                ['label' => 'Home base', 'value' => $locationVisible ? 'Richmond, Portland' : 'Private'],
            ],
            'interests' => ['trail walks', 'foster care', 'cat enrichment', 'quiet parks', 'positive training'],
            'languages' => [
                [
                    'icon' => 'languages',
                    'title' => 'English',
                    'description' => 'Primary profile and conversation language.',
                ],
                [
                    'icon' => 'languages',
                    'title' => 'Spanish',
                    'description' => 'Available for conversational messages.',
                ],
            ],
            'details' => [
                ['label' => 'Username', 'value' => $identity['handle']],
                ['label' => 'Account type', 'value' => 'Pet owner and volunteer'],
                ['label' => 'Joined', 'value' => '2024'],
                ['label' => 'Profile language', 'value' => 'English'],
            ],
            'badges' => [
                ['icon' => 'badge-check', 'label' => 'Email verified', 'tone' => 'mint'],
                ['icon' => 'heart-handshake', 'label' => 'Active volunteer', 'tone' => 'sun'],
                ['icon' => 'circle-check-big', 'label' => 'Profile complete', 'tone' => 'surface'],
            ],
            'completion' => [
                'value' => 86,
                'label' => 'Profile completeness',
                'detail' => 'Add an optional website to finish the public basics.',
            ],
            'privacy' => $this->privacySummary($privacy),
            'safety_actions' => $this->safetyActions('owner-mia-carter', 'Mia Carter', $audience),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function petPage(
        string $slug,
        string $tab = 'feed',
        string $audience = 'owner',
    ): ?array {
        $pet = $this->pet($slug);

        if ($pet === null) {
            return null;
        }

        $tab = in_array($tab, ['feed', 'about', 'photos', 'friends', 'care', 'family'], true) ? $tab : 'feed';
        $audience = $this->audience($audience);
        $privacy = $this->state->petPrivacy($slug);
        $locationVisible = $this->visibility->allows($privacy['location'], $audience);
        $postsVisible = $this->visibility->allows($privacy['posts'], $audience);
        $friendsVisible = $this->visibility->allows($privacy['friends'], $audience);
        $careVisible = $this->visibility->allows($privacy['care'], $audience);

        $pet['location'] = $locationVisible ? $pet['location'] : 'Location kept private';
        $pet['actions'] = $this->petActions($pet, $audience);
        $pet['stats'] = [
            ['label' => 'Followers', 'value' => $slug === 'scout' ? '1.8k' : '690', 'detail' => 'pet audience'],
            ['label' => 'Friends', 'value' => $slug === 'scout' ? '28' : '14', 'detail' => 'pet connections'],
            ['label' => 'Moments', 'value' => $slug === 'scout' ? '36' : '18', 'detail' => 'about '.$pet['name']],
            ['label' => 'Walks', 'value' => $slug === 'scout' ? '12' : '0', 'detail' => $slug === 'scout' ? 'shared plans' : 'indoor routine'],
        ];

        return [
            'kind' => 'pet',
            'owner' => $this->owner(),
            'identity' => $pet,
            'page_title' => $pet['name'].' '.$pet['handle'].' | PawCircle',
            'active_section' => 'pets',
            'audience' => $audience,
            'audience_options' => $this->audienceOptions(
                routeName: $pet['route'],
                tab: $tab,
                audience: $audience,
            ),
            'tabs' => $this->petTabs($pet, $tab, $audience, $careVisible),
            'active_tab' => $tab,
            'moments' => $postsVisible ? $this->petMoments($slug) : [],
            'posts_restricted' => ! $postsVisible,
            'friends' => $friendsVisible ? $this->pets->friends($slug) : [],
            'friends_restricted' => ! $friendsVisible,
            'care_visible' => $careVisible,
            'managers' => $this->pets->managers($slug, $this->owner()),
            'privacy' => $this->privacySummary($privacy),
            'badges' => [
                ['icon' => 'paw-print', 'label' => 'Pet profile complete', 'tone' => 'mint'],
                ['icon' => 'user-round-check', 'label' => 'Owner confirmed', 'tone' => 'surface'],
            ],
            'safety_actions' => $this->safetyActions('pet-'.$slug, $pet['name'], $audience),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pets(): array
    {
        $pets = array_values(array_filter([
            $this->pet('scout'),
            $this->pet('nori'),
        ]));

        $cards = array_map(fn (array $pet): array => $this->pets->card($pet), $pets);

        return [
            ...$cards,
            ...$this->created->pets(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function scoutMoments(): array
    {
        return $this->interactions->posts($this->pets->moments('scout'));
    }

    /**
     * @return array<string, string>
     */
    public function visibilityOptions(): array
    {
        return $this->visibility->options();
    }

    /**
     * @return array{target: string, label: string, route: string, route_parameters: array<string, string>}|null
     */
    public function reportContext(string $target): ?array
    {
        if ($target === 'owner-mia-carter') {
            return [
                'target' => $target,
                'label' => 'Mia Carter',
                'route' => 'profile.mia',
                'route_parameters' => [],
            ];
        }

        if (! str_starts_with($target, 'pet-')) {
            return null;
        }

        $slug = substr($target, 4);
        $pet = $this->pet($slug);

        if ($pet !== null) {
            return [
                'target' => $target,
                'label' => $pet['name'],
                'route' => $pet['route'],
                'route_parameters' => [],
            ];
        }

        $friendPet = $this->friendPets->find($target);

        if ($friendPet === null || isset($this->friendPets->owned()[$target])) {
            return null;
        }

        return [
            'target' => $target,
            'label' => $friendPet['name'],
            'route' => $friendPet['route_name'],
            'route_parameters' => $friendPet['route_parameters'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ownerActions(string $audience): array
    {
        if ($audience === 'owner') {
            return [
                $this->linkAction('Edit profile', 'pencil', route('compose', 'profile'), 'primary'),
                $this->linkAction(
                    'Privacy',
                    'shield-check',
                    route('compose', ['kind' => 'profile-privacy']),
                ),
                $this->postAction('Share', 'share-2', [
                    'action' => 'share',
                    'target' => 'mia-carter',
                    'label' => 'Mia Carter profile',
                ]),
            ];
        }

        return [
            $this->toggleAction(
                label: 'Follow Mia',
                icon: 'user-plus',
                collection: 'subscriptions',
                target: 'owner-mia-carter',
                activeLabel: 'Following Mia',
                activeIcon: 'user-check',
                action: 'toggle-subscription',
                feedbackLabel: 'Mia Carter',
            ),
            $this->toggleAction(
                label: 'Add friend',
                icon: 'user-round-plus',
                collection: 'friends',
                target: 'owner-mia-carter',
                activeLabel: 'Request sent',
                activeIcon: 'clock-3',
                action: 'toggle-friend',
                feedbackLabel: 'Mia Carter',
            ),
            $this->linkAction(
                'Message',
                'message-circle',
                route('compose', ['kind' => 'message']),
            ),
            $this->postAction('Share', 'share-2', [
                'action' => 'share',
                'target' => 'mia-carter',
                'label' => 'Mia Carter profile',
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $pet
     * @return array<int, array<string, mixed>>
     */
    private function petActions(array $pet, string $audience): array
    {
        if ($audience === 'owner') {
            $actions = [
                $this->linkAction(
                    'Edit profile',
                    'pencil',
                    route('compose', ['kind' => 'pet-profile', 'pet' => $pet['slug']]),
                    'primary',
                ),
                $this->linkAction(
                    'Privacy',
                    'shield-check',
                    route('compose', ['kind' => 'pet-privacy', 'pet' => $pet['slug']]),
                ),
                $this->linkAction(
                    'Pet friends',
                    'heart-handshake',
                    route('pet-friends.index', ['pet' => $pet['slug']]),
                ),
            ];

            if ($pet['slug'] === 'scout') {
                $actions[] = $this->linkAction(
                    'Plan a walk',
                    'footprints',
                    route('compose', ['kind' => 'walk-plan', 'target' => 'mochi']),
                );
            }

            $actions[] = $this->postAction('Share', 'share-2', [
                'action' => 'share',
                'target' => $pet['slug'],
                'label' => $pet['name'].' profile',
            ]);

            return $actions;
        }

        $actions = [
            $this->toggleAction(
                label: 'Follow '.$pet['name'],
                icon: 'heart',
                collection: 'subscriptions',
                target: 'pet-'.$pet['slug'],
                activeLabel: 'Following '.$pet['name'],
                activeIcon: 'heart-handshake',
                action: 'toggle-subscription',
                feedbackLabel: $pet['name'],
            ),
        ];

        if ($pet['slug'] === 'scout') {
            $actions[] = $this->linkAction(
                'Invite to walk',
                'footprints',
                route('compose', ['kind' => 'walk-plan', 'target' => 'scout']),
            );
        }

        $actions[] = $this->postAction('Share', 'share-2', [
            'action' => 'share',
            'target' => $pet['slug'],
            'label' => $pet['name'].' profile',
        ]);

        return $actions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function safetyActions(string $target, string $label, string $audience): array
    {
        if ($audience === 'owner') {
            return [];
        }

        return [
            $this->toggleAction(
                label: 'Block profile',
                icon: 'ban',
                collection: 'blocks',
                target: $target,
                activeLabel: 'Unblock profile',
                activeIcon: 'shield-off',
                action: 'toggle-block',
                variant: 'paper',
                feedbackLabel: $label,
            ),
            $this->linkAction(
                'Report profile',
                'flag',
                route('compose', ['kind' => 'report-profile', 'target' => $target]),
            ),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ownerTabs(string $active, string $audience): array
    {
        return $this->tabs(
            routeName: 'profile.mia',
            active: $active,
            audience: $audience,
            definitions: [
                'overview' => ['label' => 'Overview', 'icon' => 'layout-dashboard'],
                'pets' => ['label' => 'Pets', 'icon' => 'paw-print', 'count' => '2'],
                'posts' => ['label' => 'Posts', 'icon' => 'images', 'count' => '42'],
                'about' => ['label' => 'About', 'icon' => 'circle-user-round'],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $pet
     * @return array<int, array<string, mixed>>
     */
    private function petTabs(array $pet, string $active, string $audience, bool $careVisible): array
    {
        return $this->tabs(
            routeName: $pet['route'],
            active: $active,
            audience: $audience,
            definitions: [
                'feed' => ['label' => 'Feed', 'icon' => 'newspaper'],
                'about' => ['label' => 'About', 'icon' => 'paw-print'],
                'photos' => ['label' => 'Photos', 'icon' => 'images', 'count' => (string) count($pet['gallery'])],
                'friends' => [
                    'label' => 'Friends',
                    'icon' => 'heart-handshake',
                    'count' => $pet['slug'] === 'scout' ? '28' : '14',
                ],
                'care' => ['label' => 'Care', 'icon' => $careVisible ? 'heart-pulse' : 'lock-keyhole'],
                'family' => ['label' => 'Family', 'icon' => 'users-round'],
            ],
        );
    }

    /**
     * @param  array<string, array{label: string, icon: string, count?: string}>  $definitions
     * @return array<int, array<string, mixed>>
     */
    private function tabs(
        string $routeName,
        string $active,
        string $audience,
        array $definitions,
    ): array {
        $tabs = [];

        foreach ($definitions as $key => $definition) {
            $tabs[] = [
                ...$definition,
                'href' => route($routeName, ['tab' => $key, 'view' => $audience]),
                'active' => $key === $active,
            ];
        }

        return $tabs;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function audienceOptions(string $routeName, string $tab, string $audience): array
    {
        $options = [
            'owner' => ['label' => 'Owner', 'icon' => 'key-round'],
            'public' => ['label' => 'Public', 'icon' => 'globe-2'],
            'follower' => ['label' => 'Follower', 'icon' => 'user-check'],
            'friend' => ['label' => 'Friend', 'icon' => 'users-round'],
        ];

        return array_map(
            static fn (array $option, string $key): array => [
                ...$option,
                'href' => route($routeName, ['tab' => $tab, 'view' => $key]),
                'active' => $key === $audience,
            ],
            array_values($options),
            array_keys($options),
        );
    }

    /**
     * @param  array<string, string>  $privacy
     * @return array<int, array{label: string, value: string}>
     */
    private function privacySummary(array $privacy): array
    {
        $labels = [
            'location' => 'Location',
            'pets' => 'Pet profiles',
            'posts' => 'Posts',
            'friends' => 'Friends',
            'activity' => 'Activity',
            'care' => 'Care details',
        ];
        $options = $this->visibility->options();
        $summary = [];

        foreach ($privacy as $key => $value) {
            $summary[] = [
                'label' => $labels[$key] ?? ucfirst($key),
                'value' => $options[$value] ?? 'Hidden',
            ];
        }

        return $summary;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ownerMoments(): array
    {
        return $this->scoutMoments();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function petMoments(string $slug): array
    {
        return $slug === 'scout'
            ? $this->scoutMoments()
            : $this->interactions->posts($this->pets->moments($slug));
    }

    /**
     * @return array<string, mixed>
     */
    private function linkAction(
        string $label,
        string $icon,
        string $href,
        string $variant = 'paper',
    ): array {
        return compact('label', 'icon', 'href', 'variant');
    }

    /**
     * @param  array<string, string>  $payload
     * @return array<string, mixed>
     */
    private function postAction(
        string $label,
        string $icon,
        array $payload,
        string $variant = 'paper',
    ): array {
        return [
            'label' => $label,
            'icon' => $icon,
            'endpoint' => route('actions.perform'),
            'payload' => $payload,
            'variant' => $variant,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toggleAction(
        string $label,
        string $icon,
        string $collection,
        string $target,
        string $activeLabel,
        string $activeIcon,
        string $action = 'toggle-follow',
        string $variant = 'primary',
        ?string $feedbackLabel = null,
    ): array {
        $active = $action === 'toggle-subscription'
            ? $this->state->isSubscribed($target)
            : $this->state->isActive($collection, $target);

        return [
            'label' => $label,
            'icon' => $icon,
            'endpoint' => route('actions.perform'),
            'payload' => [
                'action' => $action,
                'target' => $target,
                'label' => $feedbackLabel ?? $label,
            ],
            'variant' => $variant,
            'active' => $active,
            'active_label' => $activeLabel,
            'active_icon' => $activeIcon,
            'pressed' => $active,
        ];
    }

    private function audience(string $audience): string
    {
        return in_array($audience, ['owner', 'public', 'follower', 'friend'], true)
            ? $audience
            : 'owner';
    }
}

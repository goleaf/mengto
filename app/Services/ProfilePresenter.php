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
        $profileUrl = route('profile.mia');
        $owner = [
            'name' => __('messages.mia_carter_0e5b29cc3b'),
            'handle' => '@mia-carter',
            'location' => __('messages.richmond_portland_or_fdcefc3192'),
            'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
            'summary' => __('messages.weekend_trail_walker_foster_volunteer_and_keeper_of_two__afe0498ca0'),
            'profile_url' => $profileUrl,
            'media_target' => [
                'url' => $profileUrl,
                'label' => __('presentation.open_profile', ['name' => __('messages.mia_carter_0e5b29cc3b')]),
            ],
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
            'role' => __('messages.pet_parent_and_foster_volunteer_ebca8cdaa7'),
            'member_since' => __('messages.member_since_2024_8cd8fa63cc'),
            'status' => __('messages.open_to_weekend_walks_a7331aa83e'),
            'bio' => __('messages.mia_plans_low_pressure_neighborhood_walks_shares_foster__5c7ca41df1'),
            'cover_image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => __('messages.scout_lying_in_grass_behind_a_tennis_ball_e7cfee5e55'),
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

        $identity['location'] = $locationVisible ? $identity['location'] : __('messages.location_kept_private_867cdae4a7');
        $identity['actions'] = $this->ownerActions($audience);
        $identity['stats'] = [
            ['label' => __('messages.pets_7dc1cd7eaf'), 'value' => '2', 'detail' => __('messages.separate_profiles_3fe5ddf3c7')],
            ['label' => __('messages.followers_a145ab342a'), 'value' => '2.4k', 'detail' => __('messages.owner_audience_2690d497ed')],
            ['label' => __('messages.following_344b4271ca'), 'value' => '186', 'detail' => __('messages.people_and_pets_a74d68d13d')],
            ['label' => __('messages.posts_a80811cf68'), 'value' => '42', 'detail' => __('messages.from_mia_86b3491ce2')],
        ];

        return [
            'kind' => 'owner',
            'owner' => $this->owner(),
            'identity' => $identity,
            'page_title' => __('presentation.brand_title', [
                'title' => __('presentation.identity_with_handle', [
                    'name' => $identity['name'],
                    'handle' => $identity['handle'],
                ]),
            ]),
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
                ['label' => __('messages.best_time_9bbcba7bd0'), 'value' => __('messages.weekend_mornings_7f491ceb6f')],
                ['label' => __('messages.usual_pace_cb92b55a8a'), 'value' => __('messages.easy_to_moderate_4a32157874')],
                ['label' => __('messages.home_base_3c3cbe73c2'), 'value' => $locationVisible ? __('messages.richmond_portland_45cfbdb042') : __('messages.private_c63eb6720c')],
            ],
            'interests' => [__('messages.trail_walks_e65914f579'), __('messages.foster_care_12c77089f0'), __('messages.cat_enrichment_064d3c0748'), __('messages.quiet_parks_42c6d28887'), __('messages.positive_training_265845eade')],
            'languages' => [
                [
                    'icon' => 'languages',
                    'title' => __('messages.english_ba118bf7fc'),
                    'description' => __('messages.primary_profile_and_conversation_language_bb0e0040e7'),
                ],
                [
                    'icon' => 'languages',
                    'title' => __('messages.spanish_3411059cb8'),
                    'description' => __('messages.available_for_conversational_messages_6ba952d0f2'),
                ],
            ],
            'details' => [
                ['label' => __('messages.username_e3b89e9d33'), 'value' => $identity['handle']],
                ['label' => __('messages.account_type_dac7998fba'), 'value' => __('messages.pet_owner_and_volunteer_526f09cd38')],
                ['label' => __('messages.joined_69318b0c6a'), 'value' => '2024'],
                ['label' => __('messages.profile_language_7d015791da'), 'value' => __('messages.english_ba118bf7fc')],
            ],
            'badges' => [
                ['icon' => 'badge-check', 'label' => __('messages.email_verified_bdfb1e4f00'), 'tone' => 'mint'],
                ['icon' => 'heart-handshake', 'label' => __('messages.active_volunteer_3c5bb60697'), 'tone' => 'sun'],
                ['icon' => 'circle-check-big', 'label' => __('messages.profile_complete_cf542cf483'), 'tone' => 'surface'],
            ],
            'completion' => [
                'value' => 86,
                'label' => __('messages.profile_completeness_71a35c6f7e'),
                'detail' => __('messages.add_an_optional_website_to_finish_the_public_basics_9a38e0ad00'),
            ],
            'privacy' => $this->privacySummary($privacy),
            'safety_actions' => $this->safetyActions('owner-mia-carter', __('messages.mia_carter_0e5b29cc3b'), $audience),
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

        $pet['location'] = $locationVisible ? $pet['location'] : __('messages.location_kept_private_867cdae4a7');
        $pet['actions'] = $this->petActions($pet, $audience);
        $pet['stats'] = [
            ['label' => __('messages.followers_a145ab342a'), 'value' => $slug === 'scout' ? '1.8k' : '690', 'detail' => __('messages.pet_audience_aa5dae2193')],
            ['label' => __('messages.friends_bd104d1b98'), 'value' => $slug === 'scout' ? '28' : '14', 'detail' => __('messages.pet_connections_999fbdfe85')],
            ['label' => __('messages.moments_0b016f8b0a'), 'value' => $slug === 'scout' ? '36' : '18', 'detail' => __('messages.about_eae9424335').$pet['name']],
            ['label' => __('messages.walks_22e4ca854b'), 'value' => $slug === 'scout' ? '12' : '0', 'detail' => $slug === 'scout' ? __('messages.shared_plans_29e1ecab1c') : __('messages.indoor_routine_89dfe81292')],
        ];

        return [
            'kind' => 'pet',
            'owner' => $this->owner(),
            'identity' => $pet,
            'page_title' => __('presentation.brand_title', [
                'title' => __('presentation.identity_with_handle', [
                    'name' => $pet['name'],
                    'handle' => $pet['handle'],
                ]),
            ]),
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
                ['icon' => 'paw-print', 'label' => __('messages.pet_profile_complete_8af1889736'), 'tone' => 'mint'],
                ['icon' => 'user-round-check', 'label' => __('messages.owner_confirmed_57fe730848'), 'tone' => 'surface'],
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
                'label' => __('messages.mia_carter_0e5b29cc3b'),
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
                $this->linkAction(__('messages.edit_profile_15c4aa1303'), 'pencil', route('compose', 'profile'), 'primary'),
                $this->linkAction(__('auth.settings.action'), 'settings', route('profile.settings')),
                $this->linkAction(
                    __('messages.privacy_54a57c3147'),
                    'shield-check',
                    route('compose', ['kind' => 'profile-privacy']),
                ),
                $this->postAction(__('messages.share_29887a5ff9'), 'share-2', [
                    'action' => 'share',
                    'target' => 'mia-carter',
                    'label' => __('messages.mia_carter_profile_52e1e8d2ba'),
                ]),
            ];
        }

        return [
            $this->toggleAction(
                label: __('messages.follow_mia_13439a868f'),
                icon: 'user-plus',
                collection: 'subscriptions',
                target: 'owner-mia-carter',
                activeLabel: __('messages.following_mia_4ea69077f2'),
                activeIcon: 'user-check',
                action: 'toggle-subscription',
                feedbackLabel: __('messages.mia_carter_0e5b29cc3b'),
            ),
            $this->toggleAction(
                label: __('messages.add_friend_c1f8728197'),
                icon: 'user-round-plus',
                collection: 'friends',
                target: 'owner-mia-carter',
                activeLabel: __('messages.request_sent_a73f99f6bf'),
                activeIcon: 'clock-3',
                action: 'toggle-friend',
                feedbackLabel: __('messages.mia_carter_0e5b29cc3b'),
            ),
            $this->linkAction(
                __('messages.message_2f77668a9d'),
                'message-circle',
                route('compose', ['kind' => 'message']),
            ),
            $this->postAction(__('messages.share_29887a5ff9'), 'share-2', [
                'action' => 'share',
                'target' => 'mia-carter',
                'label' => __('messages.mia_carter_profile_52e1e8d2ba'),
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
                    __('messages.edit_profile_15c4aa1303'),
                    'pencil',
                    route('compose', ['kind' => 'pet-profile', 'pet' => $pet['slug']]),
                    'primary',
                ),
                $this->linkAction(
                    __('messages.privacy_54a57c3147'),
                    'shield-check',
                    route('compose', ['kind' => 'pet-privacy', 'pet' => $pet['slug']]),
                ),
                $this->linkAction(
                    __('messages.pet_friends_8866f0adbb'),
                    'heart-handshake',
                    route('pet-friends.index', ['pet' => $pet['slug']]),
                ),
            ];

            if ($pet['slug'] === 'scout') {
                $actions[] = $this->linkAction(
                    __('messages.plan_a_walk_10f67c3800'),
                    'footprints',
                    route('compose', ['kind' => 'walk-plan', 'target' => 'mochi']),
                );
            }

            $actions[] = $this->postAction(__('messages.share_29887a5ff9'), 'share-2', [
                'action' => 'share',
                'target' => $pet['slug'],
                'label' => __('presentation.profile_for', ['name' => $pet['name']]),
            ]);

            return $actions;
        }

        $actions = [
            $this->toggleAction(
                label: __('messages.follow_ab54ab0728').$pet['name'],
                icon: 'heart',
                collection: 'subscriptions',
                target: 'pet-'.$pet['slug'],
                activeLabel: __('presentation.following_name', ['name' => $pet['name']]),
                activeIcon: 'heart-handshake',
                action: 'toggle-subscription',
                feedbackLabel: $pet['name'],
            ),
        ];

        if ($pet['slug'] === 'scout') {
            $actions[] = $this->linkAction(
                __('messages.invite_to_walk_35311967f3'),
                'footprints',
                route('compose', ['kind' => 'walk-plan', 'target' => 'scout']),
            );
        }

        $actions[] = $this->postAction(__('messages.share_29887a5ff9'), 'share-2', [
            'action' => 'share',
            'target' => $pet['slug'],
            'label' => __('presentation.profile_for', ['name' => $pet['name']]),
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
                label: __('messages.block_profile_fe810d74e7'),
                icon: 'ban',
                collection: 'blocks',
                target: $target,
                activeLabel: __('messages.unblock_profile_2781020042'),
                activeIcon: 'shield-off',
                action: 'toggle-block',
                variant: 'paper',
                feedbackLabel: $label,
            ),
            $this->linkAction(
                __('messages.report_profile_c32c158ce5'),
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
                'overview' => ['label' => __('messages.overview_d4b1ea5708'), 'icon' => 'layout-dashboard'],
                'pets' => ['label' => __('messages.pets_7dc1cd7eaf'), 'icon' => 'paw-print', 'count' => '2'],
                'posts' => ['label' => __('messages.posts_a80811cf68'), 'icon' => 'images', 'count' => '42'],
                'about' => ['label' => __('messages.about_4efca0d10c'), 'icon' => 'circle-user-round'],
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
                'feed' => ['label' => __('messages.feed_396c3cb18f'), 'icon' => 'newspaper'],
                'about' => ['label' => __('messages.about_4efca0d10c'), 'icon' => 'paw-print'],
                'photos' => ['label' => __('messages.photos_5e3147ab51'), 'icon' => 'images', 'count' => (string) count($pet['gallery'])],
                'friends' => [
                    'label' => __('messages.friends_bd104d1b98'),
                    'icon' => 'heart-handshake',
                    'count' => $pet['slug'] === 'scout' ? '28' : '14',
                ],
                'care' => ['label' => __('messages.care_4262074d6c'), 'icon' => $careVisible ? 'heart-pulse' : 'lock-keyhole'],
                'family' => ['label' => __('messages.family_bd2d677b2e'), 'icon' => 'users-round'],
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
            'owner' => ['label' => __('messages.owner_4b1b8aa360'), 'icon' => 'key-round'],
            'public' => ['label' => __('messages.public_591935b15b'), 'icon' => 'globe-2'],
            'follower' => ['label' => __('messages.follower_9d949fae9f'), 'icon' => 'user-check'],
            'friend' => ['label' => __('messages.friend_acd8f66440'), 'icon' => 'users-round'],
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
            'location' => __('messages.location_15b61974b2'),
            'pets' => __('messages.pet_profiles_6d3a4fd8d3'),
            'posts' => __('messages.posts_a80811cf68'),
            'friends' => __('messages.friends_bd104d1b98'),
            'activity' => __('messages.activity_38da1505ca'),
            'care' => __('messages.care_details_2ea88ecec1'),
        ];
        $options = $this->visibility->options();
        $summary = [];

        foreach ($privacy as $key => $value) {
            $summary[] = [
                'label' => $labels[$key] ?? ucfirst($key),
                'value' => $options[$value] ?? __('messages.hidden_7e6fefff0f'),
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

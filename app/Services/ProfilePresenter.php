<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use LogicException;

final class ProfilePresenter
{
    public function __construct(
        private readonly PrototypeState $state,
        private readonly ProfileVisibility $visibility,
        private readonly InteractionPresenter $interactions,
        private readonly PetProfileCatalog $pets,
        private readonly CreatedContentPresenter $created,
        private readonly PetFriendCatalog $friendPets,
        private readonly AuthFactory $auth,
        private readonly AuthenticatedUserPresenter $authenticatedUsers,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function owner(): array
    {
        $user = $this->auth->guard()->user();

        if (! $user instanceof User) {
            throw new LogicException('Authenticated owner presentation requires a User.');
        }

        return $this->authenticatedUsers->present($user);
    }

    /**
     * @return array<string, mixed>
     */
    private function demoOwner(): array
    {
        $profileUrl = route('profile.mia');
        $owner = [
            'name' => __('member_profiles.owner.identity.name'),
            'handle' => __('member_profiles.owner.identity.handle'),
            'location' => __('member_profiles.owner.identity.location'),
            'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&crop=faces&w=320&h=320&q=80',
            'avatar_alt' => __('member_profiles.owner.identity.avatar_alt'),
            'summary' => __('member_profiles.owner.identity.summary'),
            'profile_url' => $profileUrl,
            'media_target' => [
                'url' => $profileUrl,
                'label' => __('member_profiles.owner.identity.media_label'),
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
            ...$this->demoOwner(),
            'slug' => 'mia-carter',
            'role' => __('member_profiles.owner.identity.role'),
            'member_since' => __('member_profiles.owner.identity.member_since'),
            'status' => __('member_profiles.owner.identity.status'),
            'bio' => __('member_profiles.owner.identity.bio'),
            'cover_image' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1600&h=760&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1624361239583-7ba5ffb376f5?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => __('member_profiles.owner.identity.cover_image_alt'),
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
        $petsRequired = in_array($tab, ['overview', 'pets'], true);
        $momentsRequired = in_array($tab, ['overview', 'posts'], true);

        $identity['location'] = $locationVisible
            ? $identity['location']
            : __('member_profiles.owner.identity.private_location');
        $identity['actions'] = $this->ownerActions($audience);
        $identity['stats'] = [
            ['label' => __('member_profiles.owner.stats.pets.label'), 'value' => '2', 'detail' => __('member_profiles.owner.stats.pets.detail')],
            ['label' => __('member_profiles.owner.stats.followers.label'), 'value' => '2.4k', 'detail' => __('member_profiles.owner.stats.followers.detail')],
            ['label' => __('member_profiles.owner.stats.following.label'), 'value' => '186', 'detail' => __('member_profiles.owner.stats.following.detail')],
            ['label' => __('member_profiles.owner.stats.posts.label'), 'value' => '42', 'detail' => __('member_profiles.owner.stats.posts.detail')],
        ];

        return [
            'kind' => 'owner',
            'owner' => $this->owner(),
            'identity' => $identity,
            'page_title' => __('member_profiles.owner.page.title', [
                'name' => $identity['name'],
                'handle' => $identity['handle'],
            ]),
            'active_section' => 'profile',
            'audience' => $audience,
            'audience_options' => $this->ownerAudienceOptions(
                routeName: 'profile.mia',
                tab: $tab,
                audience: $audience,
            ),
            'tabs' => $this->ownerTabs($tab, $audience),
            'active_tab' => $tab,
            'pets' => $petsVisible && $petsRequired ? $this->demoPets() : [],
            'pets_restricted' => ! $petsVisible,
            'moments' => $postsVisible && $momentsRequired ? $this->ownerMoments() : [],
            'posts_restricted' => ! $postsVisible,
            'availability' => [
                ['label' => __('member_profiles.owner.availability.time_label'), 'value' => __('member_profiles.owner.availability.time_value')],
                ['label' => __('member_profiles.owner.availability.pace_label'), 'value' => __('member_profiles.owner.availability.pace_value')],
                ['label' => __('member_profiles.owner.availability.home_label'), 'value' => $locationVisible ? __('member_profiles.owner.availability.home_value') : __('member_profiles.owner.availability.private_value')],
            ],
            'interests' => [
                __('member_profiles.owner.interests.trail_walks'),
                __('member_profiles.owner.interests.foster_care'),
                __('member_profiles.owner.interests.cat_enrichment'),
                __('member_profiles.owner.interests.quiet_parks'),
                __('member_profiles.owner.interests.positive_training'),
            ],
            'languages' => [
                [
                    'icon' => 'languages',
                    'title' => __('member_profiles.owner.languages.english.title'),
                    'description' => __('member_profiles.owner.languages.english.description'),
                ],
                [
                    'icon' => 'languages',
                    'title' => __('member_profiles.owner.languages.spanish.title'),
                    'description' => __('member_profiles.owner.languages.spanish.description'),
                ],
            ],
            'details' => [
                ['label' => __('member_profiles.owner.details.username'), 'value' => $identity['handle']],
                ['label' => __('member_profiles.owner.details.account_type'), 'value' => __('member_profiles.owner.details.account_type_value')],
                ['label' => __('member_profiles.owner.details.joined'), 'value' => __('member_profiles.owner.details.joined_value')],
                ['label' => __('member_profiles.owner.details.language'), 'value' => __('member_profiles.owner.details.language_value')],
            ],
            'badges' => [
                ['icon' => 'badge-check', 'label' => __('member_profiles.owner.badges.email_verified'), 'tone' => 'mint'],
                ['icon' => 'heart-handshake', 'label' => __('member_profiles.owner.badges.active_volunteer'), 'tone' => 'sun'],
                ['icon' => 'circle-check-big', 'label' => __('member_profiles.owner.badges.profile_complete'), 'tone' => 'surface'],
            ],
            'completion' => [
                'value' => 86,
                'label' => __('member_profiles.owner.completion.label'),
                'detail' => __('member_profiles.owner.completion.detail'),
            ],
            'privacy' => $this->ownerPrivacySummary($privacy),
            'safety_actions' => $this->ownerSafetyActions($audience),
            'copy' => $this->ownerCopy($audience),
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

        $pet['location'] = $locationVisible ? $pet['location'] : __('messages.location_kept_private_profile');
        $pet['actions'] = $this->petActions($pet, $audience);
        $pet['stats'] = [
            ['label' => __('messages.followers'), 'value' => $slug === 'scout' ? '1.8k' : '690', 'detail' => __('messages.pet_audience')],
            ['label' => __('messages.friends'), 'value' => $slug === 'scout' ? '28' : '14', 'detail' => __('messages.pet_connections')],
            ['label' => __('messages.moments'), 'value' => $slug === 'scout' ? '36' : '18', 'detail' => __('messages.about_lowercase_prefix').$pet['name']],
            ['label' => __('messages.walks'), 'value' => $slug === 'scout' ? '12' : '0', 'detail' => $slug === 'scout' ? __('messages.shared_plans') : __('messages.indoor_routine')],
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
            'managers' => $this->pets->managers($slug, $this->demoOwner()),
            'privacy' => $this->privacySummary($privacy),
            'badges' => [
                ['icon' => 'paw-print', 'label' => __('messages.pet_profile_complete'), 'tone' => 'mint'],
                ['icon' => 'user-round-check', 'label' => __('messages.owner_confirmed'), 'tone' => 'surface'],
            ],
            'safety_actions' => $this->safetyActions('pet-'.$slug, $pet['name'], $audience),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function pets(): array
    {
        return $this->created->pets();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function demoPets(): array
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
                'label' => __('messages.mia_carter'),
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
                $this->linkAction(__('member_profiles.owner.actions.edit'), 'pencil', route('compose', 'profile'), 'primary'),
                $this->linkAction(__('member_profiles.owner.actions.settings'), 'settings', route('profile.settings')),
                $this->linkAction(
                    __('member_profiles.owner.actions.privacy'),
                    'shield-check',
                    route('compose', ['kind' => 'profile-privacy']),
                ),
                $this->postAction(__('member_profiles.owner.actions.share'), 'share-2', [
                    'action' => 'share',
                    'target' => 'mia-carter',
                    'label' => __('member_profiles.owner.actions.profile_label'),
                ]),
            ];
        }

        return [
            $this->toggleAction(
                label: __('member_profiles.owner.actions.follow'),
                icon: 'user-plus',
                collection: 'subscriptions',
                target: 'owner-mia-carter',
                activeLabel: __('member_profiles.owner.actions.following'),
                activeIcon: 'user-check',
                action: 'toggle-subscription',
                feedbackLabel: __('member_profiles.owner.identity.name'),
            ),
            $this->toggleAction(
                label: __('member_profiles.owner.actions.friend'),
                icon: 'user-round-plus',
                collection: 'friends',
                target: 'owner-mia-carter',
                activeLabel: __('member_profiles.owner.actions.request_sent'),
                activeIcon: 'clock-3',
                action: 'toggle-friend',
                feedbackLabel: __('member_profiles.owner.identity.name'),
            ),
            $this->linkAction(
                __('member_profiles.owner.actions.message'),
                'message-circle',
                route('compose', ['kind' => 'message']),
            ),
            $this->postAction(__('member_profiles.owner.actions.share'), 'share-2', [
                'action' => 'share',
                'target' => 'mia-carter',
                'label' => __('member_profiles.owner.actions.profile_label'),
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
                    __('messages.edit_profile'),
                    'pencil',
                    route('compose', ['kind' => 'pet-profile', 'pet' => $pet['slug']]),
                    'primary',
                ),
                $this->linkAction(
                    __('messages.privacy'),
                    'shield-check',
                    route('compose', ['kind' => 'pet-privacy', 'pet' => $pet['slug']]),
                ),
                $this->linkAction(
                    __('messages.pet_friends'),
                    'heart-handshake',
                    route('pet-friends.index', ['pet' => $pet['slug']]),
                ),
            ];

            if ($pet['slug'] === 'scout') {
                $actions[] = $this->linkAction(
                    __('messages.plan_a_walk'),
                    'footprints',
                    route('compose', ['kind' => 'walk-plan', 'target' => 'mochi']),
                );
            }

            $actions[] = $this->postAction(__('messages.share'), 'share-2', [
                'action' => 'share',
                'target' => $pet['slug'],
                'label' => __('presentation.profile_for', ['name' => $pet['name']]),
            ]);

            return $actions;
        }

        $actions = [
            $this->toggleAction(
                label: __('messages.follow_prefix').$pet['name'],
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
                __('messages.invite_to_walk'),
                'footprints',
                route('compose', ['kind' => 'walk-plan', 'target' => 'scout']),
            );
        }

        $actions[] = $this->postAction(__('messages.share'), 'share-2', [
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
                label: __('messages.block_profile'),
                icon: 'ban',
                collection: 'blocks',
                target: $target,
                activeLabel: __('messages.unblock_profile'),
                activeIcon: 'shield-off',
                action: 'toggle-block',
                variant: 'paper',
                feedbackLabel: $label,
            ),
            $this->linkAction(
                __('messages.report_profile'),
                'flag',
                route('compose', ['kind' => 'report-profile', 'target' => $target]),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ownerCopy(string $audience): array
    {
        return [
            'hero' => [
                'summary_label' => __('member_profiles.owner.hero.summary_label'),
                'summary_unavailable' => __('member_profiles.owner.hero.summary_unavailable'),
                'actions_label' => __('member_profiles.owner.hero.actions_label', [
                    'name' => __('member_profiles.owner.identity.name'),
                ]),
            ],
            'tabs' => [
                'label' => __('member_profiles.owner.tabs.label'),
            ],
            'preview' => [
                'title' => __('member_profiles.owner.preview.title'),
                'description' => __('member_profiles.owner.preview.audiences.'.$audience),
                'label' => __('member_profiles.owner.preview.label'),
            ],
            'sections' => [
                'about' => [
                    'eyebrow' => __('member_profiles.owner.sections.about.eyebrow'),
                    'title' => __('member_profiles.owner.sections.about.title'),
                    'icon' => 'user-round',
                ],
                'pets' => [
                    'eyebrow' => __('member_profiles.owner.sections.pets.eyebrow'),
                    'title' => __('member_profiles.owner.sections.pets.title'),
                    'tab_eyebrow' => __('member_profiles.owner.sections.pets.tab_eyebrow'),
                    'tab_title' => __('member_profiles.owner.sections.pets.tab_title'),
                    'empty' => __('member_profiles.owner.sections.pets.empty'),
                    'icon' => 'paw-print',
                    'add_action' => [
                        'href' => route('pets.manage.create'),
                        'label' => __('member_profiles.owner.sections.pets.add'),
                        'icon' => 'plus',
                    ],
                ],
                'posts' => [
                    'eyebrow' => __('member_profiles.owner.sections.posts.eyebrow'),
                    'tab_eyebrow' => __('member_profiles.owner.sections.posts.tab_eyebrow'),
                    'title' => __('member_profiles.owner.sections.posts.title'),
                    'tab_title' => __('member_profiles.owner.sections.posts.tab_title'),
                    'empty' => __('member_profiles.owner.sections.posts.empty'),
                    'icon' => 'images',
                ],
                'details' => [
                    'eyebrow' => __('member_profiles.owner.sections.details.eyebrow'),
                    'title' => __('member_profiles.owner.sections.details.title'),
                    'icon' => 'id-card',
                ],
                'interests' => [
                    'eyebrow' => __('member_profiles.owner.sections.interests.eyebrow'),
                    'title' => __('member_profiles.owner.sections.interests.title'),
                    'empty' => __('member_profiles.owner.sections.interests.empty'),
                    'icon' => 'sparkles',
                ],
                'languages' => [
                    'eyebrow' => __('member_profiles.owner.sections.languages.eyebrow'),
                    'title' => __('member_profiles.owner.sections.languages.title'),
                    'icon' => 'languages',
                ],
                'privacy' => [
                    'eyebrow' => __('member_profiles.owner.sections.privacy.eyebrow'),
                    'title' => __('member_profiles.owner.sections.privacy.title'),
                    'icon' => 'shield-check',
                ],
                'completion' => [
                    'eyebrow' => __('member_profiles.owner.sections.completion.eyebrow'),
                    'title' => __('member_profiles.owner.sections.completion.title'),
                    'icon' => 'gauge',
                ],
                'badges' => [
                    'eyebrow' => __('member_profiles.owner.sections.badges.eyebrow'),
                    'title' => __('member_profiles.owner.sections.badges.title'),
                    'icon' => 'badge-check',
                ],
                'availability' => [
                    'eyebrow' => __('member_profiles.owner.sections.availability.eyebrow'),
                    'title' => __('member_profiles.owner.sections.availability.title'),
                    'icon' => 'calendar-clock',
                ],
                'safety' => [
                    'eyebrow' => __('member_profiles.owner.sections.safety.eyebrow'),
                    'title' => __('member_profiles.owner.sections.safety.title'),
                    'description' => __('member_profiles.owner.sections.safety.description'),
                    'actions_label' => __('member_profiles.owner.sections.safety.actions_label'),
                    'icon' => 'shield-alert',
                ],
            ],
            'restrictions' => [
                'pets' => [
                    'title' => __('member_profiles.owner.restrictions.pets.title'),
                    'tab_description' => __('member_profiles.owner.restrictions.pets.tab_description'),
                    'overview_description' => __('member_profiles.owner.restrictions.pets.overview_description'),
                ],
                'posts' => [
                    'title' => __('member_profiles.owner.restrictions.posts.title'),
                    'tab_description' => __('member_profiles.owner.restrictions.posts.tab_description'),
                    'overview_title' => __('member_profiles.owner.restrictions.posts.overview_title'),
                    'overview_description' => __('member_profiles.owner.restrictions.posts.overview_description'),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ownerSafetyActions(string $audience): array
    {
        if ($audience === 'owner') {
            return [];
        }

        return [
            $this->toggleAction(
                label: __('member_profiles.owner.actions.block'),
                icon: 'ban',
                collection: 'blocks',
                target: 'owner-mia-carter',
                activeLabel: __('member_profiles.owner.actions.unblock'),
                activeIcon: 'shield-off',
                action: 'toggle-block',
                variant: 'paper',
                feedbackLabel: __('member_profiles.owner.identity.name'),
            ),
            $this->linkAction(
                __('member_profiles.owner.actions.report'),
                'flag',
                route('compose', ['kind' => 'report-profile', 'target' => 'owner-mia-carter']),
            ),
        ];
    }

    /**
     * @param  array<string, string>  $privacy
     * @return array<int, array{label: string, value: string}>
     */
    private function ownerPrivacySummary(array $privacy): array
    {
        return array_map(
            static fn (string $value, string $key): array => [
                'label' => __('member_profiles.owner.privacy.labels.'.$key),
                'value' => __('member_profiles.owner.privacy.values.'.$value),
            ],
            array_values($privacy),
            array_keys($privacy),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function ownerAudienceOptions(string $routeName, string $tab, string $audience): array
    {
        $options = [
            'owner' => ['label' => __('member_profiles.owner.preview.options.owner'), 'icon' => 'key-round'],
            'public' => ['label' => __('member_profiles.owner.preview.options.public'), 'icon' => 'globe-2'],
            'follower' => ['label' => __('member_profiles.owner.preview.options.follower'), 'icon' => 'user-check'],
            'friend' => ['label' => __('member_profiles.owner.preview.options.friend'), 'icon' => 'users-round'],
        ];

        return array_map(
            static fn (array $option, string $key): array => [
                ...$option,
                'code' => $key,
                'href' => route($routeName, ['tab' => $tab, 'view' => $key]),
                'active' => $key === $audience,
            ],
            array_values($options),
            array_keys($options),
        );
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
                'overview' => ['label' => __('member_profiles.owner.tabs.overview'), 'icon' => 'layout-dashboard'],
                'pets' => ['label' => __('member_profiles.owner.tabs.pets'), 'icon' => 'paw-print', 'count' => '2'],
                'posts' => ['label' => __('member_profiles.owner.tabs.posts'), 'icon' => 'images', 'count' => '42'],
                'about' => ['label' => __('member_profiles.owner.tabs.about'), 'icon' => 'circle-user-round'],
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
                'feed' => ['label' => __('messages.feed'), 'icon' => 'newspaper'],
                'about' => ['label' => __('messages.about'), 'icon' => 'paw-print'],
                'photos' => ['label' => __('messages.photos'), 'icon' => 'images', 'count' => (string) count($pet['gallery'])],
                'friends' => [
                    'label' => __('messages.friends'),
                    'icon' => 'heart-handshake',
                    'count' => $pet['slug'] === 'scout' ? '28' : '14',
                ],
                'care' => ['label' => __('messages.care'), 'icon' => $careVisible ? 'heart-pulse' : 'lock-keyhole'],
                'family' => ['label' => __('messages.family'), 'icon' => 'users-round'],
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
                'code' => $key,
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
            'owner' => ['label' => __('messages.owner'), 'icon' => 'key-round'],
            'public' => ['label' => __('messages.public'), 'icon' => 'globe-2'],
            'follower' => ['label' => __('messages.follower'), 'icon' => 'user-check'],
            'friend' => ['label' => __('messages.friend'), 'icon' => 'users-round'],
        ];

        return array_map(
            static fn (array $option, string $key): array => [
                ...$option,
                'code' => $key,
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
            'location' => __('messages.location'),
            'pets' => __('messages.pet_profiles'),
            'posts' => __('messages.posts'),
            'friends' => __('messages.friends'),
            'activity' => __('messages.activity'),
            'care' => __('messages.care_details'),
        ];
        $options = $this->visibility->options();
        $summary = [];

        foreach ($privacy as $key => $value) {
            $summary[] = [
                'label' => $labels[$key] ?? ucfirst($key),
                'value' => $options[$value] ?? __('messages.hidden'),
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

<?php

namespace App\Services;

final class ConnectionPresenter
{
    public function __construct(
        private readonly ConnectionCatalog $catalog,
        private readonly PrototypeState $state,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function page(
        string $tab = 'following',
        string $type = 'all',
        string $sort = 'recommended',
    ): array {
        $tab = in_array($tab, ['following', 'followers', 'requests', 'recommendations'], true)
            ? $tab
            : 'following';
        $type = array_key_exists($type, $this->typeOptions()) ? $type : 'all';
        $sort = array_key_exists($sort, $this->sortOptions()) ? $sort : 'recommended';
        $items = $this->items($tab);
        $items = $this->filter($items, $type);
        $items = $this->sort($items, $sort);
        $items = $this->withReturnState($items, $tab, $type, $sort);
        $summary = $this->summary();

        return [
            'page_title' => __('messages.connections_and_recommendations_pawcircle_f952cfc656'),
            'active_section' => 'circle',
            'summary' => [
                'eyebrow' => __('messages.your_social_graph_5e99767a29'),
                'title' => __('messages.connections_you_control_0a4f0a20d7'),
                'description' => __('messages.people_pets_organizations_and_interests_stay_separate_wi_aa07a86bc8'),
                'count' => trans_choice('presentation.results_count', count($items), ['count' => count($items)]),
                'stats' => $summary,
            ],
            'connections' => [
                'tab' => $tab,
                'type' => $type,
                'sort' => $sort,
                'tabs' => $this->tabs($tab, $type, $sort, $summary),
                'type_options' => $this->typeOptions(),
                'sort_options' => $this->sortOptions(),
                'items' => $items,
                'empty' => $this->emptyState($tab),
                'last_dismissed' => $this->lastDismissed($type, $sort),
                'last_blocked' => $this->lastBlocked($tab, $type, $sort),
                'endpoint' => route('actions.perform'),
                'browse_url' => route('connections.index'),
                'feed_url' => route('preview.feed', [
                    'feed' => 'following',
                    'sort' => 'latest',
                ]),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function target(string $target): ?array
    {
        return $this->catalog->find($target);
    }

    public function isRecommendation(string $target): bool
    {
        foreach ($this->catalog->recommendations() as $recommendation) {
            if ($recommendation['target'] === $target) {
                return true;
            }
        }

        return false;
    }

    public function isFollower(string $target): bool
    {
        if (in_array($target, $this->catalog->followerTargets(), true)) {
            return true;
        }

        return $this->state->incomingFollowRequestStatus($target) === 'accepted';
    }

    public function isIncomingRequest(string $target): bool
    {
        return in_array($target, $this->catalog->incomingRequestTargets(), true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function items(string $tab): array
    {
        return match ($tab) {
            'followers' => $this->followers(),
            'requests' => $this->requests(),
            'recommendations' => $this->recommendations(),
            default => $this->following(),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function following(): array
    {
        $items = [];

        foreach ($this->state->subscriptions() as $target => $subscription) {
            $record = $this->catalog->find($target);

            if ($record === null || $this->blocked($target)) {
                continue;
            }

            $items[] = $this->decorate($record, [
                'context' => __('presentation.following_since', [
                    'date' => $this->followedDate((string) $subscription['followed_at']),
                ]),
                'following' => true,
                'favorite' => (bool) $subscription['favorite'],
                'muted' => (bool) $subscription['muted'],
                'notification_level' => (string) $subscription['notification_level'],
                'sort_order' => (string) $subscription['followed_at'],
                'primary_action' => $this->action(
                    'toggle-subscription',
                    $target,
                    __('messages.following_344b4271ca'),
                    'user-check',
                    'paper',
                    'following',
                ),
                'secondary_actions' => [
                    $this->action(
                        'toggle-subscription-favorite',
                        $target,
                        (bool) $subscription['favorite'] ? __('messages.remove_favorite_5bc0aa08d6') : __('messages.add_to_favorites_7f3c0782af'),
                        (bool) $subscription['favorite'] ? 'star-off' : 'star',
                    ),
                    $this->action(
                        'toggle-subscription-mute',
                        $target,
                        (bool) $subscription['muted'] ? __('messages.show_in_feed_6905741e64') : __('messages.mute_in_feed_4b05e4a21e'),
                        (bool) $subscription['muted'] ? 'volume-2' : 'volume-x',
                    ),
                    $this->action('toggle-connection-block', $target, __('messages.block_profile_fe810d74e7'), 'ban'),
                ],
                'notification_options' => $this->notificationOptions(
                    $target,
                    (string) $subscription['notification_level'],
                ),
            ]);
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function followers(): array
    {
        $targets = [
            ...$this->catalog->followerTargets(),
            ...$this->state->acceptedFollowerTargets(),
        ];
        $items = [];

        foreach (array_values(array_unique($targets)) as $index => $target) {
            $record = $this->catalog->find($target);

            if ($record === null || $this->state->followerIsRemoved($target) || $this->blocked($target)) {
                continue;
            }

            $items[] = $this->decorate($record, [
                'context' => $index === 0 ? __('messages.new_follower_this_week_f79e52e2fc') : __('messages.follows_your_public_updates_3e725dc2db'),
                'sort_order' => str_pad((string) (99 - $index), 2, '0', STR_PAD_LEFT),
                'primary_action' => [
                    'label' => __('messages.view_profile_d4788f256f'),
                    'icon' => 'circle-user-round',
                    'href' => $this->href($record),
                    'variant' => 'paper',
                ],
                'secondary_actions' => [
                    $this->action('remove-follower', $target, __('messages.remove_follower_abea22ee75'), 'user-minus', 'quiet', 'followers'),
                    $this->action('toggle-connection-block', $target, __('messages.block_profile_fe810d74e7'), 'ban', 'quiet', 'followers'),
                ],
            ]);
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function requests(): array
    {
        $items = [];

        foreach ($this->catalog->incomingRequestTargets() as $index => $target) {
            if ($this->state->incomingFollowRequestStatus($target) !== 'pending') {
                continue;
            }

            $record = $this->catalog->find($target);

            if ($record === null || $this->blocked($target)) {
                continue;
            }

            $items[] = $this->decorate($record, [
                'context' => __('messages.wants_to_follow_your_owner_profile_e653df5e16'),
                'request_direction' => 'incoming',
                'sort_order' => str_pad((string) (99 - $index), 2, '0', STR_PAD_LEFT),
                'primary_action' => $this->action(
                    'accept-follow-request',
                    $target,
                    __('messages.accept_89713b9c9c'),
                    'user-check',
                    'primary',
                    'requests',
                ),
                'secondary_actions' => [
                    $this->action('decline-follow-request', $target, __('messages.decline_a2d285b352'), 'user-x', 'paper', 'requests'),
                    $this->action('toggle-connection-block', $target, __('messages.block_profile_fe810d74e7'), 'ban', 'quiet', 'requests'),
                ],
            ]);
        }

        foreach ($this->state->outgoingFollowRequests() as $target => $status) {
            if ($status !== 'pending') {
                continue;
            }

            $record = $this->catalog->find($target);

            if ($record === null || $this->blocked($target)) {
                continue;
            }

            $primaryAction = $this->action(
                'toggle-follow-request',
                $target,
                __('messages.request_sent_a73f99f6bf'),
                'clock-3',
                'paper',
                'requests',
            );
            $primaryAction['active'] = true;
            $primaryAction['pressed'] = true;

            $items[] = $this->decorate($record, [
                'context' => __('messages.your_request_is_waiting_for_approval_1b63d48504'),
                'request_direction' => 'outgoing',
                'sort_order' => '10',
                'primary_action' => $primaryAction,
                'secondary_actions' => [],
            ]);
        }

        return $items;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recommendations(): array
    {
        $items = [];

        foreach ($this->catalog->recommendations() as $index => $recommendation) {
            $target = $recommendation['target'];

            if (
                $this->state->isSubscribed($target)
                || $this->state->recommendationIsDismissed($target)
                || $this->blocked($target)
            ) {
                continue;
            }

            $record = $this->catalog->find($target);

            if ($record === null) {
                continue;
            }

            $requestPending = $this->state->outgoingFollowRequestStatus($target) === 'pending';
            $action = $record['private']
                ? $this->action(
                    'toggle-follow-request',
                    $target,
                    $requestPending ? __('messages.request_sent_a73f99f6bf') : __('messages.request_follow_8bd513a22d'),
                    $requestPending ? 'clock-3' : 'user-plus',
                    $requestPending ? 'paper' : 'primary',
                    'recommendations',
                )
                : $this->action(
                    'toggle-subscription',
                    $target,
                    __('messages.follow_641d1ef657'),
                    'user-plus',
                    'primary',
                    'recommendations',
                );
            $action['active'] = $requestPending;
            $action['pressed'] = $requestPending;

            $items[] = $this->decorate($record, [
                'context' => $recommendation['group'],
                'recommendation_reason' => $recommendation['reason'],
                'signals' => $recommendation['signals'],
                'sort_order' => str_pad((string) (99 - $index), 2, '0', STR_PAD_LEFT),
                'primary_action' => $action,
                'secondary_actions' => [
                    $this->action(
                        'dismiss-recommendation',
                        $target,
                        __('messages.not_interested_7991fb9792'),
                        'eye-off',
                        'quiet',
                        'recommendations',
                    ),
                    $this->action(
                        'toggle-connection-block',
                        $target,
                        __('messages.block_profile_fe810d74e7'),
                        'ban',
                        'quiet',
                        'recommendations',
                    ),
                ],
            ]);
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function decorate(array $record, array $state): array
    {
        return [
            ...$record,
            'type_label' => $record['typeLabel'],
            'image_alt' => $record['imageAlt'],
            'href' => $this->href($record),
            'following' => false,
            'favorite' => false,
            'muted' => false,
            'notification_level' => null,
            'notification_options' => [],
            'secondary_actions' => [],
            'signals' => [],
            'recommendation_reason' => null,
            'request_direction' => null,
            ...$state,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function filter(array $items, string $type): array
    {
        if ($type === 'all') {
            return $items;
        }

        return array_values(array_filter(
            $items,
            static fn (array $item): bool => $item['type'] === $type,
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function sort(array $items, string $sort): array
    {
        if ($sort === 'name') {
            usort($items, static fn (array $left, array $right): int => strcasecmp($left['name'], $right['name']));
        }

        if ($sort === 'recent') {
            usort(
                $items,
                static fn (array $left, array $right): int => strcmp(
                    (string) $right['sort_order'],
                    (string) $left['sort_order'],
                ),
            );
        }

        return $items;
    }

    /**
     * @return array<int, array{label: string, value: string, detail: string}>
     */
    private function summary(): array
    {
        $subscriptions = $this->state->subscriptions();
        $following = count(array_filter(
            array_keys($subscriptions),
            fn (string $target): bool => ! $this->blocked($target),
        ));
        $followers = count(array_filter(
            array_unique([
                ...$this->catalog->followerTargets(),
                ...$this->state->acceptedFollowerTargets(),
            ]),
            fn (string $target): bool => ! $this->state->followerIsRemoved($target) && ! $this->blocked($target),
        ));
        $requests = count(array_filter(
            $this->catalog->incomingRequestTargets(),
            fn (string $target): bool => $this->state->incomingFollowRequestStatus($target) === 'pending'
                && ! $this->blocked($target),
        )) + count(array_filter(
            $this->state->outgoingFollowRequests(),
            fn (string $status, string $target): bool => $status === 'pending' && ! $this->blocked($target),
            ARRAY_FILTER_USE_BOTH,
        ));
        $favorites = count(array_filter(
            $subscriptions,
            fn (array $subscription, string $target): bool => (bool) $subscription['favorite']
                && ! $this->blocked($target),
            ARRAY_FILTER_USE_BOTH,
        ));

        return [
            ['label' => __('messages.following_344b4271ca'), 'value' => (string) $following, 'detail' => __('messages.exact_targets_f218a155ed')],
            ['label' => __('messages.followers_a145ab342a'), 'value' => (string) $followers, 'detail' => __('messages.visible_profiles_73d919ac75')],
            ['label' => __('messages.requests_ada27592c9'), 'value' => (string) $requests, 'detail' => __('messages.need_attention_8db7dd4122')],
            ['label' => __('messages.favorites_7a1f2a83ac'), 'value' => (string) $favorites, 'detail' => __('messages.priority_profiles_821d4bdbf2')],
        ];
    }

    /**
     * @param  array<int, array{label: string, value: string, detail: string}>  $summary
     * @return array<int, array<string, mixed>>
     */
    private function tabs(string $active, string $type, string $sort, array $summary): array
    {
        $definitions = [
            'following' => ['label' => __('messages.following_344b4271ca'), 'icon' => 'user-check', 'count' => $summary[0]['value']],
            'followers' => ['label' => __('messages.followers_a145ab342a'), 'icon' => 'users-round', 'count' => $summary[1]['value']],
            'requests' => ['label' => __('messages.requests_ada27592c9'), 'icon' => 'inbox', 'count' => $summary[2]['value']],
            'recommendations' => ['label' => __('messages.recommendations_0738ee00b6'), 'icon' => 'sparkles'],
        ];
        $tabs = [];

        foreach ($definitions as $key => $definition) {
            $tabs[] = [
                ...$definition,
                'href' => route('connections.index', [
                    'tab' => $key,
                    'type' => $type,
                    'sort' => $sort,
                ]),
                'active' => $active === $key,
            ];
        }

        return $tabs;
    }

    /**
     * @return array<string, string>
     */
    private function typeOptions(): array
    {
        return [
            'all' => __('messages.all_types_f10988e79e'),
            'people' => __('messages.people_7db2089705'),
            'pets' => __('messages.pets_7dc1cd7eaf'),
            'organizations' => __('messages.organizations_2730183d6b'),
            'specialists' => __('messages.specialists_fc75c064bb'),
            'groups' => __('messages.groups_39bbb719fa'),
            'topics' => __('messages.topics_e22820fcf5'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'recommended' => __('messages.best_match_d83ab68f74'),
            'recent' => __('messages.most_recent_7459b86904'),
            'name' => __('messages.name_dcd1d5223f'),
        ];
    }

    /**
     * @return array{icon: string, title: string, description: string}
     */
    private function emptyState(string $tab): array
    {
        return match ($tab) {
            'followers' => [
                'icon' => 'users-round',
                'title' => __('messages.no_followers_match_this_filter_03c0ff705c'),
                'description' => __('messages.change_the_profile_type_or_return_to_all_followers_d3ebb96d09'),
            ],
            'requests' => [
                'icon' => 'inbox',
                'title' => __('messages.no_requests_need_attention_f2b6b9cbfa'),
                'description' => __('messages.incoming_and_private_profile_requests_will_appear_here_a249607804'),
            ],
            'recommendations' => [
                'icon' => 'sparkles',
                'title' => __('messages.recommendations_are_tuned_5a8cb5cfdb'),
                'description' => __('messages.change_the_type_filter_or_restore_a_recently_hidden_sugg_787db10838'),
            ],
            default => [
                'icon' => 'user-check',
                'title' => __('messages.no_subscriptions_match_this_filter_5e4281feea'),
                'description' => __('messages.follow_a_recommendation_or_change_the_selected_profile_t_cdcc1deb4f'),
            ],
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lastDismissed(string $type, string $sort): ?array
    {
        $target = $this->state->lastDismissedRecommendation();
        $record = $target === null ? null : $this->catalog->find($target);

        if ($record === null) {
            return null;
        }

        $action = $this->action(
            'undo-recommendation-dismissal',
            $target,
            __('messages.undo_a8283ade31'),
            'undo-2',
            'paper',
            'recommendations',
        );
        $action['payload'] = [
            ...$action['payload'],
            'return_type' => $type,
            'return_sort' => $sort,
        ];

        return [
            'name' => $record['name'],
            'action' => $action,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lastBlocked(string $tab, string $type, string $sort): ?array
    {
        $target = $this->state->lastBlockedConnection();
        $record = $target === null ? null : $this->catalog->find($target);

        if ($record === null) {
            return null;
        }

        $action = $this->action(
            'toggle-connection-block',
            $target,
            __('messages.undo_block_6b7c1d55a0'),
            'undo-2',
            'paper',
            $tab,
        );
        $action['payload'] = [
            ...$action['payload'],
            'return_type' => $type,
            'return_sort' => $sort,
        ];

        return [
            'name' => $record['name'],
            'action' => $action,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function withReturnState(array $items, string $tab, string $type, string $sort): array
    {
        return array_map(function (array $item) use ($tab, $type, $sort): array {
            $item['primary_action'] = $this->appendReturnState(
                $item['primary_action'],
                $tab,
                $type,
                $sort,
            );
            $item['secondary_actions'] = array_map(
                fn (array $action): array => $this->appendReturnState($action, $tab, $type, $sort),
                $item['secondary_actions'],
            );
            $item['notification_options'] = array_map(
                static fn (array $option): array => [
                    ...$option,
                    'payload' => [
                        ...$option['payload'],
                        'return_tab' => $tab,
                        'return_type' => $type,
                        'return_sort' => $sort,
                    ],
                ],
                $item['notification_options'],
            );

            return $item;
        }, $items);
    }

    /**
     * @param  array<string, mixed>  $action
     * @return array<string, mixed>
     */
    private function appendReturnState(
        array $action,
        string $tab,
        string $type,
        string $sort,
    ): array {
        if (! isset($action['payload'])) {
            return $action;
        }

        return [
            ...$action,
            'payload' => [
                ...$action['payload'],
                'return_tab' => $tab,
                'return_type' => $type,
                'return_sort' => $sort,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function notificationOptions(string $target, string $active): array
    {
        $labels = [
            'all' => __('messages.all_posts_3a2e5c2c34'),
            'important' => __('messages.important_only_c2c4224926'),
            'standard' => __('messages.standard_ef6691545d'),
            'feed' => __('messages.feed_only_1e67b011dc'),
            'off' => __('messages.paused_e159b06187'),
        ];
        $options = [];

        foreach ($labels as $value => $label) {
            $options[] = [
                'label' => $label,
                'active' => $value === $active,
                'payload' => [
                    'action' => 'set-subscription-notifications',
                    'target' => $target,
                    'notification_level' => $value,
                    'return_tab' => 'following',
                ],
            ];
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    private function action(
        string $action,
        string $target,
        string $label,
        string $icon,
        string $variant = 'quiet',
        string $returnTab = 'following',
    ): array {
        return [
            'label' => $label,
            'icon' => $icon,
            'endpoint' => route('actions.perform'),
            'payload' => [
                'action' => $action,
                'target' => $target,
                'return_tab' => $returnTab,
            ],
            'variant' => $variant,
        ];
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function href(array $record): string
    {
        return route($record['routeName'], $record['routeParameters']);
    }

    private function blocked(string $target): bool
    {
        return $this->state->isActive('blocks', $target);
    }

    private function followedDate(string $value): string
    {
        return match (true) {
            str_starts_with($value, '2026-07-') => __('messages.july_2026_012fc02ad4'),
            str_starts_with($value, '2026-06-') => __('messages.june_2026_ee00ffb56d'),
            default => 'recently',
        };
    }
}

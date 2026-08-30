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
            'page_title' => __('messages.connections_and_recommendations_brand'),
            'active_section' => 'circle',
            'summary' => [
                'eyebrow' => __('messages.your_social_graph'),
                'title' => __('messages.connections_you_control'),
                'description' => __('messages.people_pets_organizations_and_interests_stay_separate_with_clear_feed_and_notification_settings'),
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
                    __('messages.following'),
                    'user-check',
                    'paper',
                    'following',
                ),
                'secondary_actions' => [
                    $this->action(
                        'toggle-subscription-favorite',
                        $target,
                        (bool) $subscription['favorite'] ? __('messages.remove_favorite') : __('messages.add_to_favorites'),
                        (bool) $subscription['favorite'] ? 'star-off' : 'star',
                    ),
                    $this->action(
                        'toggle-subscription-mute',
                        $target,
                        (bool) $subscription['muted'] ? __('messages.show_in_feed') : __('messages.mute_in_feed'),
                        (bool) $subscription['muted'] ? 'volume-2' : 'volume-x',
                    ),
                    $this->action('toggle-connection-block', $target, __('messages.block_profile'), 'ban'),
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
                'context' => $index === 0 ? __('messages.new_follower_this_week') : __('messages.follows_your_public_updates'),
                'sort_order' => str_pad((string) (99 - $index), 2, '0', STR_PAD_LEFT),
                'primary_action' => [
                    'label' => __('messages.view_profile'),
                    'icon' => 'circle-user-round',
                    'href' => $this->href($record),
                    'variant' => 'paper',
                ],
                'secondary_actions' => [
                    $this->action('remove-follower', $target, __('messages.remove_follower'), 'user-minus', 'quiet', 'followers'),
                    $this->action('toggle-connection-block', $target, __('messages.block_profile'), 'ban', 'quiet', 'followers'),
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
                'context' => __('messages.wants_to_follow_your_owner_profile'),
                'request_direction' => 'incoming',
                'sort_order' => str_pad((string) (99 - $index), 2, '0', STR_PAD_LEFT),
                'primary_action' => $this->action(
                    'accept-follow-request',
                    $target,
                    __('messages.accept'),
                    'user-check',
                    'primary',
                    'requests',
                ),
                'secondary_actions' => [
                    $this->action('decline-follow-request', $target, __('messages.decline'), 'user-x', 'paper', 'requests'),
                    $this->action('toggle-connection-block', $target, __('messages.block_profile'), 'ban', 'quiet', 'requests'),
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
                __('messages.request_sent'),
                'clock-3',
                'paper',
                'requests',
            );
            $primaryAction['active'] = true;
            $primaryAction['pressed'] = true;

            $items[] = $this->decorate($record, [
                'context' => __('messages.your_request_is_waiting_for_approval'),
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
                    $requestPending ? __('messages.request_sent') : __('messages.request_follow'),
                    $requestPending ? 'clock-3' : 'user-plus',
                    $requestPending ? 'paper' : 'primary',
                    'recommendations',
                )
                : $this->action(
                    'toggle-subscription',
                    $target,
                    __('messages.follow'),
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
                        __('messages.not_interested'),
                        'eye-off',
                        'quiet',
                        'recommendations',
                    ),
                    $this->action(
                        'toggle-connection-block',
                        $target,
                        __('messages.block_profile'),
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
            ['label' => __('messages.following'), 'value' => (string) $following, 'detail' => __('messages.exact_targets')],
            ['label' => __('messages.followers'), 'value' => (string) $followers, 'detail' => __('messages.visible_profiles')],
            ['label' => __('messages.requests'), 'value' => (string) $requests, 'detail' => __('messages.need_attention')],
            ['label' => __('messages.favorites'), 'value' => (string) $favorites, 'detail' => __('messages.priority_profiles')],
        ];
    }

    /**
     * @param  array<int, array{label: string, value: string, detail: string}>  $summary
     * @return array<int, array<string, mixed>>
     */
    private function tabs(string $active, string $type, string $sort, array $summary): array
    {
        $definitions = [
            'following' => ['label' => __('messages.following'), 'icon' => 'user-check', 'count' => $summary[0]['value']],
            'followers' => ['label' => __('messages.followers'), 'icon' => 'users-round', 'count' => $summary[1]['value']],
            'requests' => ['label' => __('messages.requests'), 'icon' => 'inbox', 'count' => $summary[2]['value']],
            'recommendations' => ['label' => __('messages.recommendations'), 'icon' => 'sparkles'],
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
            'all' => __('messages.all_types'),
            'people' => __('messages.people'),
            'pets' => __('messages.pets'),
            'organizations' => __('messages.organizations'),
            'specialists' => __('messages.specialists'),
            'groups' => __('messages.groups'),
            'topics' => __('messages.topics'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'recommended' => __('messages.best_match'),
            'recent' => __('messages.most_recent'),
            'name' => __('messages.name'),
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
                'title' => __('messages.no_followers_match_this_filter'),
                'description' => __('messages.change_the_profile_type_or_return_to_all_followers'),
            ],
            'requests' => [
                'icon' => 'inbox',
                'title' => __('messages.no_requests_need_attention'),
                'description' => __('messages.incoming_and_private_profile_requests_will_appear_here'),
            ],
            'recommendations' => [
                'icon' => 'sparkles',
                'title' => __('messages.recommendations_are_tuned'),
                'description' => __('messages.change_the_type_filter_or_restore_a_recently_hidden_suggestion'),
            ],
            default => [
                'icon' => 'user-check',
                'title' => __('messages.no_subscriptions_match_this_filter'),
                'description' => __('messages.follow_a_recommendation_or_change_the_selected_profile_type'),
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
            __('messages.undo'),
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
            __('messages.undo_block'),
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
            'all' => __('messages.all_posts'),
            'important' => __('messages.important_only'),
            'standard' => __('messages.standard'),
            'feed' => __('messages.feed_only'),
            'off' => __('messages.paused'),
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
            str_starts_with($value, '2026-07-') => __('messages.july_2026'),
            str_starts_with($value, '2026-06-') => __('messages.june_2026'),
            default => 'recently',
        };
    }
}

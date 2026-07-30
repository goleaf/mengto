<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PawCircleConnectionPresenter
{
    public function __construct(
        private readonly PawCircleConnectionCatalog $catalog,
        private readonly PawCirclePrototypeState $state,
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
            'page_title' => 'Connections and recommendations | PawCircle',
            'active_section' => 'circle',
            'summary' => [
                'eyebrow' => 'Your social graph',
                'title' => 'Connections you control',
                'description' => 'People, pets, organizations, and interests stay separate, with clear feed and notification settings.',
                'count' => count($items).' '.Str::plural('result', count($items)),
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
                'endpoint' => route('pet-social.actions.perform'),
                'browse_url' => route('pet-social.connections.index'),
                'feed_url' => route('pet-social.preview', [
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
                'context' => 'Following since '.$this->followedDate((string) $subscription['followed_at']),
                'following' => true,
                'favorite' => (bool) $subscription['favorite'],
                'muted' => (bool) $subscription['muted'],
                'notification_level' => (string) $subscription['notification_level'],
                'sort_order' => (string) $subscription['followed_at'],
                'primary_action' => $this->action(
                    'toggle-subscription',
                    $target,
                    'Following',
                    'user-check',
                    'paper',
                    'following',
                ),
                'secondary_actions' => [
                    $this->action(
                        'toggle-subscription-favorite',
                        $target,
                        (bool) $subscription['favorite'] ? 'Remove favorite' : 'Add to favorites',
                        (bool) $subscription['favorite'] ? 'star-off' : 'star',
                    ),
                    $this->action(
                        'toggle-subscription-mute',
                        $target,
                        (bool) $subscription['muted'] ? 'Show in feed' : 'Mute in feed',
                        (bool) $subscription['muted'] ? 'volume-2' : 'volume-x',
                    ),
                    $this->action('toggle-connection-block', $target, 'Block profile', 'ban'),
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
                'context' => $index === 0 ? 'New follower this week' : 'Follows your public updates',
                'sort_order' => str_pad((string) (99 - $index), 2, '0', STR_PAD_LEFT),
                'primary_action' => [
                    'label' => 'View profile',
                    'icon' => 'circle-user-round',
                    'href' => $this->href($record),
                    'variant' => 'paper',
                ],
                'secondary_actions' => [
                    $this->action('remove-follower', $target, 'Remove follower', 'user-minus', 'quiet', 'followers'),
                    $this->action('toggle-connection-block', $target, 'Block profile', 'ban', 'quiet', 'followers'),
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
                'context' => 'Wants to follow your owner profile',
                'request_direction' => 'incoming',
                'sort_order' => str_pad((string) (99 - $index), 2, '0', STR_PAD_LEFT),
                'primary_action' => $this->action(
                    'accept-follow-request',
                    $target,
                    'Accept',
                    'user-check',
                    'primary',
                    'requests',
                ),
                'secondary_actions' => [
                    $this->action('decline-follow-request', $target, 'Decline', 'user-x', 'paper', 'requests'),
                    $this->action('toggle-connection-block', $target, 'Block profile', 'ban', 'quiet', 'requests'),
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
                'Request sent',
                'clock-3',
                'paper',
                'requests',
            );
            $primaryAction['active'] = true;
            $primaryAction['pressed'] = true;

            $items[] = $this->decorate($record, [
                'context' => 'Your request is waiting for approval',
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
                    $requestPending ? 'Request sent' : 'Request follow',
                    $requestPending ? 'clock-3' : 'user-plus',
                    $requestPending ? 'paper' : 'primary',
                    'recommendations',
                )
                : $this->action(
                    'toggle-subscription',
                    $target,
                    'Follow',
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
                        'Not interested',
                        'eye-off',
                        'quiet',
                        'recommendations',
                    ),
                    $this->action(
                        'toggle-connection-block',
                        $target,
                        'Block profile',
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
            ['label' => 'Following', 'value' => (string) $following, 'detail' => 'exact targets'],
            ['label' => 'Followers', 'value' => (string) $followers, 'detail' => 'visible profiles'],
            ['label' => 'Requests', 'value' => (string) $requests, 'detail' => 'need attention'],
            ['label' => 'Favorites', 'value' => (string) $favorites, 'detail' => 'priority profiles'],
        ];
    }

    /**
     * @param  array<int, array{label: string, value: string, detail: string}>  $summary
     * @return array<int, array<string, mixed>>
     */
    private function tabs(string $active, string $type, string $sort, array $summary): array
    {
        $definitions = [
            'following' => ['label' => 'Following', 'icon' => 'user-check', 'count' => $summary[0]['value']],
            'followers' => ['label' => 'Followers', 'icon' => 'users-round', 'count' => $summary[1]['value']],
            'requests' => ['label' => 'Requests', 'icon' => 'inbox', 'count' => $summary[2]['value']],
            'recommendations' => ['label' => 'Recommendations', 'icon' => 'sparkles'],
        ];
        $tabs = [];

        foreach ($definitions as $key => $definition) {
            $tabs[] = [
                ...$definition,
                'href' => route('pet-social.connections.index', [
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
            'all' => 'All types',
            'people' => 'People',
            'pets' => 'Pets',
            'organizations' => 'Organizations',
            'specialists' => 'Specialists',
            'groups' => 'Groups',
            'topics' => 'Topics',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'recommended' => 'Best match',
            'recent' => 'Most recent',
            'name' => 'Name',
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
                'title' => 'No followers match this filter',
                'description' => 'Change the profile type or return to all followers.',
            ],
            'requests' => [
                'icon' => 'inbox',
                'title' => 'No requests need attention',
                'description' => 'Incoming and private-profile requests will appear here.',
            ],
            'recommendations' => [
                'icon' => 'sparkles',
                'title' => 'Recommendations are tuned',
                'description' => 'Change the type filter or restore a recently hidden suggestion.',
            ],
            default => [
                'icon' => 'user-check',
                'title' => 'No subscriptions match this filter',
                'description' => 'Follow a recommendation or change the selected profile type.',
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
            'Undo',
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
            'Undo block',
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
            'all' => 'All posts',
            'important' => 'Important only',
            'standard' => 'Standard',
            'feed' => 'Feed only',
            'off' => 'Paused',
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
            'endpoint' => route('pet-social.actions.perform'),
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
            str_starts_with($value, '2026-07-') => 'July 2026',
            str_starts_with($value, '2026-06-') => 'June 2026',
            default => 'recently',
        };
    }
}

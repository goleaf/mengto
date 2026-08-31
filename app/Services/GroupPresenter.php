<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class GroupPresenter
{
    public function __construct(
        private readonly GroupCatalog $catalog,
        private readonly GroupContentCatalog $content,
        private readonly GroupState $state,
        private readonly CreatedContentPresenter $created,
        private readonly LocaleFormatter $formatter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function directory(
        string $query = '',
        string $filter = 'recommended',
        string $sort = 'active',
    ): array {
        $query = trim($query);
        $filter = array_key_exists($filter, $this->filterOptions()) ? $filter : 'recommended';
        $sort = array_key_exists($sort, $this->sortOptions()) ? $sort : 'active';
        $groups = array_map(
            fn (array $group): array => $this->decorateDirectoryGroup(
                $group,
                $query,
                $filter,
                $sort,
            ),
            $this->catalog->all(),
        );
        $groups = array_values(array_filter(
            $groups,
            fn (array $group): bool => $this->matches($group, $query, $filter),
        ));
        usort($groups, fn (array $left, array $right): int => $this->compare($left, $right, $sort));
        $groups = [...$groups, ...$this->createdGroups($query, $filter)];
        $joinedCount = count(array_filter(
            $this->catalog->all(),
            fn (array $group): bool => $this->state->membership($group['key']) === 'joined',
        ));

        return [
            'page_title' => __('groups.directory.page_title'),
            'active_section' => 'groups',
            'summary' => [
                'eyebrow' => __('groups.directory.eyebrow'),
                'title' => __('groups.directory.title'),
                'description' => __('groups.directory.description'),
                'count' => trans_choice('presentation.groups_count', count($groups), ['count' => count($groups)]),
                'highlights' => [
                    ['label' => __('groups.directory.your_groups'), 'value' => (string) $joinedCount, 'detail' => __('groups.directory.joined_communities')],
                    ['label' => __('groups.directory.nearby'), 'value' => '5', 'detail' => __('groups.directory.portland_communities')],
                    ['label' => __('groups.directory.this_week'), 'value' => '563', 'detail' => __('groups.directory.posts_across_groups')],
                ],
            ],
            'groups' => [
                'items' => $groups,
                'query' => $query,
                'filter' => $filter,
                'sort' => $sort,
                'filters' => $this->labelledOptions($this->filterOptions()),
                'sort_options' => $this->sortOptions(),
                'browse_url' => route('groups.index'),
                'create_url' => route('compose', ['kind' => 'group']),
                'last_dismissed' => $this->lastDismissed($query, $filter, $sort),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $key, string $tab = 'overview'): ?array
    {
        $group = $this->catalog->find($key);

        if ($group === null) {
            return null;
        }

        $tab = array_key_exists($tab, $this->tabOptions()) ? $tab : 'overview';
        $membership = $this->state->membership($key);
        $canViewContent = $group['privacy'] === 'public' || $membership === 'joined';
        $content = $this->content->content($group);
        $content['poll'] = $this->decoratePoll($group, $content['poll'], $tab);

        return [
            'page_title' => __('presentation.brand_title', ['title' => $group['name']]),
            'active_section' => 'groups',
            'group' => [
                ...$group,
                'privacy_label' => $group['privacy'] === 'closed' ? __('groups.detail.privacy.closed') : __('groups.detail.privacy.public'),
                'privacy_icon' => $group['privacy'] === 'closed' ? 'lock-keyhole' : 'globe-2',
                'members' => trans_choice('presentation.members_count', $group['member_count'], [
                    'count' => $this->compactNumber($group['member_count']),
                ]),
                'pets' => trans_choice('presentation.pets_count', $group['pet_count'], [
                    'count' => $this->compactNumber($group['pet_count']),
                ]),
                'activity' => trans_choice('presentation.posts_this_week', $group['posts_week'], [
                    'count' => $group['posts_week'],
                ]),
                'membership' => $membership,
                'membership_label' => $this->membershipLabel($membership),
                'primary_action' => $this->membershipAction($group, $tab),
                'share_action' => [
                    'label' => __('groups.detail.actions.share'),
                    'icon' => 'send',
                    'variant' => 'paper',
                    'href' => route('share.show', ['target' => $key]),
                ],
                'report_action' => [
                    'label' => __('groups.detail.actions.report'),
                    'icon' => 'flag',
                    'variant' => 'quiet',
                    'href' => route('compose', [
                        'kind' => 'report-group',
                        'target' => $key,
                    ]),
                ],
                'stats' => [
                    ['label' => __('groups.detail.stats.members.label'), 'value' => $this->compactNumber($group['member_count']), 'detail' => __('groups.detail.stats.members.detail')],
                    ['label' => __('groups.detail.stats.pets.label'), 'value' => $this->compactNumber($group['pet_count']), 'detail' => __('groups.detail.stats.pets.detail')],
                    ['label' => __('groups.detail.stats.week.label'), 'value' => (string) $group['posts_week'], 'detail' => __('groups.detail.stats.week.detail')],
                    ['label' => __('groups.detail.stats.since.label'), 'value' => $group['started'], 'detail' => __('groups.detail.stats.since.detail')],
                ],
                'meta' => [
                    ['icon' => 'map-pin', 'label' => $group['location']],
                    ['icon' => $group['privacy'] === 'closed' ? 'lock-keyhole' : 'globe-2', 'label' => $group['privacy'] === 'closed' ? __('groups.detail.privacy.closed') : __('groups.detail.privacy.public')],
                    ['icon' => 'languages', 'label' => $group['language']],
                ],
            ],
            'active_tab' => $tab,
            'tabs' => $this->tabs($group, $tab),
            'can_view_content' => $canViewContent,
            'content' => $content,
            'membership' => [
                'status' => $membership,
                'label' => $this->membershipLabel($membership),
                'notification_level' => $this->state->notificationLevel($key),
                'notification_options' => $this->notificationOptions($group, $tab),
            ],
            'access_gate' => [
                'icon' => 'lock-keyhole',
                'title' => $membership === 'pending' ? __('groups.detail.access.pending_title') : __('groups.detail.access.join_title'),
                'description' => $membership === 'pending'
                    ? __('groups.detail.access.pending_description')
                    : __('groups.detail.access.join_description'),
                'action' => $this->membershipAction($group, $tab),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    private function decorateDirectoryGroup(
        array $group,
        string $query,
        string $filter,
        string $sort,
    ): array {
        $membership = $this->state->membership($group['key']);
        $detailUrl = route('groups.show', ['group' => $group['key']]);

        return [
            ...$group,
            'detail_route' => 'groups.show',
            'detail_parameters' => ['group' => $group['key']],
            'media_target' => [
                'url' => $detailUrl,
                'label' => __('presentation.open_group', ['name' => $group['name']]),
            ],
            'privacy_label' => $group['privacy'] === 'closed' ? __('groups.directory.privacy.closed') : __('groups.directory.privacy.public'),
            'privacy_icon' => $group['privacy'] === 'closed' ? 'lock-keyhole' : 'globe-2',
            'members' => trans_choice('presentation.members_count', $group['member_count'], [
                'count' => $this->compactNumber($group['member_count']),
            ]),
            'activity' => trans_choice('presentation.posts_this_week', $group['posts_week'], [
                'count' => $group['posts_week'],
            ]),
            'membership' => $membership,
            'joined' => $membership === 'joined',
            'primary_action' => $this->membershipAction(
                $group,
                'overview',
                ['group_return_q' => $query, 'group_return_filter' => $filter, 'group_return_sort' => $sort],
            ),
            'secondary_action' => $filter === 'recommended'
                ? $this->directoryAction(
                    'dismiss-group-recommendation',
                    $group['key'],
                    __('groups.directory.actions.hide_suggestion'),
                    'x',
                    $query,
                    $filter,
                    $sort,
                )
                : null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function createdGroups(string $query, string $filter): array
    {
        if (! in_array($filter, ['recommended', 'joined', 'local'], true)) {
            return [];
        }

        return array_values(array_filter(
            array_map(static function (array $group): array {
                $detailUrl = route($group['detail_route'], $group['detail_parameters']);

                return [
                    ...$group,
                    'privacy' => $group['privacy'] ?? 'public',
                    'privacy_label' => ($group['privacy'] ?? 'public') === 'closed'
                        ? __('groups.directory.privacy.closed')
                        : __('groups.directory.privacy.public'),
                    'privacy_icon' => ($group['privacy'] ?? 'public') === 'closed' ? 'lock-keyhole' : 'globe-2',
                    'official' => false,
                    'membership' => 'joined',
                    'joined' => true,
                    'recommendation_reason' => __('groups.directory.actions.created_by_you'),
                    'next_event' => null,
                    'media_target' => [
                        'url' => $detailUrl,
                        'label' => __('presentation.open_group', ['name' => $group['name']]),
                    ],
                    'primary_action' => [
                        'label' => __('groups.directory.actions.open_group'),
                        'icon' => 'arrow-up-right',
                        'variant' => 'paper',
                        'href' => $detailUrl,
                    ],
                    'secondary_action' => null,
                ];
            }, $this->created->groups()),
            static function (array $group) use ($query): bool {
                if ($query === '') {
                    return true;
                }

                return Str::contains(
                    Str::lower(implode(' ', [
                        $group['name'],
                        $group['category'],
                        $group['topic'],
                        $group['description'],
                    ])),
                    Str::lower($query),
                );
            },
        ));
    }

    /**
     * @param  array<string, mixed>  $group
     */
    private function matches(array $group, string $query, string $filter): bool
    {
        if ($filter === 'recommended' && $this->state->recommendationIsDismissed($group['key'])) {
            return false;
        }

        $filterMatches = match ($filter) {
            'joined' => $group['membership'] === 'joined',
            'local' => (bool) $group['local'],
            'breed' => $group['group_type'] === 'breed',
            'care' => in_array($group['group_type'], ['care', 'adoption', 'support'], true),
            'official' => (bool) $group['official'],
            default => true,
        };

        if (! $filterMatches || $query === '') {
            return $filterMatches;
        }

        $searchable = implode(' ', [
            $group['name'],
            $group['category'],
            $group['topic'],
            $group['description'],
            $group['organizer'],
            $group['location'],
            ...$group['tags'],
        ]);

        return Str::contains(Str::lower($searchable), Str::lower($query));
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    private function compare(array $left, array $right, string $sort): int
    {
        return match ($sort) {
            'members' => $right['member_count'] <=> $left['member_count'],
            'name' => strcasecmp($left['name'], $right['name']),
            default => $right['activity_score'] <=> $left['activity_score'],
        };
    }

    /**
     * @param  array<string, mixed>  $group
     * @param  array<string, string>  $returnState
     * @return array<string, mixed>
     */
    private function membershipAction(array $group, string $tab, array $returnState = []): array
    {
        $membership = $this->state->membership($group['key']);
        $action = match ($membership) {
            'joined' => ['leave-group', __('groups.directory.actions.joined'), 'check', 'paper'],
            'pending' => ['cancel-group-request', __('groups.directory.actions.cancel_request'), 'x', 'paper'],
            default => ['join-group', $group['privacy'] === 'closed' ? __('groups.directory.actions.request_to_join') : __('groups.directory.actions.join_group'), 'user-plus', 'primary'],
        };

        return [
            'label' => $action[1],
            'icon' => $action[2],
            'variant' => $action[3],
            'active' => $membership !== null,
            'pressed' => $membership === 'joined',
            'endpoint' => route('actions.perform'),
            'payload' => [
                'action' => $action[0],
                'target' => $group['key'],
                'group_return_tab' => $tab,
                ...$returnState,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, mixed>>
     */
    private function tabs(array $group, string $active): array
    {
        $counts = [
            'posts' => $group['posts_week'],
            'discussions' => 3,
            'events' => 2,
            'members' => $group['member_count'],
            'pets' => $group['pet_count'],
            'resources' => 4,
            'rules' => count($group['requirements']) + 3,
        ];
        $tabs = [];

        foreach ($this->tabOptions() as $value => $option) {
            $tabs[] = [
                ...$option,
                'active' => $value === $active,
                'count' => isset($counts[$value]) ? $this->compactNumber($counts[$value]) : null,
                'href' => route('groups.show', [
                    'group' => $group['key'],
                    'tab' => $value,
                ]),
            ];
        }

        return $tabs;
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, mixed>>
     */
    private function notificationOptions(array $group, string $tab): array
    {
        $active = $this->state->notificationLevel($group['key']);
        $options = [];

        foreach ([
            'all' => __('groups.detail.notifications.all'),
            'important' => __('groups.detail.notifications.important'),
            'events' => __('groups.detail.notifications.events'),
            'mentions' => __('groups.detail.notifications.mentions'),
            'digest' => __('groups.detail.notifications.digest'),
            'off' => __('groups.detail.notifications.off'),
        ] as $value => $label) {
            $options[] = [
                'label' => $label,
                'active' => $value === $active,
                'payload' => [
                    'action' => 'set-group-notifications',
                    'target' => $group['key'],
                    'group_notification_level' => $value,
                    'group_return_tab' => $tab,
                ],
            ];
        }

        return $options;
    }

    /**
     * @param  array<string, mixed>  $group
     * @param  array<string, mixed>  $poll
     * @return array<string, mixed>
     */
    private function decoratePoll(array $group, array $poll, string $tab): array
    {
        $selected = $this->state->pollVote($group['key'], $poll['key']);

        return [
            ...$poll,
            'selected' => $selected,
            'total' => array_sum(array_column($poll['options'], 'votes')),
            'options' => array_map(
                fn (array $option): array => [
                    ...$option,
                    'active' => $selected === $option['key'],
                    'payload' => [
                        'action' => 'vote-group-poll',
                        'target' => $group['key'],
                        'poll' => $poll['key'],
                        'poll_option' => $option['key'],
                        'group_return_tab' => $tab,
                    ],
                ],
                $poll['options'],
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lastDismissed(string $query, string $filter, string $sort): ?array
    {
        $target = $this->state->lastDismissed();
        $group = $target === null ? null : $this->catalog->find($target);

        if ($group === null) {
            return null;
        }

        return [
            'message' => __('presentation.hidden_from_recommendations', ['name' => $group['name']]),
            'action' => $this->directoryAction(
                'undo-group-recommendation',
                $target,
                __('groups.directory.actions.undo'),
                'undo-2',
                $query,
                $filter,
                $sort,
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function directoryAction(
        string $action,
        string $target,
        string $label,
        string $icon,
        string $query,
        string $filter,
        string $sort,
    ): array {
        return [
            'label' => $label,
            'icon' => $icon,
            'variant' => 'quiet',
            'endpoint' => route('actions.perform'),
            'payload' => [
                'action' => $action,
                'target' => $target,
                'group_return_q' => $query,
                'group_return_filter' => $filter,
                'group_return_sort' => $sort,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function filterOptions(): array
    {
        return [
            'recommended' => __('groups.directory.filters.recommended'),
            'joined' => __('groups.directory.filters.joined'),
            'local' => __('groups.directory.filters.local'),
            'breed' => __('groups.directory.filters.breed'),
            'care' => __('groups.directory.filters.care'),
            'official' => __('groups.directory.filters.official'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'active' => __('groups.directory.sort.active'),
            'members' => __('groups.directory.sort.members'),
            'name' => __('groups.directory.sort.name'),
        ];
    }

    /**
     * @param  array<string, string>  $options
     * @return list<array{value: string, label: string}>
     */
    private function labelledOptions(array $options): array
    {
        return array_map(
            static fn (string $label, string $value): array => [
                'value' => $value,
                'label' => $label,
            ],
            array_values($options),
            array_keys($options),
        );
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    private function tabOptions(): array
    {
        return [
            'overview' => ['label' => __('groups.detail.tabs.overview'), 'icon' => 'layout-dashboard'],
            'posts' => ['label' => __('groups.detail.tabs.posts'), 'icon' => 'newspaper'],
            'discussions' => ['label' => __('groups.detail.tabs.discussions'), 'icon' => 'messages-square'],
            'events' => ['label' => __('groups.detail.tabs.events'), 'icon' => 'calendar-days'],
            'members' => ['label' => __('groups.detail.tabs.members'), 'icon' => 'users'],
            'pets' => ['label' => __('groups.detail.tabs.pets'), 'icon' => 'paw-print'],
            'resources' => ['label' => __('groups.detail.tabs.resources'), 'icon' => 'library'],
            'rules' => ['label' => __('groups.detail.tabs.rules'), 'icon' => 'scroll-text'],
        ];
    }

    private function membershipLabel(?string $membership): string
    {
        return match ($membership) {
            'joined' => __('groups.detail.membership.member'),
            'pending' => __('groups.detail.membership.pending'),
            default => __('groups.detail.membership.none'),
        };
    }

    private function compactNumber(int $value): string
    {
        if ($value < 1000) {
            return $this->formatter->number($value);
        }

        return __('presentation.compact_thousands', [
            'count' => $this->formatter->number($value / 1000, 1),
        ]);
    }
}

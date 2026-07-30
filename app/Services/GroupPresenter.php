<?php

namespace App\Services;

use Illuminate\Support\Str;

final class GroupPresenter
{
    public function __construct(
        private readonly GroupCatalog $catalog,
        private readonly GroupContentCatalog $content,
        private readonly GroupState $state,
        private readonly ProfilePresenter $profiles,
        private readonly CreatedContentPresenter $created,
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
            'owner' => $this->profiles->owner(),
            'page_title' => 'Groups | PawCircle',
            'active_section' => 'groups',
            'summary' => [
                'eyebrow' => 'Communities with a purpose',
                'title' => 'Find your people and build something useful',
                'description' => 'Explore local, breed, care, adoption, and interest groups with clear privacy and moderation boundaries.',
                'count' => count($groups).' '.Str::plural('group', count($groups)),
                'highlights' => [
                    ['label' => 'Your groups', 'value' => (string) $joinedCount, 'detail' => 'joined communities'],
                    ['label' => 'Nearby', 'value' => '5', 'detail' => 'Portland communities'],
                    ['label' => 'This week', 'value' => '563', 'detail' => 'posts across groups'],
                ],
            ],
            'groups' => [
                'items' => $groups,
                'query' => $query,
                'filter' => $filter,
                'sort' => $sort,
                'filters' => array_values($this->filterOptions()),
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
            'owner' => $this->profiles->owner(),
            'page_title' => $group['name'].' | PawCircle',
            'active_section' => 'groups',
            'group' => [
                ...$group,
                'privacy_label' => $group['privacy'] === 'closed' ? 'Closed group' : 'Public group',
                'privacy_icon' => $group['privacy'] === 'closed' ? 'lock-keyhole' : 'globe-2',
                'members' => $this->compactNumber($group['member_count']).' members',
                'pets' => $this->compactNumber($group['pet_count']).' pets',
                'activity' => $group['posts_week'].' posts this week',
                'membership' => $membership,
                'membership_label' => $this->membershipLabel($membership),
                'primary_action' => $this->membershipAction($group, $tab),
                'share_action' => [
                    'label' => 'Share',
                    'icon' => 'send',
                    'variant' => 'paper',
                    'href' => route('share.show', ['target' => $key]),
                ],
                'report_action' => [
                    'label' => 'Report group',
                    'icon' => 'flag',
                    'variant' => 'quiet',
                    'href' => route('compose', [
                        'kind' => 'report-group',
                        'target' => $key,
                    ]),
                ],
                'stats' => [
                    ['label' => 'Members', 'value' => $this->compactNumber($group['member_count']), 'detail' => 'people in the community'],
                    ['label' => 'Pets', 'value' => $this->compactNumber($group['pet_count']), 'detail' => 'owner-managed profiles'],
                    ['label' => 'This week', 'value' => (string) $group['posts_week'], 'detail' => 'new posts'],
                    ['label' => 'Since', 'value' => $group['started'], 'detail' => 'community history'],
                ],
                'meta' => [
                    ['icon' => 'map-pin', 'label' => $group['location']],
                    ['icon' => $group['privacy'] === 'closed' ? 'lock-keyhole' : 'globe-2', 'label' => $group['privacy'] === 'closed' ? 'Closed group' : 'Public group'],
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
                'title' => $membership === 'pending' ? 'Your request is waiting for review' : 'Join to see member content',
                'description' => $membership === 'pending'
                    ? 'Moderators can review your profile and application. Group posts, members, files, and private event details stay closed meanwhile.'
                    : 'The group is discoverable, while posts, members, files, chats, and private event details remain visible only to approved members.',
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

        return [
            ...$group,
            'detail_route' => 'groups.show',
            'detail_parameters' => ['group' => $group['key']],
            'privacy_label' => $group['privacy'] === 'closed' ? 'Closed' : 'Public',
            'privacy_icon' => $group['privacy'] === 'closed' ? 'lock-keyhole' : 'globe-2',
            'members' => $this->compactNumber($group['member_count']).' members',
            'activity' => $group['posts_week'].' posts this week',
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
                    'Hide suggestion',
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
            array_map(static fn (array $group): array => [
                ...$group,
                'privacy' => $group['privacy'] ?? 'public',
                'privacy_label' => Str::headline($group['privacy'] ?? 'public'),
                'privacy_icon' => ($group['privacy'] ?? 'public') === 'closed' ? 'lock-keyhole' : 'globe-2',
                'official' => false,
                'membership' => 'joined',
                'joined' => true,
                'recommendation_reason' => 'Created by you',
                'next_event' => null,
                'primary_action' => [
                    'label' => 'Open group',
                    'icon' => 'arrow-up-right',
                    'variant' => 'paper',
                    'href' => route($group['detail_route'], $group['detail_parameters']),
                ],
                'secondary_action' => null,
            ], $this->created->groups()),
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
            'joined' => ['leave-group', 'Joined', 'check', 'paper'],
            'pending' => ['cancel-group-request', 'Cancel request', 'x', 'paper'],
            default => ['join-group', $group['privacy'] === 'closed' ? 'Request to join' : 'Join group', 'user-plus', 'primary'],
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
            'all' => 'All activity',
            'important' => 'Important only',
            'events' => 'Events',
            'mentions' => 'Mentions and replies',
            'digest' => 'Weekly digest',
            'off' => 'Off',
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
            'message' => $group['name'].' hidden from recommendations.',
            'action' => $this->directoryAction(
                'undo-group-recommendation',
                $target,
                'Undo',
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
            'recommended' => 'Recommended',
            'joined' => 'Joined',
            'local' => 'Local',
            'breed' => 'Breed',
            'care' => 'Care',
            'official' => 'Official',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'active' => 'Most active',
            'members' => 'Most members',
            'name' => 'Name',
        ];
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    private function tabOptions(): array
    {
        return [
            'overview' => ['label' => 'Overview', 'icon' => 'layout-dashboard'],
            'posts' => ['label' => 'Posts', 'icon' => 'newspaper'],
            'discussions' => ['label' => 'Discussions', 'icon' => 'messages-square'],
            'events' => ['label' => 'Events', 'icon' => 'calendar-days'],
            'members' => ['label' => 'Members', 'icon' => 'users'],
            'pets' => ['label' => 'Pets', 'icon' => 'paw-print'],
            'resources' => ['label' => 'Resources', 'icon' => 'library'],
            'rules' => ['label' => 'Rules', 'icon' => 'scroll-text'],
        ];
    }

    private function membershipLabel(?string $membership): string
    {
        return match ($membership) {
            'joined' => 'Member',
            'pending' => 'Request pending',
            default => 'Not a member',
        };
    }

    private function compactNumber(int $value): string
    {
        if ($value < 1000) {
            return number_format($value);
        }

        return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'k';
    }
}

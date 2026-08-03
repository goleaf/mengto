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
        private readonly ProfilePresenter $profiles,
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
            'owner' => $this->profiles->owner(),
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
            'owner' => $this->profiles->owner(),
            'page_title' => __('presentation.brand_title', ['title' => $group['name']]),
            'active_section' => 'groups',
            'group' => [
                ...$group,
                'privacy_label' => $group['privacy'] === 'closed' ? __('messages.closed_group_e1f1a48f09') : __('messages.public_group_b99668e88a'),
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
                    'label' => __('messages.share_29887a5ff9'),
                    'icon' => 'send',
                    'variant' => 'paper',
                    'href' => route('share.show', ['target' => $key]),
                ],
                'report_action' => [
                    'label' => __('messages.report_group_daa5c248b2'),
                    'icon' => 'flag',
                    'variant' => 'quiet',
                    'href' => route('compose', [
                        'kind' => 'report-group',
                        'target' => $key,
                    ]),
                ],
                'stats' => [
                    ['label' => __('messages.members_1044a4c056'), 'value' => $this->compactNumber($group['member_count']), 'detail' => __('messages.people_in_the_community_90c6b042ed')],
                    ['label' => __('messages.pets_7dc1cd7eaf'), 'value' => $this->compactNumber($group['pet_count']), 'detail' => __('messages.owner_managed_profiles_ec265b1755')],
                    ['label' => __('messages.this_week_8c4eef5ab2'), 'value' => (string) $group['posts_week'], 'detail' => __('messages.new_posts_297e9b6fbf')],
                    ['label' => __('messages.since_98af1ed618'), 'value' => $group['started'], 'detail' => __('messages.community_history_d83fb270c3')],
                ],
                'meta' => [
                    ['icon' => 'map-pin', 'label' => $group['location']],
                    ['icon' => $group['privacy'] === 'closed' ? 'lock-keyhole' : 'globe-2', 'label' => $group['privacy'] === 'closed' ? __('messages.closed_group_e1f1a48f09') : __('messages.public_group_b99668e88a')],
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
                'title' => $membership === 'pending' ? __('messages.your_request_is_waiting_for_review_e7ad75e893') : __('messages.join_to_see_member_content_f278c69ce0'),
                'description' => $membership === 'pending'
                    ? __('messages.moderators_can_review_your_profile_and_application_group_764a880f8f')
                    : __('messages.the_group_is_discoverable_while_posts_members_files_chat_db8bccc335'),
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
            'all' => __('messages.all_activity_29ebb2ef2d'),
            'important' => __('messages.important_only_c2c4224926'),
            'events' => __('messages.events_8d14f6e72d'),
            'mentions' => __('messages.mentions_and_replies_d2cae6302b'),
            'digest' => __('messages.weekly_digest_b134b14f1c'),
            'off' => __('messages.off_ca7981b46e'),
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
            'overview' => ['label' => __('messages.overview_d4b1ea5708'), 'icon' => 'layout-dashboard'],
            'posts' => ['label' => __('messages.posts_a80811cf68'), 'icon' => 'newspaper'],
            'discussions' => ['label' => __('messages.discussions_60157cfcfe'), 'icon' => 'messages-square'],
            'events' => ['label' => __('messages.events_8d14f6e72d'), 'icon' => 'calendar-days'],
            'members' => ['label' => __('messages.members_1044a4c056'), 'icon' => 'users'],
            'pets' => ['label' => __('messages.pets_7dc1cd7eaf'), 'icon' => 'paw-print'],
            'resources' => ['label' => __('messages.resources_e89b30aa1d'), 'icon' => 'library'],
            'rules' => ['label' => __('messages.rules_4228aeb07c'), 'icon' => 'scroll-text'],
        ];
    }

    private function membershipLabel(?string $membership): string
    {
        return match ($membership) {
            'joined' => __('messages.member_7c968fb71f'),
            'pending' => __('messages.request_pending_bc26ab4d4b'),
            default => __('messages.not_a_member_1099a75b03'),
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

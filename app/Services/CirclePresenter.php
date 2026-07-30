<?php

declare(strict_types=1);

namespace App\Services;

final class CirclePresenter
{
    private const FILTER_VALUES = ['overview', 'saved-posts', 'following', 'groups', 'meetups'];

    /**
     * @param  array<string, mixed>  $owner
     * @param  array<int, array<string, mixed>>  $posts
     * @param  array<int, array<string, mixed>>  $pets
     * @param  array<int, array<string, mixed>>  $neighbors
     * @param  array<int, array<string, mixed>>  $groups
     * @param  array<int, array<string, mixed>>  $meetups
     * @return array<string, mixed>
     */
    public function present(
        string $filter,
        array $owner,
        array $posts,
        array $pets,
        array $neighbors,
        array $groups,
        array $meetups,
    ): array {
        $activeFilter = $this->activeFilter($filter);
        $savedPosts = $this->activeItems($posts, 'saved');
        $following = [
            ...$this->entries('neighbor', $this->activeItems($neighbors, 'followed')),
            ...$this->entries('pet', $this->activeItems($pets, 'followed')),
        ];
        $joinedGroups = $this->activeItems($groups, 'joined');
        $rsvpMeetups = $this->activeItems($meetups, 'rsvp');
        $collections = $this->collections($savedPosts, $following, $joinedGroups, $rsvpMeetups);
        $total = count($savedPosts) + count($following) + count($joinedGroups) + count($rsvpMeetups);

        return [
            'owner' => $owner,
            'summary' => $this->summary($savedPosts, $following, $joinedGroups, $rsvpMeetups, $total),
            'filters' => $this->filters(),
            'activeFilter' => $activeFilter,
            'collections' => $this->visibleCollections($collections, $activeFilter),
            'showStarter' => $activeFilter === 'overview' && $total === 0,
            'starterItems' => $this->starterItems($posts, $neighbors, $meetups),
        ];
    }

    private function activeFilter(string $filter): string
    {
        return in_array($filter, self::FILTER_VALUES, true) ? $filter : 'overview';
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function filters(): array
    {
        return [
            ['value' => 'overview', 'label' => __('messages.overview_d4b1ea5708')],
            ['value' => 'saved-posts', 'label' => __('messages.saved_posts_c6171ac089')],
            ['value' => 'following', 'label' => __('messages.following_344b4271ca')],
            ['value' => 'groups', 'label' => __('messages.groups_39bbb719fa')],
            ['value' => 'meetups', 'label' => __('messages.meetups_ce225ab027')],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function activeItems(array $items, string $state): array
    {
        return array_values(array_filter(
            $items,
            static fn (array $item): bool => (bool) ($item[$state] ?? false),
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{type: string, data: array<string, mixed>}>
     */
    private function entries(string $type, array $items): array
    {
        return array_map(
            static fn (array $item): array => ['type' => $type, 'data' => $item],
            $items,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $savedPosts
     * @param  array<int, array{type: string, data: array<string, mixed>}>  $following
     * @param  array<int, array<string, mixed>>  $joinedGroups
     * @param  array<int, array<string, mixed>>  $rsvpMeetups
     * @return array<int, array<string, mixed>>
     */
    private function collections(
        array $savedPosts,
        array $following,
        array $joinedGroups,
        array $rsvpMeetups,
    ): array {
        return [
            $this->collection(
                key: 'saved-posts',
                eyebrow: __('messages.keep_for_later_8c4fffde3f'),
                title: __('messages.saved_moments_536af8f7ef'),
                items: $this->entries('post', $savedPosts),
                emptyIcon: 'bookmark',
                emptyTitle: __('messages.no_saved_moments_yet_8638ac0095'),
                emptyDescription: __('messages.save_useful_routines_local_recommendations_and_pet_updat_ae5e33da81'),
                actionRoute: 'home',
                actionLabel: __('messages.browse_the_feed_deef0dc0b4'),
                actionIcon: 'newspaper',
            ),
            $this->collection(
                key: 'following',
                eyebrow: __('messages.stay_connected_208e29928a'),
                title: __('messages.people_and_pets_you_follow_54fb2e1c40'),
                items: $following,
                emptyIcon: 'user-round-plus',
                emptyTitle: __('messages.your_following_list_is_open_a41b9bb72a'),
                emptyDescription: __('messages.follow_nearby_people_and_pets_to_keep_their_routines_eas_acd4269e04'),
                actionRoute: 'neighbors.index',
                actionLabel: __('messages.find_neighbors_af90a9d101'),
                actionIcon: 'users',
            ),
            $this->collection(
                key: 'groups',
                eyebrow: __('messages.shared_routines_36e841cb6c'),
                title: __('messages.joined_groups_527c48db8d'),
                items: $this->entries('group', $joinedGroups),
                emptyIcon: 'users-round',
                emptyTitle: __('messages.no_groups_joined_yet_c942287138'),
                emptyDescription: __('messages.join_a_focused_local_group_when_its_routines_match_life__bd313117ca'),
                actionRoute: 'groups.index',
                actionLabel: __('messages.explore_groups_aa9347c42f'),
                actionIcon: 'users-round',
            ),
            $this->collection(
                key: 'meetups',
                eyebrow: __('messages.on_your_calendar_06a50f1c20'),
                title: __('messages.meetups_you_are_attending_625c53f3f5'),
                items: $this->entries('meetup', $rsvpMeetups),
                emptyIcon: 'calendar-days',
                emptyTitle: __('messages.no_meetup_plans_yet_c0c8eadf52'),
                emptyDescription: __('messages.rsvp_to_a_nearby_walk_or_social_hour_and_it_will_stay_co_1ecd235e12'),
                actionRoute: 'meetups.index',
                actionLabel: __('messages.see_meetups_a15bc595fd'),
                actionIcon: 'calendar-days',
            ),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $collections
     * @return array<int, array<string, mixed>>
     */
    private function visibleCollections(array $collections, string $activeFilter): array
    {
        if ($activeFilter !== 'overview') {
            return array_values(array_filter(
                $collections,
                static fn (array $collection): bool => $collection['key'] === $activeFilter,
            ));
        }

        return array_values(array_filter(array_map(
            static fn (array $collection): array => [
                ...$collection,
                'items' => array_slice($collection['items'], 0, 2),
            ],
            $collections,
        ), static fn (array $collection): bool => $collection['items'] !== []));
    }

    /**
     * @param  array<int, array<string, mixed>>  $savedPosts
     * @param  array<int, array<string, mixed>>  $following
     * @param  array<int, array<string, mixed>>  $joinedGroups
     * @param  array<int, array<string, mixed>>  $rsvpMeetups
     * @return array<string, mixed>
     */
    private function summary(
        array $savedPosts,
        array $following,
        array $joinedGroups,
        array $rsvpMeetups,
        int $total,
    ): array {
        return [
            'eyebrow' => __('messages.your_pawcircle_0e89d31f86'),
            'title' => __('messages.everything_you_chose_to_keep_close_1b17f803d1'),
            'description' => __('messages.saved_moments_familiar_neighbors_joined_groups_and_meetu_a906de654b'),
            'count' => trans_choice('presentation.collected_items', $total, ['count' => $total]),
            'stats' => [
                ['label' => __('messages.saved_b5c120b316'), 'value' => (string) count($savedPosts), 'detail' => 'moments'],
                ['label' => __('messages.following_344b4271ca'), 'value' => (string) count($following), 'detail' => __('messages.people_and_pets_a74d68d13d')],
                ['label' => __('messages.groups_39bbb719fa'), 'value' => (string) count($joinedGroups), 'detail' => 'joined'],
                ['label' => __('messages.meetups_ce225ab027'), 'value' => (string) count($rsvpMeetups), 'detail' => 'going'],
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $posts
     * @param  array<int, array<string, mixed>>  $neighbors
     * @param  array<int, array<string, mixed>>  $meetups
     * @return array<int, array<string, mixed>>
     */
    private function starterItems(array $posts, array $neighbors, array $meetups): array
    {
        return [
            [
                'title' => __('presentation.pet_moments', ['pet' => $posts[0]['pet']]),
                'meta' => __('messages.neighborhood_feed_3f7d71b76a'),
                ...array_intersect_key($posts[0], array_flip(['image', 'image_small', 'image_medium', 'image_alt'])),
                'route' => 'home',
                'icon' => 'bookmark',
            ],
            [
                'title' => __('presentation.pet_pair', [
                    'person' => $neighbors[0]['name'],
                    'pet' => $neighbors[0]['pet'],
                ]),
                'meta' => $neighbors[0]['neighborhood'],
                ...array_intersect_key($neighbors[0], array_flip(['image', 'image_small', 'image_medium', 'image_alt'])),
                'route' => 'neighbors.index',
                'icon' => 'user-plus',
            ],
            [
                'title' => $meetups[0]['title'],
                'meta' => $meetups[0]['place'],
                ...array_intersect_key($meetups[0], array_flip(['image', 'image_small', 'image_medium', 'image_alt'])),
                'route' => 'meetups.index',
                'icon' => 'calendar-plus',
            ],
        ];
    }

    /**
     * @param  array<int, array{type: string, data: array<string, mixed>}>  $items
     * @return array<string, mixed>
     */
    private function collection(
        string $key,
        string $eyebrow,
        string $title,
        array $items,
        string $emptyIcon,
        string $emptyTitle,
        string $emptyDescription,
        string $actionRoute,
        string $actionLabel,
        string $actionIcon,
    ): array {
        return [
            'key' => $key,
            'eyebrow' => $eyebrow,
            'title' => $title,
            'items' => $items,
            'empty_icon' => $emptyIcon,
            'empty_title' => $emptyTitle,
            'empty_description' => $emptyDescription,
            'action_route' => $actionRoute,
            'action_label' => $actionLabel,
            'action_icon' => $actionIcon,
        ];
    }
}

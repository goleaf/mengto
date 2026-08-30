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
            ['value' => 'overview', 'label' => __('messages.overview')],
            ['value' => 'saved-posts', 'label' => __('messages.saved_posts')],
            ['value' => 'following', 'label' => __('messages.following')],
            ['value' => 'groups', 'label' => __('messages.groups')],
            ['value' => 'meetups', 'label' => __('messages.meetups')],
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
                eyebrow: __('messages.keep_for_later'),
                title: __('messages.saved_moments'),
                items: $this->entries('post', $savedPosts),
                emptyIcon: 'bookmark',
                emptyTitle: __('messages.no_saved_moments_yet'),
                emptyDescription: __('messages.save_useful_routines_local_recommendations_and_pet_updates_from_the_feed'),
                actionRoute: 'preview.feed',
                actionLabel: __('messages.browse_the_feed'),
                actionIcon: 'newspaper',
            ),
            $this->collection(
                key: 'following',
                eyebrow: __('messages.stay_connected'),
                title: __('messages.people_and_pets_you_follow'),
                items: $following,
                emptyIcon: 'user-round-plus',
                emptyTitle: __('messages.your_following_list_is_open'),
                emptyDescription: __('messages.follow_nearby_people_and_pets_to_keep_their_routines_easy_to_find'),
                actionRoute: 'neighbors.index',
                actionLabel: __('messages.find_neighbors'),
                actionIcon: 'users',
            ),
            $this->collection(
                key: 'groups',
                eyebrow: __('messages.shared_routines'),
                title: __('messages.joined_groups'),
                items: $this->entries('group', $joinedGroups),
                emptyIcon: 'users-round',
                emptyTitle: __('messages.no_groups_joined_yet'),
                emptyDescription: __('messages.join_a_focused_local_group_when_its_routines_match_life_with_your_pets'),
                actionRoute: 'groups.index',
                actionLabel: __('messages.explore_groups'),
                actionIcon: 'users-round',
            ),
            $this->collection(
                key: 'meetups',
                eyebrow: __('messages.on_your_calendar'),
                title: __('messages.meetups_you_are_attending'),
                items: $this->entries('meetup', $rsvpMeetups),
                emptyIcon: 'calendar-days',
                emptyTitle: __('messages.no_meetup_plans_yet'),
                emptyDescription: __('messages.rsvp_to_a_nearby_walk_or_social_hour_and_it_will_stay_collected_here'),
                actionRoute: 'meetups.index',
                actionLabel: __('messages.see_meetups'),
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
            'eyebrow' => __('messages.your_brand'),
            'title' => __('messages.everything_you_chose_to_keep_close'),
            'description' => __('messages.saved_moments_familiar_neighbors_joined_groups_and_meetup_plans_stay_together_without_adding_noise_to_the_main_feed'),
            'count' => trans_choice('presentation.collected_items', $total, ['count' => $total]),
            'stats' => [
                ['label' => __('messages.saved'), 'value' => (string) count($savedPosts), 'detail' => 'moments'],
                ['label' => __('messages.following'), 'value' => (string) count($following), 'detail' => __('messages.people_and_pets')],
                ['label' => __('messages.groups'), 'value' => (string) count($joinedGroups), 'detail' => 'joined'],
                ['label' => __('messages.meetups'), 'value' => (string) count($rsvpMeetups), 'detail' => 'going'],
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
                'meta' => __('messages.neighborhood_feed'),
                ...array_intersect_key($posts[0], array_flip(['image', 'image_small', 'image_medium', 'image_alt'])),
                'route' => 'preview.feed',
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

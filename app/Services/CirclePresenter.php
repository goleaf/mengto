<?php

namespace App\Services;

use Illuminate\Support\Str;

final class CirclePresenter
{
    private const FILTERS = ['Overview', 'Saved posts', 'Following', 'Groups', 'Meetups'];

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
            'filters' => self::FILTERS,
            'activeFilter' => $activeFilter,
            'collections' => $this->visibleCollections($collections, $activeFilter),
            'showStarter' => $activeFilter === 'overview' && $total === 0,
            'starterItems' => $this->starterItems($posts, $neighbors, $meetups),
        ];
    }

    private function activeFilter(string $filter): string
    {
        $allowed = array_map(
            static fn (string $label): string => Str::slug($label),
            self::FILTERS,
        );

        return in_array($filter, $allowed, true) ? $filter : 'overview';
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
                eyebrow: 'Keep for later',
                title: 'Saved moments',
                items: $this->entries('post', $savedPosts),
                emptyIcon: 'bookmark',
                emptyTitle: 'No saved moments yet',
                emptyDescription: 'Save useful routines, local recommendations, and pet updates from the feed.',
                actionRoute: 'home',
                actionLabel: 'Browse the feed',
                actionIcon: 'newspaper',
            ),
            $this->collection(
                key: 'following',
                eyebrow: 'Stay connected',
                title: 'People and pets you follow',
                items: $following,
                emptyIcon: 'user-round-plus',
                emptyTitle: 'Your following list is open',
                emptyDescription: 'Follow nearby people and pets to keep their routines easy to find.',
                actionRoute: 'neighbors.index',
                actionLabel: 'Find neighbors',
                actionIcon: 'users',
            ),
            $this->collection(
                key: 'groups',
                eyebrow: 'Shared routines',
                title: 'Joined groups',
                items: $this->entries('group', $joinedGroups),
                emptyIcon: 'users-round',
                emptyTitle: 'No groups joined yet',
                emptyDescription: 'Join a focused local group when its routines match life with your pets.',
                actionRoute: 'groups.index',
                actionLabel: 'Explore groups',
                actionIcon: 'users-round',
            ),
            $this->collection(
                key: 'meetups',
                eyebrow: 'On your calendar',
                title: 'Meetups you are attending',
                items: $this->entries('meetup', $rsvpMeetups),
                emptyIcon: 'calendar-days',
                emptyTitle: 'No meetup plans yet',
                emptyDescription: 'RSVP to a nearby walk or social hour and it will stay collected here.',
                actionRoute: 'meetups.index',
                actionLabel: 'See meetups',
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
            'eyebrow' => 'Your PawCircle',
            'title' => 'Everything you chose to keep close',
            'description' => 'Saved moments, familiar neighbors, joined groups, and meetup plans stay together without adding noise to the main feed.',
            'count' => $total.' '.Str::plural('item', $total).' collected',
            'stats' => [
                ['label' => 'Saved', 'value' => (string) count($savedPosts), 'detail' => 'moments'],
                ['label' => 'Following', 'value' => (string) count($following), 'detail' => 'people and pets'],
                ['label' => 'Groups', 'value' => (string) count($joinedGroups), 'detail' => 'joined'],
                ['label' => 'Meetups', 'value' => (string) count($rsvpMeetups), 'detail' => 'going'],
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
                'title' => $posts[0]['pet'].' moments',
                'meta' => 'Neighborhood feed',
                ...array_intersect_key($posts[0], array_flip(['image', 'image_small', 'image_medium', 'image_alt'])),
                'route' => 'home',
                'icon' => 'bookmark',
            ],
            [
                'title' => $neighbors[0]['name'].' and '.$neighbors[0]['pet'],
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

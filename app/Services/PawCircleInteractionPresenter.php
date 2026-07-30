<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PawCircleInteractionPresenter
{
    public function __construct(private readonly PawCirclePrototypeState $state) {}

    /**
     * @param  array<int, array<string, mixed>>  $posts
     * @return array<int, array<string, mixed>>
     */
    public function posts(array $posts): array
    {
        return array_map(function (array $post): array {
            $key = $post['key'] ?? Str::slug(implode('-', [
                $post['author'],
                $post['pet'],
                $post['datetime'],
            ]));
            $replyCount = (int) ($post['stats']['replies'] ?? 0) + count($this->state->comments($key));

            return [
                ...$post,
                'key' => $key,
                'stats' => [
                    ...$post['stats'],
                    'replies' => (string) $replyCount,
                ],
                'pawed' => $this->state->isActive('paws', $key),
                'saved' => $this->state->isActive('saved', $key),
            ];
        }, $posts);
    }

    /**
     * @param  array<int, array<string, mixed>>  $pets
     * @return array<int, array<string, mixed>>
     */
    public function pets(array $pets): array
    {
        return array_map(function (array $pet): array {
            $key = $pet['key'] ?? Str::slug((string) $pet['name']);
            $profile = $key === 'scout'
                ? array_intersect_key($this->state->pet(), $pet)
                : [];

            return [
                ...$pet,
                ...array_filter($profile, static fn (string $value): bool => $value !== ''),
                'key' => $key,
                'followed' => $this->state->isActive('follows', $key),
            ];
        }, $pets);
    }

    /**
     * @param  array<int, array<string, mixed>>  $meetups
     * @return array<int, array<string, mixed>>
     */
    public function meetups(array $meetups): array
    {
        return array_map(function (array $meetup): array {
            $key = $meetup['key'] ?? Str::slug((string) $meetup['title']);

            return [
                ...$meetup,
                'key' => $key,
                'rsvp' => $this->state->isActive('meetups', $key),
            ];
        }, $meetups);
    }

    /**
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<int, array<string, mixed>>
     */
    public function groups(array $groups): array
    {
        return array_map(function (array $group): array {
            $key = $group['key'] ?? Str::slug((string) $group['name']);

            return [
                ...$group,
                'key' => $key,
                'joined' => $this->state->isActive('groups', $key),
            ];
        }, $groups);
    }

    /**
     * @param  array<int, array<string, mixed>>  $neighbors
     * @return array<int, array<string, mixed>>
     */
    public function neighbors(array $neighbors): array
    {
        return array_map(function (array $neighbor): array {
            $key = $neighbor['key'] ?? Str::slug((string) $neighbor['name']);

            return [
                ...$neighbor,
                'key' => $key,
                'followed' => $this->state->isActive('follows', $key),
            ];
        }, $neighbors);
    }
}

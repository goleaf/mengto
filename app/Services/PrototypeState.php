<?php

declare(strict_types=1);

namespace App\Services;

class PrototypeState
{
    private const STATE_NAMESPACE = 'prototype.state.v1';

    public function __construct(private readonly PersistentStateStore $states) {}

    public function isActive(string $collection, string $target): bool
    {
        return in_array($target, $this->state()['toggles'][$collection] ?? [], true);
    }

    public function toggle(string $collection, string $target): bool
    {
        $state = $this->state();
        $activeTargets = $state['toggles'][$collection] ?? [];

        if (in_array($target, $activeTargets, true)) {
            $activeTargets = array_values(array_filter(
                $activeTargets,
                static fn (string $activeTarget): bool => $activeTarget !== $target,
            ));
            $isActive = false;
        } else {
            $activeTargets[] = $target;
            $activeTargets = array_values(array_unique($activeTargets));
            $isActive = true;
        }

        $state['toggles'][$collection] = $activeTargets;
        $this->store($state);

        return $isActive;
    }

    /**
     * @param  array<string, string|bool>  $comment
     */
    public function addComment(array $comment): void
    {
        $state = $this->state();
        $state['comments'] ??= [];
        $state['comments'][] = $comment;
        $state['comments'] = array_slice($state['comments'], -40);

        $this->store($state);
    }

    /**
     * @return array<int, array<string, string|bool>>
     */
    public function comments(string $post): array
    {
        return array_values(array_filter(
            $this->state()['comments'] ?? [],
            static fn (array $comment): bool => ($comment['post'] ?? '') === $post,
        ));
    }

    /**
     * @param  array<string, string>  $post
     */
    public function addPost(array $post): void
    {
        $state = $this->state();
        $state['posts'] ??= [];
        array_unshift($state['posts'], $post);
        $state['posts'] = array_slice($state['posts'], 0, 24);

        $this->store($state);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function posts(): array
    {
        return $this->state()['posts'] ?? [];
    }

    /**
     * @return array<string, string>|null
     */
    public function post(string $key): ?array
    {
        foreach ($this->posts() as $post) {
            if (($post['key'] ?? '') === $key) {
                return $post;
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $values
     */
    public function updatePost(string $key, array $values): bool
    {
        $state = $this->state();
        $state['posts'] ??= [];

        foreach ($state['posts'] as $index => $post) {
            if (($post['key'] ?? '') !== $key) {
                continue;
            }

            $state['posts'][$index] = [
                ...$post,
                ...$values,
                'updated_at' => now()->toAtomString(),
            ];
            $this->store($state);

            return true;
        }

        return false;
    }

    public function movePost(string $key, string $status): bool
    {
        return $this->updatePost($key, ['status' => $status]);
    }

    public function deletePost(string $key): bool
    {
        $state = $this->state();
        $posts = $state['posts'] ?? [];
        $remaining = array_values(array_filter(
            $posts,
            static fn (array $post): bool => ($post['key'] ?? '') !== $key,
        ));

        if (count($remaining) === count($posts)) {
            return false;
        }

        $state['posts'] = $remaining;
        $this->store($state);

        return true;
    }

    public function setReaction(string $post, string $reaction): ?string
    {
        $state = $this->state();
        $state['reactions'] ??= [];
        $current = $state['reactions'][$post] ?? null;

        if ($current === $reaction) {
            unset($state['reactions'][$post]);
            $selected = null;
        } else {
            $state['reactions'][$post] = $reaction;
            $selected = $reaction;
        }

        $this->store($state);

        return $selected;
    }

    public function reaction(string $post): ?string
    {
        return $this->state()['reactions'][$post] ?? null;
    }

    /**
     * @param  array<string, string>  $report
     */
    public function addPostReport(array $report): void
    {
        $state = $this->state();
        $state['post_reports'] ??= [];
        array_unshift($state['post_reports'], $report);
        $state['post_reports'] = array_slice($state['post_reports'], 0, 20);

        $this->store($state);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function postReports(): array
    {
        return $this->state()['post_reports'] ?? [];
    }

    public function markNotificationsRead(): void
    {
        $state = $this->state();
        $state['notifications_read'] = true;

        $this->store($state);
    }

    public function notificationsAreRead(): bool
    {
        return $this->state()['notifications_read'];
    }

    public function markConversationRead(string $conversation): void
    {
        $state = $this->state();
        $state['read_conversations'] ??= [];
        $state['read_conversations'][] = $conversation;
        $state['read_conversations'] = array_values(array_unique($state['read_conversations']));

        $this->store($state);
    }

    public function conversationIsRead(string $conversation): bool
    {
        return in_array($conversation, $this->state()['read_conversations'] ?? [], true);
    }

    public function toggleSetting(string $setting): bool
    {
        $state = $this->state();
        $enabled = ! ($state['settings'][$setting] ?? false);
        $state['settings'][$setting] = $enabled;

        $this->store($state);

        return $enabled;
    }

    /**
     * @param  array<string, bool>  $defaults
     * @return array<string, bool>
     */
    public function settings(array $defaults): array
    {
        $state = $this->state();
        $settings = [...$defaults, ...$state['settings']];

        if ($settings !== $state['settings']) {
            $state['settings'] = $settings;
            $this->store($state);
        }

        return $settings;
    }

    /**
     * @param  array{target: string, reason: string, body: string, created_at: string}  $report
     */
    public function addProfileReport(array $report): void
    {
        $state = $this->state();
        $state['profile_reports'] ??= [];
        array_unshift($state['profile_reports'], $report);
        $state['profile_reports'] = array_slice($state['profile_reports'], 0, 20);

        $this->store($state);
    }

    /**
     * @return array<int, array{target: string, reason: string, body: string, created_at: string}>
     */
    public function profileReports(): array
    {
        return $this->state()['profile_reports'] ?? [];
    }

    /**
     * @param  array<string, string>  $item
     */
    public function addCreated(string $collection, array $item): void
    {
        $state = $this->state();
        array_unshift($state['created'][$collection], $item);
        $state['created'][$collection] = array_slice($state['created'][$collection], 0, 8);

        $this->store($state);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function created(string $collection): array
    {
        return $this->state()['created'][$collection] ?? [];
    }

    /**
     * @return array<string, array{
     *     notification_level: string,
     *     favorite: bool,
     *     muted: bool,
     *     followed_at: string
     * }>
     */
    public function subscriptions(): array
    {
        return $this->state()['subscriptions'] ?? [];
    }

    /**
     * @return array{notification_level: string, favorite: bool, muted: bool, followed_at: string}|null
     */
    public function subscription(string $target): ?array
    {
        return $this->subscriptions()[$target] ?? null;
    }

    public function isSubscribed(string $target): bool
    {
        return $this->subscription($target) !== null;
    }

    public function toggleSubscription(string $target): bool
    {
        $state = $this->state();
        $state['subscriptions'] = $this->subscriptions();

        if (isset($state['subscriptions'][$target])) {
            unset($state['subscriptions'][$target]);
            $following = false;
        } else {
            $state['subscriptions'][$target] = [
                'notification_level' => 'standard',
                'favorite' => false,
                'muted' => false,
                'followed_at' => now()->toDateString(),
            ];
            $following = true;
        }

        unset($state['outgoing_follow_requests'][$target]);
        $this->store($state);

        return $following;
    }

    public function toggleSubscriptionFlag(string $target, string $flag): ?bool
    {
        if (! in_array($flag, ['favorite', 'muted'], true)) {
            return null;
        }

        $state = $this->state();
        $state['subscriptions'] = $this->subscriptions();

        if (! isset($state['subscriptions'][$target])) {
            return null;
        }

        $enabled = ! (bool) $state['subscriptions'][$target][$flag];
        $state['subscriptions'][$target][$flag] = $enabled;
        $this->store($state);

        return $enabled;
    }

    public function setSubscriptionNotificationLevel(string $target, string $level): bool
    {
        $state = $this->state();
        $state['subscriptions'] = $this->subscriptions();

        if (! isset($state['subscriptions'][$target])) {
            return false;
        }

        $state['subscriptions'][$target]['notification_level'] = $level;
        $this->store($state);

        return true;
    }

    /**
     * @return array<string, string>
     */
    public function outgoingFollowRequests(): array
    {
        return $this->state()['outgoing_follow_requests'] ?? [];
    }

    public function outgoingFollowRequestStatus(string $target): ?string
    {
        return $this->outgoingFollowRequests()[$target] ?? null;
    }

    public function toggleOutgoingFollowRequest(string $target): bool
    {
        $state = $this->state();
        $state['outgoing_follow_requests'] ??= [];

        if (($state['outgoing_follow_requests'][$target] ?? null) === 'pending') {
            unset($state['outgoing_follow_requests'][$target]);
            $pending = false;
        } else {
            $state['outgoing_follow_requests'][$target] = 'pending';
            $pending = true;
        }

        $this->store($state);

        return $pending;
    }

    /**
     * @return array<string, string>
     */
    public function incomingFollowRequests(): array
    {
        return $this->state()['incoming_follow_requests'] ?? [];
    }

    public function incomingFollowRequestStatus(string $target): ?string
    {
        return $this->incomingFollowRequests()[$target] ?? null;
    }

    public function resolveIncomingFollowRequest(string $target, string $status): bool
    {
        if (! in_array($status, ['accepted', 'declined'], true)) {
            return false;
        }

        $state = $this->state();
        $state['incoming_follow_requests'] = $this->incomingFollowRequests();

        if (($state['incoming_follow_requests'][$target] ?? null) !== 'pending') {
            return false;
        }

        $state['incoming_follow_requests'][$target] = $status;
        $this->store($state);

        return true;
    }

    /**
     * @return array<int, string>
     */
    public function acceptedFollowerTargets(): array
    {
        return array_keys(array_filter(
            $this->incomingFollowRequests(),
            static fn (string $status): bool => $status === 'accepted',
        ));
    }

    public function removeFollower(string $target): void
    {
        $state = $this->state();
        $state['removed_followers'] ??= [];
        $state['removed_followers'][] = $target;
        $state['removed_followers'] = array_values(array_unique($state['removed_followers']));

        $this->store($state);
    }

    public function followerIsRemoved(string $target): bool
    {
        return in_array($target, $this->state()['removed_followers'] ?? [], true);
    }

    public function dismissRecommendation(string $target): void
    {
        $state = $this->state();
        $state['dismissed_recommendations'] ??= [];
        $state['dismissed_recommendations'][] = $target;
        $state['dismissed_recommendations'] = array_values(array_unique($state['dismissed_recommendations']));
        $state['last_dismissed_recommendation'] = $target;

        $this->store($state);
    }

    public function recommendationIsDismissed(string $target): bool
    {
        return in_array($target, $this->state()['dismissed_recommendations'] ?? [], true);
    }

    public function lastDismissedRecommendation(): ?string
    {
        return $this->state()['last_dismissed_recommendation'] ?? null;
    }

    public function undoRecommendationDismissal(?string $target = null): ?string
    {
        $state = $this->state();
        $target ??= $state['last_dismissed_recommendation'] ?? null;

        if ($target === null) {
            return null;
        }

        $state['dismissed_recommendations'] = array_values(array_filter(
            $state['dismissed_recommendations'] ?? [],
            static fn (string $dismissed): bool => $dismissed !== $target,
        ));
        $state['last_dismissed_recommendation'] = null;
        $this->store($state);

        return $target;
    }

    public function toggleConnectionBlock(string $target): bool
    {
        $blocked = $this->toggle('blocks', $target);
        $state = $this->state();
        $state['last_blocked_connection'] = $blocked ? $target : null;
        $this->store($state);

        return $blocked;
    }

    public function lastBlockedConnection(): ?string
    {
        $target = $this->state()['last_blocked_connection'] ?? null;

        if ($target === null || ! $this->isActive('blocks', $target)) {
            return null;
        }

        return $target;
    }

    /**
     * @return array{
     *     toggles: array<string, array<int, string>>,
     *     comments: array<int, array<string, string|bool>>,
     *     notifications_read: bool,
     *     read_conversations: array<int, string>,
     *     settings: array<string, bool>,
     *     profile_reports: array<int, array{target: string, reason: string, body: string, created_at: string}>,
     *     post_reports: array<int, array<string, string>>,
     *     posts: array<int, array<string, string>>,
     *     reactions: array<string, string>,
     *     created: array<string, array<int, array<string, string>>>,
     *     subscriptions?: array<string, array{notification_level: string, favorite: bool, muted: bool, followed_at: string}>,
     *     outgoing_follow_requests?: array<string, string>,
     *     incoming_follow_requests?: array<string, string>,
     *     removed_followers?: array<int, string>,
     *     dismissed_recommendations?: array<int, string>,
     *     last_dismissed_recommendation?: string|null,
     *     last_blocked_connection?: string|null
     * }
     */
    private function state(): array
    {
        return $this->states->get(self::STATE_NAMESPACE, [
            'toggles' => [],
            'comments' => [],
            'notifications_read' => false,
            'read_conversations' => [],
            'settings' => [],
            'profile_reports' => [],
            'post_reports' => [],
            'posts' => [],
            'reactions' => [],
            'subscriptions' => [],
            'outgoing_follow_requests' => [],
            'incoming_follow_requests' => [],
            'removed_followers' => [],
            'dismissed_recommendations' => [],
            'last_dismissed_recommendation' => null,
            'last_blocked_connection' => null,
            'created' => [
                'posts' => [],
                'groups' => [],
                'meetups' => [],
                'pets' => [],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function store(array $state): void
    {
        $this->states->put(self::STATE_NAMESPACE, $state);
    }
}

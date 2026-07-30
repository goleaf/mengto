<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\ConnectionPresenter;
use App\Services\PrototypeState;
use Illuminate\Validation\ValidationException;

final class PerformConnectionAction
{
    private const ACTIONS = [
        'toggle-subscription',
        'toggle-follow-request',
        'toggle-subscription-favorite',
        'toggle-subscription-mute',
        'toggle-connection-block',
        'set-subscription-notifications',
        'dismiss-recommendation',
        'undo-recommendation-dismissal',
        'remove-follower',
        'accept-follow-request',
        'decline-follow-request',
    ];

    public function __construct(
        private readonly PrototypeState $state,
        private readonly ConnectionPresenter $connections,
    ) {}

    public function supports(string $action): bool
    {
        return in_array($action, self::ACTIONS, true);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    public function handle(array $data): array
    {
        return match ((string) $data['action']) {
            'toggle-subscription' => $this->toggleSubscription($data),
            'toggle-follow-request' => $this->toggleFollowRequest($data),
            'toggle-subscription-favorite' => $this->toggleSubscriptionFlag($data, 'favorite'),
            'toggle-subscription-mute' => $this->toggleSubscriptionFlag($data, 'muted'),
            'toggle-connection-block' => $this->toggleConnectionBlock($data),
            'set-subscription-notifications' => $this->setSubscriptionNotifications($data),
            'dismiss-recommendation' => $this->dismissRecommendation($data),
            'undo-recommendation-dismissal' => $this->undoRecommendationDismissal($data),
            'remove-follower' => $this->removeFollower($data),
            'accept-follow-request' => $this->resolveFollowRequest($data, 'accepted'),
            'decline-follow-request' => $this->resolveFollowRequest($data, 'declined'),
            default => throw ValidationException::withMessages([
                'action' => __('messages.this_action_is_unavailable_c64fa3888d'),
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function toggleSubscription(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $connection = $this->requireConnection($target);

        if ($connection['private']) {
            throw ValidationException::withMessages([
                'target' => __('messages.this_private_profile_requires_a_follow_request_1bb9cc4b19'),
            ]);
        }

        $following = $this->state->toggleSubscription($target);

        return $this->connectionResult(
            $following
                ? __('messages.connection_following', ['name' => $connection['name']])
                : __('messages.connection_no_longer_following', ['name' => $connection['name']]),
            $data,
            $following ? 'following' : 'recommendations',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function toggleFollowRequest(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $connection = $this->requireConnection($target);

        if (! $connection['private']) {
            throw ValidationException::withMessages([
                'target' => __('messages.this_public_profile_can_be_followed_immediately_67a8ce1ee8'),
            ]);
        }

        $pending = $this->state->toggleOutgoingFollowRequest($target);

        return $this->connectionResult(
            $pending
                ? __('messages.connection_follow_request_sent', ['name' => $connection['name']])
                : __('messages.connection_follow_request_cancelled', ['name' => $connection['name']]),
            $data,
            $pending ? 'requests' : 'recommendations',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function toggleSubscriptionFlag(array $data, string $flag): array
    {
        $target = (string) ($data['target'] ?? '');
        $connection = $this->requireConnection($target);
        $enabled = $this->state->toggleSubscriptionFlag($target, $flag);

        if ($enabled === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.follow_this_profile_before_changing_its_settings_d191be8926'),
            ]);
        }

        $message = match ($flag) {
            'favorite' => $enabled
                ? __('messages.connection_added_to_favorites', ['name' => $connection['name']])
                : __('messages.connection_removed_from_favorites', ['name' => $connection['name']]),
            default => $enabled
                ? __('messages.connection_muted_in_feed', ['name' => $connection['name']])
                : __('messages.connection_restored_to_feed', ['name' => $connection['name']]),
        };

        return $this->connectionResult($message, $data, 'following');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function toggleConnectionBlock(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $connection = $this->requireConnection($target);
        $blocked = $this->state->toggleConnectionBlock($target);

        return $this->connectionResult(
            $blocked
                ? __('messages.connection_blocked', ['name' => $connection['name']])
                : __('messages.connection_unblocked', ['name' => $connection['name']]),
            $data,
            'following',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function setSubscriptionNotifications(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $level = (string) ($data['notification_level'] ?? '');
        $connection = $this->requireConnection($target);

        if (! $this->state->setSubscriptionNotificationLevel($target, $level)) {
            throw ValidationException::withMessages([
                'target' => __('messages.follow_this_profile_before_changing_notifications_86c14f1dad'),
            ]);
        }

        $labels = [
            'all' => __('messages.notification_level_all_publications'),
            'important' => __('messages.notification_level_important_updates'),
            'standard' => __('messages.notification_level_standard_updates'),
            'feed' => __('messages.notification_level_feed_only'),
            'off' => __('messages.notification_level_paused'),
        ];

        return $this->connectionResult(
            __('messages.connection_notifications_updated', [
                'name' => $connection['name'],
                'level' => $labels[$level],
            ]),
            $data,
            'following',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function dismissRecommendation(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $connection = $this->requireConnection($target);

        if (! $this->connections->isRecommendation($target)) {
            throw ValidationException::withMessages(['target' => __('messages.this_recommendation_is_unavailable_b4a79291ab')]);
        }

        $this->state->dismissRecommendation($target);

        return $this->connectionResult(
            __('messages.connection_recommendation_removed', ['name' => $connection['name']]),
            $data,
            'recommendations',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function undoRecommendationDismissal(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $connection = $this->requireConnection($target);

        if ($this->state->undoRecommendationDismissal($target) === null) {
            throw ValidationException::withMessages(['target' => __('messages.there_is_no_recommendation_to_restore_103e62ec23')]);
        }

        return $this->connectionResult(
            __('messages.connection_recommendation_restored', ['name' => $connection['name']]),
            $data,
            'recommendations',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function removeFollower(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $connection = $this->requireConnection($target);

        if (! $this->connections->isFollower($target)) {
            throw ValidationException::withMessages(['target' => __('messages.this_follower_is_unavailable_d4adefcbc7')]);
        }

        $this->state->removeFollower($target);

        return $this->connectionResult(
            __('messages.connection_follower_removed', ['name' => $connection['name']]),
            $data,
            'followers',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function resolveFollowRequest(array $data, string $status): array
    {
        $target = (string) ($data['target'] ?? '');
        $connection = $this->requireConnection($target);

        if (
            ! $this->connections->isIncomingRequest($target)
            || ! $this->state->resolveIncomingFollowRequest($target, $status)
        ) {
            throw ValidationException::withMessages(['target' => __('messages.this_follow_request_is_unavailable_0db6453cf7')]);
        }

        return $this->connectionResult(
            $status === 'accepted'
                ? __('messages.connection_follow_request_accepted', ['name' => $connection['name']])
                : __('messages.connection_follow_request_declined', ['name' => $connection['name']]),
            $data,
            'requests',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function requireConnection(string $target): array
    {
        $this->requireTarget($target);
        $connection = $this->connections->target($target);

        if ($connection === null) {
            throw ValidationException::withMessages(['target' => __('messages.this_profile_or_interest_is_unavailable_3e0039cca7')]);
        }

        return $connection;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function connectionResult(string $message, array $data, string $defaultTab): array
    {
        if (! array_key_exists('return_tab', $data)) {
            return [
                'message' => $message,
                'route' => null,
            ];
        }

        $tab = (string) ($data['return_tab'] ?? $defaultTab);
        $parameters = ['tab' => $tab];

        if (isset($data['return_type'])) {
            $parameters['type'] = (string) $data['return_type'];
        }

        if (isset($data['return_sort'])) {
            $parameters['sort'] = (string) $data['return_sort'];
        }

        return [
            'message' => $message,
            'route' => 'connections.index',
            'parameters' => $parameters,
        ];
    }

    private function requireTarget(string $target): void
    {
        if ($target === '') {
            throw ValidationException::withMessages([
                'target' => __('messages.choose_an_item_first_eb58aed060'),
            ]);
        }
    }
}

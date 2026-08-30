<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\GroupCatalog;
use App\Services\GroupState;
use Illuminate\Validation\ValidationException;

final class PerformGroupAction
{
    private const ACTIONS = [
        'join-group',
        'cancel-group-request',
        'leave-group',
        'set-group-notifications',
        'vote-group-poll',
        'dismiss-group-recommendation',
        'undo-group-recommendation',
        'create-group-report',
    ];

    public function __construct(
        private readonly GroupCatalog $groups,
        private readonly GroupState $groupState,
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
            'join-group' => $this->joinGroup($data),
            'cancel-group-request' => $this->cancelGroupRequest($data),
            'leave-group' => $this->leaveGroup($data),
            'set-group-notifications' => $this->setGroupNotifications($data),
            'vote-group-poll' => $this->voteGroupPoll($data),
            'dismiss-group-recommendation' => $this->dismissGroupRecommendation($data),
            'undo-group-recommendation' => $this->undoGroupRecommendation($data),
            'create-group-report' => $this->createGroupReport($data),
            default => throw ValidationException::withMessages([
                'action' => __('messages.this_action_is_unavailable'),
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function joinGroup(array $data): array
    {
        $group = $this->requireGroup($data);
        $status = $this->groupState->join($group['key'], $group['privacy']);

        return $this->groupResult(
            $status === 'joined'
                ? __('messages.group_joined', ['name' => $group['name']])
                : __('messages.group_join_request_sent', ['name' => $group['name']]),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function cancelGroupRequest(array $data): array
    {
        $group = $this->requireGroup($data);

        if (! $this->groupState->cancelRequest($group['key'])) {
            throw ValidationException::withMessages(['target' => __('messages.this_joining_request_is_no_longer_pending')]);
        }

        return $this->groupResult(
            __('messages.group_join_request_cancelled', ['name' => $group['name']]),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function leaveGroup(array $data): array
    {
        $group = $this->requireGroup($data);

        if (! $this->groupState->leave($group['key'])) {
            throw ValidationException::withMessages(['target' => __('messages.this_membership_is_no_longer_active')]);
        }

        return $this->groupResult(
            __('messages.group_left', ['name' => $group['name']]),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function setGroupNotifications(array $data): array
    {
        $group = $this->requireGroup($data);
        $level = (string) ($data['group_notification_level'] ?? '');

        if (! $this->groupState->setNotificationLevel($group['key'], $level)) {
            throw ValidationException::withMessages([
                'group_notification_level' => __('messages.join_this_group_before_changing_its_notifications'),
            ]);
        }

        return $this->groupResult(
            __('messages.group_notifications_updated', ['name' => $group['name']]),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function voteGroupPoll(array $data): array
    {
        $group = $this->requireGroup($data);

        if (! $this->groupState->vote(
            $group['key'],
            (string) ($data['poll'] ?? ''),
            (string) ($data['poll_option'] ?? ''),
        )) {
            throw ValidationException::withMessages([
                'poll_option' => __('messages.join_this_group_before_voting'),
            ]);
        }

        return $this->groupResult(__('messages.your_vote_was_counted'), $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function dismissGroupRecommendation(array $data): array
    {
        $group = $this->requireGroup($data);
        $this->groupState->dismissRecommendation($group['key']);

        return $this->groupResult(
            __('messages.group_recommendation_hidden', ['name' => $group['name']]),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function undoGroupRecommendation(array $data): array
    {
        $group = $this->requireGroup($data);

        if (! $this->groupState->undoRecommendationDismissal($group['key'])) {
            throw ValidationException::withMessages([
                'target' => __('messages.there_is_no_group_recommendation_to_restore'),
            ]);
        }

        return $this->groupResult(
            __('messages.group_recommendation_restored', ['name' => $group['name']]),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function requireGroup(array $data): array
    {
        $group = $this->groups->find((string) ($data['target'] ?? ''));

        if ($group === null) {
            throw ValidationException::withMessages(['target' => __('messages.choose_an_available_group')]);
        }

        return $group;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function groupResult(string $message, array $data): array
    {
        if (array_key_exists('group_return_filter', $data)) {
            $parameters = [
                'filter' => (string) ($data['group_return_filter'] ?? 'recommended'),
                'sort' => (string) ($data['group_return_sort'] ?? 'active'),
            ];
            $query = trim((string) ($data['group_return_q'] ?? ''));

            if ($query !== '') {
                $parameters['q'] = $query;
            }

            return [
                'message' => $message,
                'route' => 'groups.index',
                'parameters' => $parameters,
            ];
        }

        return [
            'message' => $message,
            'route' => 'groups.show',
            'parameters' => [
                'group' => (string) ($data['target'] ?? ''),
                'tab' => (string) ($data['group_return_tab'] ?? 'overview'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createGroupReport(array $data): array
    {
        $group = $this->requireGroup($data);

        $this->groupState->addReport([
            'target' => $group['key'],
            'reason' => (string) ($data['category'] ?? ''),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return [
            'message' => __('messages.your_private_group_report_was_received'),
            'route' => 'groups.show',
            'parameters' => ['group' => $group['key']],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function requireText(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));

        if ($value === '') {
            throw ValidationException::withMessages([
                $key => __('messages.this_field_is_required'),
            ]);
        }

        return $value;
    }
}

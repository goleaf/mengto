<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\PetFriendCatalog;
use App\Services\PetFriendState;
use App\Services\PrototypeState;
use Illuminate\Validation\ValidationException;

final class PerformPetFriendAction
{
    private const ACTIONS = [
        'send-pet-friend-request',
        'cancel-pet-friend-request',
        'accept-pet-friend-request',
        'decline-pet-friend-request',
        'toggle-pet-friend-pause',
        'remove-pet-friendship',
        'toggle-pet-friend-block',
        'dismiss-pet-friend-recommendation',
        'undo-pet-friend-recommendation',
    ];

    public function __construct(
        private readonly PetFriendCatalog $petFriends,
        private readonly PetFriendState $petFriendState,
        private readonly PrototypeState $state,
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
            'send-pet-friend-request' => $this->sendPetFriendRequest($data),
            'cancel-pet-friend-request' => $this->cancelPetFriendRequest($data),
            'accept-pet-friend-request' => $this->resolvePetFriendRequest($data, 'accepted'),
            'decline-pet-friend-request' => $this->resolvePetFriendRequest($data, 'declined'),
            'toggle-pet-friend-pause' => $this->togglePetFriendPause($data),
            'remove-pet-friendship' => $this->removePetFriendship($data),
            'toggle-pet-friend-block' => $this->togglePetFriendBlock($data),
            'dismiss-pet-friend-recommendation' => $this->dismissPetFriendRecommendation($data),
            'undo-pet-friend-recommendation' => $this->undoPetFriendRecommendation($data),
            default => throw ValidationException::withMessages([
                'action' => __('messages.this_action_is_unavailable_c64fa3888d'),
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function sendPetFriendRequest(array $data): array
    {
        [$source, $target, $candidate] = $this->requirePetFriendPair($data);

        if ($this->state->isActive('blocks', $target)) {
            throw ValidationException::withMessages([
                'target' => __('messages.unblock_this_profile_before_sending_a_friend_request_17e3219fe4'),
            ]);
        }

        $sent = $this->petFriendState->sendRequest($source, $target, [
            'intent' => (string) ($data['friendship_intent'] ?? 'friend'),
            'message' => trim((string) ($data['friendship_message'] ?? '')),
            'met_at' => trim((string) ($data['met_at'] ?? '')),
            'share_area' => ($data['share_area'] ?? 'no') === 'yes',
        ]);

        if (! $sent) {
            throw ValidationException::withMessages([
                'target' => __('messages.this_friendship_already_has_an_active_request_or_connect_9df02db23e'),
            ]);
        }

        return $this->petFriendResult(
            __('messages.pet_friend_request_sent', ['name' => $candidate['name']]),
            $data,
            'requests',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function cancelPetFriendRequest(array $data): array
    {
        [$source, $target, $candidate] = $this->requirePetFriendPair($data);

        if (! $this->petFriendState->cancelRequest($source, $target)) {
            throw ValidationException::withMessages(['target' => __('messages.this_request_can_no_longer_be_cancelled_aa646e6cbd')]);
        }

        return $this->petFriendResult(
            __('messages.pet_friend_request_cancelled', ['name' => $candidate['name']]),
            $data,
            'requests',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function resolvePetFriendRequest(array $data, string $status): array
    {
        [$source, $target, $candidate] = $this->requirePetFriendPair($data);
        $sourceName = (string) ($this->petFriends->find($source)['name'] ?? __('messages.your_pet'));

        if (! $this->petFriendState->resolveRequest($source, $target, $status)) {
            throw ValidationException::withMessages(['target' => __('messages.this_friend_request_is_no_longer_available_83d6ae6b3f')]);
        }

        return $this->petFriendResult(
            $status === 'accepted'
                ? __('messages.pet_friend_request_accepted', [
                    'source' => $sourceName,
                    'target' => $candidate['name'],
                ])
                : __('messages.pet_friend_request_declined', ['name' => $candidate['name']]),
            $data,
            $status === 'accepted' ? 'friends' : 'requests',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function togglePetFriendPause(array $data): array
    {
        [$source, $target, $candidate] = $this->requirePetFriendPair($data);
        $status = $this->petFriendState->togglePause($source, $target);

        if ($status === null) {
            throw ValidationException::withMessages(['target' => __('messages.this_friendship_cannot_be_paused_or_restored_9e7261afa7')]);
        }

        return $this->petFriendResult(
            $status === 'paused'
                ? __('messages.pet_friendship_paused', ['name' => $candidate['name']])
                : __('messages.pet_friendship_restored', ['name' => $candidate['name']]),
            $data,
            'friends',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function removePetFriendship(array $data): array
    {
        [$source, $target, $candidate] = $this->requirePetFriendPair($data);

        if (! $this->petFriendState->removeFriendship($source, $target)) {
            throw ValidationException::withMessages(['target' => __('messages.this_friendship_is_no_longer_active_6af9d35ac7')]);
        }

        return $this->petFriendResult(
            __('messages.pet_friendship_removed', ['name' => $candidate['name']]),
            $data,
            'friends',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function togglePetFriendBlock(array $data): array
    {
        [$source, $target, $candidate] = $this->requirePetFriendPair($data);
        $blocked = $this->state->toggle('blocks', $target);
        $this->petFriendState->setBlocked($source, $target, $blocked);

        return $this->petFriendResult(
            $blocked
                ? __('messages.pet_friend_blocked', ['name' => $candidate['name']])
                : __('messages.pet_friend_unblocked', ['name' => $candidate['name']]),
            $data,
            $blocked ? 'friends' : 'discover',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function dismissPetFriendRecommendation(array $data): array
    {
        [$source, $target, $candidate] = $this->requirePetFriendPair($data);
        $this->petFriendState->dismissRecommendation($source, $target);

        return $this->petFriendResult(
            __('messages.pet_friend_recommendation_hidden', ['name' => $candidate['name']]),
            $data,
            'discover',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function undoPetFriendRecommendation(array $data): array
    {
        [$source, $target, $candidate] = $this->requirePetFriendPair($data);

        if (! $this->petFriendState->undoRecommendationDismissal($source, $target)) {
            throw ValidationException::withMessages(['target' => __('messages.there_is_no_pet_recommendation_to_restore_ab7b1eb9bc')]);
        }

        return $this->petFriendResult(
            __('messages.pet_friend_recommendation_restored', ['name' => $candidate['name']]),
            $data,
            'discover',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: string, 1: string, 2: array<string, mixed>}
     */
    private function requirePetFriendPair(array $data): array
    {
        $source = (string) ($data['source_pet'] ?? '');
        $target = (string) ($data['target'] ?? '');
        $owned = $this->petFriends->owned();
        $candidate = $this->petFriends->find($target);

        if (! isset($owned[$source]) || $candidate === null || isset($owned[$target]) || $source === $target) {
            throw ValidationException::withMessages(['target' => __('messages.choose_an_available_pet_friendship_b06a4dbe80')]);
        }

        return [$source, $target, $candidate];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function petFriendResult(string $message, array $data, string $defaultTab): array
    {
        $parameters = [
            'pet' => str_replace('pet-', '', (string) ($data['source_pet'] ?? 'pet-scout')),
            'tab' => (string) ($data['pet_return_tab'] ?? $defaultTab),
        ];

        if (isset($data['pet_return_intent'])) {
            $parameters['intent'] = (string) $data['pet_return_intent'];
        }

        if (isset($data['pet_return_sort'])) {
            $parameters['sort'] = (string) $data['pet_return_sort'];
        }

        if (trim((string) ($data['pet_return_q'] ?? '')) !== '') {
            $parameters['q'] = trim((string) $data['pet_return_q']);
        }

        return [
            'message' => $message,
            'route' => 'pet-friends.index',
            'parameters' => $parameters,
        ];
    }
}

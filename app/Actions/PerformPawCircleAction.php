<?php

namespace App\Actions;

use App\Services\PawCircleConnectionPresenter;
use App\Services\PawCircleEventCatalog;
use App\Services\PawCircleEventContentCatalog;
use App\Services\PawCircleEventState;
use App\Services\PawCircleFeedPresenter;
use App\Services\PawCircleGroupCatalog;
use App\Services\PawCircleGroupState;
use App\Services\PawCirclePetFriendCatalog;
use App\Services\PawCirclePetFriendState;
use App\Services\PawCirclePreviewService;
use App\Services\PawCircleProfilePresenter;
use App\Services\PawCirclePrototypeState;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PerformPawCircleAction
{
    public function __construct(
        private readonly PawCirclePrototypeState $state,
        private readonly PawCirclePreviewService $preview,
        private readonly PawCircleProfilePresenter $profiles,
        private readonly PawCircleFeedPresenter $feed,
        private readonly PawCircleConnectionPresenter $connections,
        private readonly PawCirclePetFriendCatalog $petFriends,
        private readonly PawCirclePetFriendState $petFriendState,
        private readonly PawCircleGroupCatalog $groups,
        private readonly PawCircleGroupState $groupState,
        private readonly PawCircleEventCatalog $events,
        private readonly PawCircleEventContentCatalog $eventContent,
        private readonly PawCircleEventState $eventState,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     message: string,
     *     route: string|null,
     *     parameters?: array<string, string>
     * }
     */
    public function handle(array $data): array
    {
        $action = (string) $data['action'];
        $target = (string) ($data['target'] ?? '');
        $label = (string) ($data['label'] ?? 'Item');

        return match ($action) {
            'toggle-follow' => $this->toggle('follows', $target, $label, 'Following', 'No longer following'),
            'toggle-group' => $this->toggle('groups', $target, $label, 'Joined', 'Left'),
            'toggle-meetup' => $this->toggle('meetups', $target, $label, 'RSVP confirmed for', 'RSVP cancelled for'),
            'toggle-paw' => $this->toggle('paws', $target, $label, 'Sent a paw to', 'Removed your paw from'),
            'toggle-save' => $this->toggle('saved', $target, $label, 'Saved', 'Removed from saved'),
            'toggle-setting' => $this->toggleSetting($target, $label),
            'toggle-friend' => $this->toggle('friends', $target, $label, 'Friend request sent to', 'Friend request cancelled for'),
            'toggle-block' => $this->toggle('blocks', $target, $label, 'Blocked', 'Unblocked'),
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
            'send-pet-friend-request' => $this->sendPetFriendRequest($data),
            'cancel-pet-friend-request' => $this->cancelPetFriendRequest($data),
            'accept-pet-friend-request' => $this->resolvePetFriendRequest($data, 'accepted'),
            'decline-pet-friend-request' => $this->resolvePetFriendRequest($data, 'declined'),
            'toggle-pet-friend-pause' => $this->togglePetFriendPause($data),
            'remove-pet-friendship' => $this->removePetFriendship($data),
            'toggle-pet-friend-block' => $this->togglePetFriendBlock($data),
            'dismiss-pet-friend-recommendation' => $this->dismissPetFriendRecommendation($data),
            'undo-pet-friend-recommendation' => $this->undoPetFriendRecommendation($data),
            'join-group' => $this->joinGroup($data),
            'cancel-group-request' => $this->cancelGroupRequest($data),
            'leave-group' => $this->leaveGroup($data),
            'set-group-notifications' => $this->setGroupNotifications($data),
            'vote-group-poll' => $this->voteGroupPoll($data),
            'dismiss-group-recommendation' => $this->dismissGroupRecommendation($data),
            'undo-group-recommendation' => $this->undoGroupRecommendation($data),
            'toggle-event-interest' => $this->toggleEventInterest($data),
            'register-event' => $this->registerEvent($data),
            'cancel-event-registration' => $this->cancelEventRegistration($data),
            'complete-event-payment' => $this->completeEventPayment($data),
            'toggle-event-calendar' => $this->toggleEventCalendar($data),
            'toggle-event-reminder' => $this->toggleEventReminder($data),
            'check-in-event' => $this->checkInEvent($data),
            'acknowledge-event-reschedule' => $this->acknowledgeEventReschedule($data),
            'set-event-travel-status' => $this->setEventTravelStatus($data),
            'send-event-message' => $this->sendEventMessage($data),
            'publish-event-announcement' => $this->publishEventAnnouncement($data),
            'approve-event-application' => $this->resolveEventApplication($data, 'approved'),
            'decline-event-application' => $this->resolveEventApplication($data, 'declined'),
            'promote-event-waitlist' => $this->promoteEventWaitlist($data),
            'reschedule-event' => $this->rescheduleEvent($data),
            'cancel-event' => $this->cancelEvent($data),
            'add-event-photo' => $this->addEventPhoto($data),
            'submit-event-review' => $this->submitEventReview($data),
            'create-event-report' => $this->createEventReport($data),
            'mark-all-read' => $this->markAllRead(),
            'send-message' => $this->sendMessage($data),
            'create-comment' => $this->createComment($data),
            'create-post' => $this->createPost($data),
            'update-post' => $this->updatePost($data),
            'set-reaction' => $this->setReaction($data),
            'toggle-post-subscription' => $this->toggle('post-subscriptions', $target, $label, 'Notifications enabled for', 'Notifications paused for'),
            'hide-post' => $this->toggle('hidden-posts', $target, $label, 'Hidden', 'Visible again'),
            'mute-author' => $this->muteAuthor($target),
            'block-post-author' => $this->blockAuthor($target),
            'repost-post' => $this->repostPost($target),
            'archive-post' => $this->movePost($target, 'archived'),
            'restore-post' => $this->movePost($target, 'published'),
            'delete-post' => $this->deletePost($target),
            'create-post-report' => $this->createPostReport($data),
            'create-group-report' => $this->createGroupReport($data),
            'create-group' => $this->createGroup($data),
            'create-meetup' => $this->createEvent($data),
            'create-walk-plan' => $this->createWalkPlan($data),
            'create-pet' => $this->create('pets', $data, 'Your pet was added to PawCircle.', 'pet-social.pets.index'),
            'update-profile' => $this->updateProfile($data),
            'update-pet' => $this->updatePet($data),
            'update-profile-privacy' => $this->updateProfilePrivacy($data),
            'update-pet-privacy' => $this->updatePetPrivacy($data),
            'create-profile-report' => $this->createProfileReport($data),
            'share' => $this->share($target, $label),
            'plan-walk' => $this->planWalk($data),
            'advance-walk-plan' => $this->advanceWalkPlan($target, $label),
            'cancel-walk-plan' => $this->cancelWalkPlan($target, $label),
            'call' => $this->call($target, $label),
            'show-info' => $this->showInfo($target),
            default => throw ValidationException::withMessages(['action' => 'This action is unavailable.']),
        };
    }

    /**
     * @return array{message: string, route: null}
     */
    private function toggle(string $collection, string $target, string $label, string $enabled, string $disabled): array
    {
        $this->requireTarget($target);
        $isActive = $this->state->toggle($collection, $target);

        return [
            'message' => ($isActive ? $enabled : $disabled).' '.$label.'.',
            'route' => null,
        ];
    }

    /**
     * @return array{message: string, route: null}
     */
    private function toggleSetting(string $target, string $label): array
    {
        $this->requireTarget($target);
        $isEnabled = $this->state->toggleSetting($target);

        return [
            'message' => $label.' notifications '.($isEnabled ? 'enabled.' : 'paused.'),
            'route' => null,
        ];
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
                'target' => 'This private profile requires a follow request.',
            ]);
        }

        $following = $this->state->toggleSubscription($target);

        return $this->connectionResult(
            $following
                ? 'Following '.$connection['name'].'.'
                : 'No longer following '.$connection['name'].'.',
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
                'target' => 'This public profile can be followed immediately.',
            ]);
        }

        $pending = $this->state->toggleOutgoingFollowRequest($target);

        return $this->connectionResult(
            $pending
                ? 'Follow request sent to '.$connection['name'].'.'
                : 'Follow request cancelled for '.$connection['name'].'.',
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
                'target' => 'Follow this profile before changing its settings.',
            ]);
        }

        $message = match ($flag) {
            'favorite' => $enabled
                ? $connection['name'].' added to favorites.'
                : $connection['name'].' removed from favorites.',
            default => $enabled
                ? $connection['name'].' muted in your feed.'
                : $connection['name'].' restored to your feed.',
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
            $blocked ? $connection['name'].' was blocked.' : $connection['name'].' was unblocked.',
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
                'target' => 'Follow this profile before changing notifications.',
            ]);
        }

        $labels = [
            'all' => 'all publications',
            'important' => 'important updates',
            'standard' => 'standard updates',
            'feed' => 'feed only',
            'off' => 'paused',
        ];

        return $this->connectionResult(
            'Notifications for '.$connection['name'].': '.$labels[$level].'.',
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
            throw ValidationException::withMessages(['target' => 'This recommendation is unavailable.']);
        }

        $this->state->dismissRecommendation($target);

        return $this->connectionResult(
            $connection['name'].' removed from recommendations. You can undo this action.',
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
            throw ValidationException::withMessages(['target' => 'There is no recommendation to restore.']);
        }

        return $this->connectionResult(
            $connection['name'].' restored to recommendations.',
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
            throw ValidationException::withMessages(['target' => 'This follower is unavailable.']);
        }

        $this->state->removeFollower($target);

        return $this->connectionResult(
            $connection['name'].' removed from your followers.',
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
            throw ValidationException::withMessages(['target' => 'This follow request is unavailable.']);
        }

        return $this->connectionResult(
            $status === 'accepted'
                ? $connection['name'].' can now follow your public profile.'
                : 'Follow request from '.$connection['name'].' declined.',
            $data,
            'requests',
        );
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
                'target' => 'Unblock this profile before sending a friend request.',
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
                'target' => 'This friendship already has an active request or connection.',
            ]);
        }

        return $this->petFriendResult(
            'Friend request sent to '.$candidate['name'].' by their owner.',
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
            throw ValidationException::withMessages(['target' => 'This request can no longer be cancelled.']);
        }

        return $this->petFriendResult(
            'Friend request to '.$candidate['name'].' cancelled.',
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
        $sourceName = (string) ($this->petFriends->find($source)['name'] ?? 'Your pet');

        if (! $this->petFriendState->resolveRequest($source, $target, $status)) {
            throw ValidationException::withMessages(['target' => 'This friend request is no longer available.']);
        }

        return $this->petFriendResult(
            $status === 'accepted'
                ? $sourceName.' and '.$candidate['name'].' are now friends.'
                : 'Friend request from '.$candidate['name'].' declined.',
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
            throw ValidationException::withMessages(['target' => 'This friendship cannot be paused or restored.']);
        }

        return $this->petFriendResult(
            $status === 'paused'
                ? 'Friendship with '.$candidate['name'].' paused.'
                : 'Friendship with '.$candidate['name'].' restored.',
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
            throw ValidationException::withMessages(['target' => 'This friendship is no longer active.']);
        }

        return $this->petFriendResult(
            'Friendship with '.$candidate['name'].' removed.',
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
                ? $candidate['name'].' and the managing owner were hidden from this friendship center.'
                : $candidate['name'].' was unblocked. A new request is still required.',
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
            $candidate['name'].' hidden from recommendations. You can undo this action.',
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
            throw ValidationException::withMessages(['target' => 'There is no pet recommendation to restore.']);
        }

        return $this->petFriendResult(
            $candidate['name'].' restored to recommendations.',
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
            throw ValidationException::withMessages(['target' => 'Choose an available pet friendship.']);
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
            'route' => 'pet-social.pet-friends.index',
            'parameters' => $parameters,
        ];
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
                ? 'You joined '.$group['name'].'.'
                : 'Your request to join '.$group['name'].' was sent.',
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
            throw ValidationException::withMessages(['target' => 'This joining request is no longer pending.']);
        }

        return $this->groupResult('Joining request cancelled for '.$group['name'].'.', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function leaveGroup(array $data): array
    {
        $group = $this->requireGroup($data);

        if (! $this->groupState->leave($group['key'])) {
            throw ValidationException::withMessages(['target' => 'This membership is no longer active.']);
        }

        return $this->groupResult('You left '.$group['name'].'.', $data);
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
                'group_notification_level' => 'Join this group before changing its notifications.',
            ]);
        }

        return $this->groupResult('Notifications updated for '.$group['name'].'.', $data);
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
                'poll_option' => 'Join this group before voting.',
            ]);
        }

        return $this->groupResult('Your vote was counted.', $data);
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
            $group['name'].' hidden from recommendations. You can undo this action.',
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
                'target' => 'There is no group recommendation to restore.',
            ]);
        }

        return $this->groupResult($group['name'].' restored to recommendations.', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function requireGroup(array $data): array
    {
        $group = $this->groups->find((string) ($data['target'] ?? ''));

        if ($group === null) {
            throw ValidationException::withMessages(['target' => 'Choose an available group.']);
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
                'route' => 'pet-social.groups.index',
                'parameters' => $parameters,
            ];
        }

        return [
            'message' => $message,
            'route' => 'pet-social.groups.show',
            'parameters' => [
                'group' => (string) ($data['target'] ?? ''),
                'tab' => (string) ($data['group_return_tab'] ?? 'overview'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function toggleEventInterest(array $data): array
    {
        $event = $this->requireEvent($data);
        $active = $this->eventState->toggleInterest($event['key']);

        return $this->eventResult(
            $active ? $event['title'].' saved to your events.' : $event['title'].' removed from saved events.',
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function registerEvent(array $data): array
    {
        $event = $this->requireEvent($data);
        $ticketType = (string) ($data['ticket_type'] ?? 'standard');
        $ticket = collect($this->eventContent->content($event)['ticket_options'])
            ->firstWhere('key', $ticketType);

        if ($ticket === null) {
            throw ValidationException::withMessages([
                'ticket_type' => 'Choose an available event ticket.',
            ]);
        }

        if (! $event['pets_allowed'] && ($data['event_pet'] ?? 'owner-only') !== 'owner-only') {
            throw ValidationException::withMessages([
                'event_pet' => 'This event is for owners without resident pets.',
            ]);
        }

        $existing = $this->eventState->registration($event['key']);
        $registration = $this->eventState->register($event, [
            ...$data,
            'ticket_price_minor' => $ticket['price_minor'],
        ]);

        if ($existing !== null && $registration['id'] === $existing['id']) {
            return $this->eventResult(
                'Your existing registration is still '.Str::lower(Str::headline($registration['status'])).'.',
                $data,
                'tickets',
            );
        }

        $message = match ($registration['status']) {
            'pending' => 'Your application was sent to the event organizer.',
            'waitlisted' => 'The event is full. You joined the waitlist.',
            'payment_required' => 'Your place is reserved temporarily. Complete the prototype payment to confirm it.',
            default => 'Your event registration is confirmed.',
        };

        return $this->eventResult($message, $data, 'tickets');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function cancelEventRegistration(array $data): array
    {
        $event = $this->requireEvent($data);
        $registration = $this->eventState->cancelRegistration($event['key']);

        if ($registration === null) {
            throw ValidationException::withMessages([
                'target' => 'This event registration can no longer be cancelled.',
            ]);
        }

        $message = $registration['payment_status'] === 'refunded'
            ? 'Registration cancelled. The prototype payment is marked refunded.'
            : 'Registration cancelled and the place was released.';

        return $this->eventResult($message, $data, 'tickets');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function completeEventPayment(array $data): array
    {
        $event = $this->requireEvent($data);
        $outcome = (string) ($data['payment_outcome'] ?? 'success');
        $registration = $this->eventState->completePayment($event['key'], $outcome);

        if ($registration === null) {
            throw ValidationException::withMessages([
                'target' => 'This registration does not have a pending prototype payment.',
            ]);
        }

        return $this->eventResult(
            $outcome === 'failure'
                ? 'Payment simulation failed. No charge or duplicate ticket was created.'
                : 'Payment simulation complete. Your unique ticket is ready.',
            $data,
            'tickets',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function toggleEventCalendar(array $data): array
    {
        $event = $this->requireEvent($data);
        $active = $this->eventState->toggleCalendar($event['key']);

        return $this->eventResult(
            $active ? 'Event added to your calendar.' : 'Event removed from your calendar.',
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function toggleEventReminder(array $data): array
    {
        $event = $this->requireEvent($data);
        $active = $this->eventState->toggleReminder($event['key']);

        return $this->eventResult(
            $active ? 'Event reminders enabled.' : 'Event reminders paused.',
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function checkInEvent(array $data): array
    {
        $event = $this->requireEvent($data);
        $registration = $this->eventState->checkIn(
            $event['key'],
            (string) ($data['check_in_method'] ?? 'qr'),
        );

        if ($registration === null) {
            throw ValidationException::withMessages([
                'target' => 'A confirmed ticket is required before check-in.',
            ]);
        }

        return $this->eventResult('Attendance confirmed. Repeating check-in will not create a duplicate.', $data, 'tickets');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function acknowledgeEventReschedule(array $data): array
    {
        $event = $this->requireEvent($data);

        if ($this->eventState->acknowledgeReschedule($event['key']) === null) {
            throw ValidationException::withMessages([
                'target' => 'Register before confirming the revised date.',
            ]);
        }

        return $this->eventResult('You confirmed the revised event details.', $data, 'tickets');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function setEventTravelStatus(array $data): array
    {
        $event = $this->requireEvent($data);
        $status = (string) ($data['travel_status'] ?? '');

        if ($this->eventState->registration($event['key']) === null) {
            throw ValidationException::withMessages([
                'target' => 'Register before sharing an arrival status.',
            ]);
        }

        $this->eventState->setTravelStatus($event['key'], $status);

        return $this->eventResult('Arrival status updated to '.Str::lower(Str::headline($status)).'.', $data, 'tickets');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function sendEventMessage(array $data): array
    {
        $event = $this->requireEvent($data);

        if ($this->eventState->registration($event['key']) === null && ! $event['managed_by_current_user']) {
            throw ValidationException::withMessages([
                'target' => 'Register or apply before joining the event chat.',
            ]);
        }

        $this->eventState->addMessage($event['key'], [
            'name' => 'Mia Carter',
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return $this->eventResult('Message posted in the event chat.', $data, 'chat');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function publishEventAnnouncement(array $data): array
    {
        $event = $this->requireManagedEvent($data);
        $this->eventState->addAnnouncement($event['key'], [
            'title' => $this->requireText($data, 'title'),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return $this->eventResult('Announcement published for registered attendees.', $data, 'announcements');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function resolveEventApplication(array $data, string $status): array
    {
        $event = $this->requireManagedEvent($data);
        $application = (string) ($data['event_application'] ?? '');

        if (! $this->eventState->resolveApplication($event['key'], $application, $status)) {
            throw ValidationException::withMessages([
                'event_application' => 'This event application is no longer pending.',
            ]);
        }

        return $this->eventResult(
            $status === 'approved' ? 'Application approved.' : 'Application declined without exposing a private reason.',
            $data,
            'manage',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function promoteEventWaitlist(array $data): array
    {
        $event = $this->requireManagedEvent($data);
        $candidate = (string) ($data['event_candidate'] ?? '');

        if (! $this->eventState->promoteWaitlist($event['key'], $candidate)) {
            throw ValidationException::withMessages([
                'event_candidate' => 'This waitlist place can no longer be promoted.',
            ]);
        }

        return $this->eventResult('The next eligible person received a temporary place hold.', $data, 'manage');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function rescheduleEvent(array $data): array
    {
        $event = $this->requireManagedEvent($data);
        $this->eventState->reschedule($event['key'], [
            'date' => (string) ($data['event_date'] ?? ''),
            'time' => (string) ($data['event_time'] ?? ''),
            'note' => $this->requireText($data, 'event_note'),
        ]);

        return $this->eventResult('Event rescheduled. Existing attendees must confirm the new details.', $data, 'manage');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function cancelEvent(array $data): array
    {
        $event = $this->requireManagedEvent($data);
        $this->eventState->cancelEvent($event['key'], (string) ($data['event_reason'] ?? 'Cancelled by organizer.'));

        return $this->eventResult(
            'Event cancelled. New payments are stopped and attendee obligations remain visible.',
            $data,
            'manage',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function addEventPhoto(array $data): array
    {
        $event = $this->requireEvent($data);
        $registration = $this->eventState->registration($event['key']);

        if (! $event['managed_by_current_user'] && $registration === null) {
            throw ValidationException::withMessages([
                'target' => 'Only organizers and attendees can add event photos.',
            ]);
        }

        $this->eventState->addPhoto($event['key'], [
            'src' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=1200&h=900&q=85',
            'alt' => 'Dog resting on grass during a calm community event',
            'caption' => trim((string) ($data['photo_caption'] ?? 'Shared by an event attendee with consent.')),
        ]);

        return $this->eventResult('Photo added to the event album for moderation.', $data, 'media');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function submitEventReview(array $data): array
    {
        $event = $this->requireEvent($data);

        if (! $this->eventState->addReview($event['key'], [
            'rating' => (int) ($data['event_rating'] ?? 0),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ])) {
            throw ValidationException::withMessages([
                'target' => 'Only checked-in attendees can publish a verified event review.',
            ]);
        }

        return $this->eventResult('Your verified-attendance review was published.', $data, 'reviews');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function createEventReport(array $data): array
    {
        $event = $this->requireEvent($data);
        $this->eventState->addReport($event['key'], [
            'reason' => (string) ($data['category'] ?? ''),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return $this->eventResult('Your private event report was received.', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function requireEvent(array $data): array
    {
        $event = $this->events->find((string) ($data['target'] ?? ''));

        if ($event === null) {
            throw ValidationException::withMessages([
                'target' => 'Choose an available event.',
            ]);
        }

        return $event;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function requireManagedEvent(array $data): array
    {
        $event = $this->requireEvent($data);

        if (! $event['managed_by_current_user']) {
            throw ValidationException::withMessages([
                'target' => 'Only an authorized event organizer can perform this action.',
            ]);
        }

        return $event;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function eventResult(string $message, array $data, string $defaultTab = 'overview'): array
    {
        if (! array_key_exists('event_return_tab', $data)) {
            return [
                'message' => $message,
                'route' => null,
            ];
        }

        return [
            'message' => $message,
            'route' => 'pet-social.meetups.show',
            'parameters' => [
                'event' => (string) ($data['target'] ?? ''),
                'tab' => (string) ($data['event_return_tab'] ?? $defaultTab),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function requireConnection(string $target): array
    {
        $this->requireTarget($target);
        $connection = $this->connections->target($target);

        if ($connection === null) {
            throw ValidationException::withMessages(['target' => 'This profile or interest is unavailable.']);
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
            'route' => 'pet-social.connections.index',
            'parameters' => $parameters,
        ];
    }

    /**
     * @return array{message: string, route: null}
     */
    private function markAllRead(): array
    {
        $this->state->markNotificationsRead();

        return ['message' => 'All notifications marked as read.', 'route' => null];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array{conversation: string}}
     */
    private function sendMessage(array $data): array
    {
        $body = $this->requireText($data, 'body');
        $now = now();

        $this->state->addMessage([
            'target' => (string) ($data['target'] ?? 'ari'),
            'sender' => 'Mia',
            'body' => $body,
            'time' => $now->format('g:i A'),
            'datetime' => $now->toAtomString(),
            'mine' => true,
        ]);

        return [
            'message' => 'Message sent to your neighbor.',
            'route' => 'pet-social.messages.index',
            'parameters' => ['conversation' => (string) ($data['target'] ?? 'ari')],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array{post: string}}
     */
    private function createComment(array $data): array
    {
        $post = (string) ($data['target'] ?? '');
        $this->requireTarget($post);

        if ($this->preview->postThreadData($post) === null) {
            throw ValidationException::withMessages(['target' => 'This conversation is unavailable.']);
        }

        $now = now();

        $this->state->addComment([
            'id' => 'comment-'.Str::lower((string) Str::ulid()),
            'post' => $post,
            'parent' => (string) ($data['parent'] ?? ''),
            'author' => 'Mia Carter',
            'pet' => 'Scout',
            'initials' => 'MC',
            'tone' => 'sun',
            'body' => $this->requireText($data, 'body'),
            'time' => $now->format('g:i A'),
            'datetime' => $now->toAtomString(),
            'mine' => true,
        ]);

        return [
            'message' => 'Your reply joined the conversation.',
            'route' => 'pet-social.posts.show',
            'parameters' => ['post' => $post],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createPost(array $data): array
    {
        $key = 'created-post-'.Str::lower((string) Str::ulid());
        $status = (string) ($data['intent'] ?? 'published');
        $now = now()->toAtomString();

        $this->state->addPost([
            'key' => $key,
            ...$this->postValues($data),
            'status' => $status,
            'original_key' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'message' => $status === 'draft'
                ? 'Draft saved privately.'
                : 'Your publication is live.',
            'route' => 'pet-social.preview',
            'parameters' => ['feed' => $status === 'draft' ? 'drafts' : 'home'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function updatePost(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $status = (string) ($data['intent'] ?? 'published');

        if (! $this->state->updatePost($target, [
            ...$this->postValues($data),
            'status' => $status,
        ])) {
            throw ValidationException::withMessages(['target' => 'This publication cannot be edited.']);
        }

        return [
            'message' => $status === 'draft' ? 'Changes saved as a draft.' : 'Publication updated.',
            'route' => 'pet-social.preview',
            'parameters' => ['feed' => $status === 'draft' ? 'drafts' : 'home'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function postValues(array $data): array
    {
        return [
            'identity' => (string) ($data['identity'] ?? 'mia'),
            'format' => (string) ($data['format'] ?? 'text'),
            'title' => trim((string) ($data['title'] ?? '')),
            'body' => $this->requireText($data, 'body'),
            'topic' => (string) ($data['topic'] ?? 'community'),
            'tags' => trim((string) ($data['tags'] ?? '')),
            'media' => (string) ($data['media'] ?? 'none'),
            'media_alt' => trim((string) ($data['media_alt'] ?? '')),
            'location' => (string) ($data['location'] ?? 'none'),
            'audience' => (string) ($data['audience'] ?? 'public'),
            'comment_policy' => (string) ($data['comment_policy'] ?? 'all'),
            'sensitive' => (string) ($data['sensitive'] ?? 'no'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: null}
     */
    private function setReaction(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $reaction = (string) ($data['reaction'] ?? '');
        $post = $this->feed->post($target);

        if ($post === null || ! array_key_exists($reaction, $post['reaction_options'])) {
            throw ValidationException::withMessages(['reaction' => 'Choose an available reaction.']);
        }

        $selected = $this->state->setReaction($target, $reaction);

        return [
            'message' => $selected === null ? 'Reaction removed.' : 'Reaction updated.',
            'route' => null,
        ];
    }

    /**
     * @return array{message: string, route: null}
     */
    private function muteAuthor(string $target): array
    {
        $post = $this->feed->post($target);

        if ($post === null) {
            throw ValidationException::withMessages(['target' => 'This author is unavailable.']);
        }

        $muted = $this->state->toggle('muted-authors', (string) $post['author_key']);

        return [
            'message' => $muted
                ? $post['author'].' was muted in your feed.'
                : $post['author'].' is visible again.',
            'route' => null,
        ];
    }

    /**
     * @return array{message: string, route: null}
     */
    private function blockAuthor(string $target): array
    {
        $post = $this->feed->post($target);

        if ($post === null) {
            throw ValidationException::withMessages(['target' => 'This author is unavailable.']);
        }

        $blocked = $this->state->toggle('blocked-authors', (string) $post['author_key']);

        return [
            'message' => $blocked
                ? $post['author'].' was blocked.'
                : $post['author'].' was unblocked.',
            'route' => null,
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function repostPost(string $target): array
    {
        $post = $this->feed->post($target);

        if ($post === null || ($post['status'] ?? 'published') !== 'published') {
            throw ValidationException::withMessages(['target' => 'This publication cannot be reposted.']);
        }

        $now = now()->toAtomString();

        $this->state->addPost([
            'key' => 'created-post-'.Str::lower((string) Str::ulid()),
            'identity' => 'mia',
            'format' => 'repost',
            'title' => '',
            'body' => 'Sharing this with my circle.',
            'topic' => 'community',
            'tags' => '',
            'media' => 'none',
            'media_alt' => '',
            'location' => 'none',
            'audience' => 'public',
            'comment_policy' => 'all',
            'sensitive' => 'no',
            'status' => 'published',
            'original_key' => $target,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'message' => 'Repost added to your feed.',
            'route' => 'pet-social.preview',
            'parameters' => ['feed' => 'home'],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function movePost(string $target, string $status): array
    {
        if (! $this->state->movePost($target, $status)) {
            throw ValidationException::withMessages(['target' => 'This publication cannot be moved.']);
        }

        return [
            'message' => $status === 'archived' ? 'Publication moved to archive.' : 'Publication restored.',
            'route' => 'pet-social.preview',
            'parameters' => ['feed' => $status === 'archived' ? 'archive' : 'home'],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function deletePost(string $target): array
    {
        if (! $this->state->deletePost($target)) {
            throw ValidationException::withMessages(['target' => 'This publication cannot be deleted.']);
        }

        return [
            'message' => 'Publication deleted from this prototype.',
            'route' => 'pet-social.preview',
            'parameters' => ['feed' => 'home'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createPostReport(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $context = $this->feed->reportContext($target);

        if ($context === null) {
            throw ValidationException::withMessages(['target' => 'This publication is unavailable to report.']);
        }

        $this->state->addPostReport([
            'target' => $target,
            'reason' => (string) ($data['category'] ?? ''),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return [
            'message' => 'Your private report was received.',
            'route' => $context['route'],
            'parameters' => $context['route_parameters'],
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
            'message' => 'Your private group report was received.',
            'route' => 'pet-social.groups.show',
            'parameters' => ['group' => $group['key']],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function createGroup(array $data): array
    {
        $this->state->addCreated('groups', [
            'id' => (string) Str::uuid(),
            'title' => $this->requireText($data, 'title'),
            'body' => $this->requireText($data, 'body'),
            'detail' => '',
            'location' => (string) ($data['city'] ?? ''),
            'category' => (string) ($data['category'] ?? ''),
            'date' => '',
            'privacy' => (string) ($data['privacy'] ?? 'public'),
            'language' => (string) ($data['language'] ?? 'English'),
            'rules' => $this->requireText($data, 'rules'),
            'pet_identity' => (string) ($data['pet_identity'] ?? 'mia'),
            'posting_policy' => (string) ($data['posting_policy'] ?? 'members'),
        ]);

        return [
            'message' => 'Your new group is ready in the directory.',
            'route' => 'pet-social.groups.index',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function createEvent(array $data): array
    {
        $this->state->addCreated('meetups', [
            'id' => (string) Str::uuid(),
            'title' => $this->requireText($data, 'title'),
            'body' => $this->requireText($data, 'body'),
            'detail' => (string) ($data['event_safety_plan'] ?? ''),
            'location' => (string) ($data['location'] ?? ''),
            'category' => (string) ($data['category'] ?? 'walk'),
            'date' => (string) ($data['date'] ?? ''),
            'time' => (string) ($data['time'] ?? ''),
            'format' => (string) ($data['event_format'] ?? 'offline'),
            'organizer' => (string) ($data['event_organizer'] ?? 'mia'),
            'timezone' => (string) ($data['event_timezone'] ?? 'America/Los_Angeles'),
            'privacy' => (string) ($data['privacy'] ?? 'public'),
            'registration_policy' => (string) ($data['event_registration_policy'] ?? 'approval'),
            'capacity' => (string) ($data['event_capacity'] ?? '8'),
            'ticket_model' => (string) ($data['event_ticket_model'] ?? 'free'),
            'ticket_price' => (string) ($data['event_ticket_price'] ?? ''),
            'online_url' => (string) ($data['event_online_url'] ?? ''),
            'cover' => (string) ($data['event_cover'] ?? 'walk'),
            'rules' => $this->requireText($data, 'rules'),
            'created_at' => now()->toAtomString(),
        ]);

        return [
            'message' => 'Your event is published with registration and safety settings.',
            'route' => 'pet-social.meetups.index',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function create(string $collection, array $data, string $message, string $route): array
    {
        $this->state->addCreated($collection, [
            'id' => (string) Str::uuid(),
            'title' => $this->requireText($data, 'title'),
            'body' => $this->requireText($data, 'body'),
            'detail' => (string) ($data['detail'] ?? ''),
            'location' => (string) ($data['location'] ?? ''),
            'category' => (string) ($data['category'] ?? ''),
            'date' => (string) ($data['date'] ?? ''),
        ]);

        return ['message' => $message, 'route' => $route];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array{filter: string}}
     */
    private function createWalkPlan(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $participant = $this->walkParticipant($target);
        $now = now();

        $this->state->addWalkPlan([
            'id' => (string) Str::uuid(),
            'target' => $target,
            'label' => $participant['pet'],
            'conversation' => $participant['conversation'],
            'title' => $this->requireText($data, 'title'),
            'body' => $this->requireText($data, 'body'),
            'detail' => (string) ($data['detail'] ?? 'Easy pace, 30 min'),
            'location' => (string) ($data['location'] ?? ''),
            'date' => (string) ($data['date'] ?? ''),
            'time' => (string) ($data['time'] ?? ''),
            'status' => 'draft',
            'created_at' => $now->toAtomString(),
            'updated_at' => $now->toAtomString(),
        ]);

        return [
            'message' => 'Your walk draft is ready to review.',
            'route' => 'pet-social.walks.index',
            'parameters' => ['filter' => 'drafts'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array{filter: string}}
     */
    private function planWalk(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $this->requireTarget($target);
        $participant = $this->walkParticipant($target);
        $now = now();

        $this->state->ensureWalkPlanDraft([
            'id' => (string) Str::uuid(),
            'target' => $target,
            'label' => $participant['pet'],
            'conversation' => $participant['conversation'],
            'title' => 'Walk with '.$participant['pet'],
            'body' => 'Start with a calm hello, keep the first loop easy, and leave room for a quiet finish.',
            'detail' => 'Easy pace, 30 min',
            'location' => $participant['location'],
            'date' => today()->addDays(2)->format('Y-m-d'),
            'time' => '08:30',
            'status' => 'draft',
            'created_at' => $now->toAtomString(),
            'updated_at' => $now->toAtomString(),
        ]);

        return [
            'message' => 'A calm walk with '.$participant['pet'].' is ready in your drafts.',
            'route' => 'pet-social.walks.index',
            'parameters' => ['filter' => 'drafts'],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array{filter: string}}
     */
    private function advanceWalkPlan(string $target, string $label): array
    {
        $this->requireTarget($target);
        $status = $this->state->advanceWalkPlan($target);

        if ($status === null) {
            throw ValidationException::withMessages(['target' => 'This walk plan cannot be updated.']);
        }

        return [
            'message' => $status === 'confirmed'
                ? $label.' is confirmed and ready to share.'
                : $label.' is marked complete.',
            'route' => 'pet-social.walks.index',
            'parameters' => ['filter' => $status === 'completed' ? 'completed' : 'upcoming'],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array{filter: string}}
     */
    private function cancelWalkPlan(string $target, string $label): array
    {
        $this->requireTarget($target);

        if (! $this->state->cancelWalkPlan($target)) {
            throw ValidationException::withMessages(['target' => 'This walk plan cannot be cancelled.']);
        }

        return [
            'message' => $label.' was moved to cancelled plans.',
            'route' => 'pet-social.walks.index',
            'parameters' => ['filter' => 'cancelled'],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array{target: string}}
     */
    private function share(string $target, string $label): array
    {
        $this->requireTarget($target);

        if ($this->preview->shareData($target) === null) {
            throw ValidationException::withMessages(['target' => 'This item is unavailable to share.']);
        }

        return [
            'message' => $label.' is ready to share.',
            'route' => 'pet-social.share.show',
            'parameters' => ['target' => $target],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array{conversation: string}}
     */
    private function call(string $target, string $label): array
    {
        $this->requireTarget($target);
        $isRequested = $this->state->toggle('call-requests', $target);

        return [
            'message' => $isRequested
                ? 'Call request sent to '.$label.'.'
                : 'Call request to '.$label.' was cancelled.',
            'route' => 'pet-social.messages.details',
            'parameters' => ['conversation' => $target],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array{conversation: string}}
     */
    private function showInfo(string $target): array
    {
        $this->requireTarget($target);

        return [
            'message' => 'Conversation details are ready.',
            'route' => 'pet-social.messages.details',
            'parameters' => ['conversation' => $target],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function updateProfile(array $data): array
    {
        $this->state->updateProfile([
            'name' => $this->requireText($data, 'title'),
            'bio' => $this->requireText($data, 'body'),
            'status' => (string) ($data['detail'] ?? ''),
            'location' => (string) ($data['location'] ?? ''),
        ]);

        return ['message' => 'Your profile was updated.', 'route' => 'pet-social.profile.mia'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function updatePet(array $data): array
    {
        $pet = (string) ($data['target'] ?? 'scout');

        $this->state->updatePet([
            'name' => $this->requireText($data, 'title'),
            'story' => $this->requireText($data, 'body'),
            'status' => (string) ($data['detail'] ?? ''),
            'breed' => (string) ($data['category'] ?? ''),
        ], $pet);

        return [
            'message' => ucfirst($pet).' profile was updated.',
            'route' => $pet === 'nori' ? 'pet-social.pets.nori' : 'pet-social.pets.scout',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function updateProfilePrivacy(array $data): array
    {
        $this->state->updateOwnerPrivacy([
            'location' => (string) $data['location_visibility'],
            'pets' => (string) $data['pets_visibility'],
            'posts' => (string) $data['posts_visibility'],
            'friends' => (string) $data['friends_visibility'],
            'activity' => (string) $data['activity_visibility'],
        ]);

        return [
            'message' => 'Owner profile privacy was updated.',
            'route' => 'pet-social.profile.mia',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function updatePetPrivacy(array $data): array
    {
        $pet = (string) ($data['target'] ?? '');
        $this->state->updatePetPrivacy($pet, [
            'location' => (string) $data['location_visibility'],
            'posts' => (string) $data['posts_visibility'],
            'friends' => (string) $data['friends_visibility'],
            'care' => (string) $data['care_visibility'],
            'activity' => (string) $data['activity_visibility'],
        ]);

        return [
            'message' => ucfirst($pet).' privacy was updated.',
            'route' => $pet === 'nori' ? 'pet-social.pets.nori' : 'pet-social.pets.scout',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createProfileReport(array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $context = $this->profiles->reportContext($target);

        if ($context === null) {
            throw ValidationException::withMessages(['target' => 'This profile is unavailable to report.']);
        }

        $this->state->addProfileReport([
            'target' => $target,
            'reason' => (string) ($data['category'] ?? ''),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return [
            'message' => 'Your report was received privately.',
            'route' => $context['route'],
            'parameters' => $context['route_parameters'],
        ];
    }

    /**
     * @return array{pet: string, conversation: string, location: string}
     */
    private function walkParticipant(string $target): array
    {
        return match ($target) {
            'mochi' => ['pet' => 'Mochi', 'conversation' => 'ari', 'location' => 'Fields Park north gate'],
            'juniper' => ['pet' => 'Juniper', 'conversation' => 'noah', 'location' => 'Sellwood Riverfront trailhead'],
            'scout' => ['pet' => 'Scout', 'conversation' => '', 'location' => 'Laurelhurst Park pond'],
            default => throw ValidationException::withMessages(['target' => 'Choose an available walking companion.']),
        };
    }

    private function requireTarget(string $target): void
    {
        if ($target === '') {
            throw ValidationException::withMessages(['target' => 'Choose an item first.']);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function requireText(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));

        if ($value === '') {
            throw ValidationException::withMessages([$key => 'This field is required.']);
        }

        return $value;
    }
}

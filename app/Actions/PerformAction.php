<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Services\FeedPresenter;
use App\Services\LocaleFormatter;
use App\Services\PreviewService;
use App\Services\ProfilePresenter;
use App\Services\PrototypeState;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PerformAction
{
    public function __construct(
        private readonly PerformPlaceAction $places,
        private readonly PrototypeState $state,
        private readonly PreviewService $preview,
        private readonly ProfilePresenter $profiles,
        private readonly FeedPresenter $feed,
        private readonly PerformConnectionAction $connectionActions,
        private readonly PerformPetFriendAction $petFriendActions,
        private readonly PerformGroupAction $groupActions,
        private readonly PerformEventAction $eventActions,
        private readonly CreatePetProfile $createPetProfile,
        private readonly UpdatePetProfile $updatePetProfile,
        private readonly UpdatePetProfilePrivacy $updatePetProfilePrivacy,
        private readonly UpdateCurrentUserProfile $updateCurrentUserProfile,
        private readonly LocaleFormatter $formatter,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     message: string,
     *     route: string|null,
     *     parameters?: array<string, string>
     * }
     */
    public function handle(User $user, array $data): array
    {
        $action = (string) $data['action'];
        $target = (string) ($data['target'] ?? '');
        $label = (string) ($data['label'] ?? __('messages.action.item'));

        if (Str::contains($action, 'place')) {
            return $this->places->handle($data);
        }

        if ($this->eventActions->supports($action)) {
            return $this->eventActions->handle($data);
        }

        if ($this->connectionActions->supports($action)) {
            return $this->connectionActions->handle($data);
        }

        if ($this->petFriendActions->supports($action)) {
            return $this->petFriendActions->handle($data);
        }

        if ($this->groupActions->supports($action)) {
            return $this->groupActions->handle($data);
        }

        return match ($action) {
            'toggle-follow' => $this->toggle('follows', $target, __('messages.action.followed', ['label' => $label]), __('messages.action.unfollowed', ['label' => $label])),
            'toggle-group' => $this->toggle('groups', $target, __('messages.action.group_joined', ['label' => $label]), __('messages.action.group_left', ['label' => $label])),
            'toggle-paw' => $this->toggle('paws', $target, __('messages.action.paw_sent', ['label' => $label]), __('messages.action.paw_removed', ['label' => $label])),
            'toggle-save' => $this->toggle('saved', $target, __('messages.action.saved', ['label' => $label]), __('messages.action.unsaved', ['label' => $label])),
            'toggle-setting' => $this->toggleSetting($target, $label),
            'toggle-friend' => $this->toggle('friends', $target, __('messages.action.friend_requested', ['label' => $label]), __('messages.action.friend_cancelled', ['label' => $label])),
            'toggle-block' => $this->toggle('blocks', $target, __('messages.action.blocked', ['label' => $label]), __('messages.action.unblocked', ['label' => $label])),
            'mark-all-read' => $this->markAllRead(),
            'create-comment' => $this->createComment($user, $data),
            'create-post' => $this->createPost($user, $data),
            'update-post' => $this->updatePost($user, $data),
            'set-reaction' => $this->setReaction($data),
            'toggle-post-subscription' => $this->toggle('post-subscriptions', $target, __('messages.action.notifications_enabled', ['label' => $label]), __('messages.action.notifications_paused', ['label' => $label])),
            'hide-post' => $this->toggle('hidden-posts', $target, __('messages.action.hidden', ['label' => $label]), __('messages.action.visible', ['label' => $label])),
            'mute-author' => $this->muteAuthor($target),
            'block-post-author' => $this->blockAuthor($target),
            'repost-post' => $this->repostPost($user, $target),
            'archive-post' => $this->movePost($target, 'archived'),
            'restore-post' => $this->movePost($target, 'published'),
            'delete-post' => $this->deletePost($target),
            'create-post-report' => $this->createPostReport($data),
            'create-group' => $this->createGroup($user, $data),
            'create-pet' => $this->createPet($data),
            'update-profile' => $this->updateProfile($user, $data),
            'update-pet' => $this->updatePet($data),
            'update-pet-privacy' => $this->updatePetPrivacy($data),
            'create-profile-report' => $this->createProfileReport($data),
            'share' => $this->share($target, $label),
            default => throw ValidationException::withMessages(['action' => __('messages.this_action_is_unavailable')]),
        };
    }

    /**
     * @return array{message: string, route: null}
     */
    private function toggle(string $collection, string $target, string $enabled, string $disabled): array
    {
        $this->requireTarget($target);
        $isActive = $this->state->toggle($collection, $target);

        return [
            'message' => $isActive ? $enabled : $disabled,
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
            'message' => $isEnabled
                ? __('messages.action.setting_notifications_enabled', ['label' => $label])
                : __('messages.action.setting_notifications_paused', ['label' => $label]),
            'route' => null,
        ];
    }

    /**
     * @return array{message: string, route: null}
     */
    private function markAllRead(): array
    {
        $this->state->markNotificationsRead();

        return ['message' => __('messages.all_notifications_marked_as_read'), 'route' => null];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array{post: string}}
     */
    private function createComment(User $user, array $data): array
    {
        $post = (string) ($data['target'] ?? '');
        $this->requireTarget($post);

        if ($this->preview->postThreadData($post) === null) {
            throw ValidationException::withMessages(['target' => __('messages.this_conversation_is_unavailable')]);
        }

        $now = now();

        $this->state->addComment([
            'id' => 'comment-'.Str::lower((string) Str::ulid()),
            'post' => $post,
            'parent' => (string) ($data['parent'] ?? ''),
            'author' => $user->name,
            'pet' => '',
            'initials' => $this->initials($user->name),
            'tone' => 'mint',
            'body' => $this->requireText($data, 'body'),
            'time' => $this->formatter->time($now),
            'datetime' => $now->toAtomString(),
            'mine' => true,
        ]);

        return [
            'message' => __('messages.your_reply_joined_the_conversation'),
            'route' => 'posts.show',
            'parameters' => ['post' => $post],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function createPost(User $user, array $data): array
    {
        $key = 'created-post-'.Str::lower((string) Str::ulid());
        $status = (string) ($data['intent'] ?? 'published');
        $now = now()->toAtomString();

        $this->state->addPost([
            'key' => $key,
            ...$this->postValues($user, $data),
            'status' => $status,
            'original_key' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'message' => $status === 'draft'
                ? __('messages.draft_saved_privately')
                : __('messages.your_publication_is_live'),
            'route' => 'home',
            'parameters' => ['feed' => $status === 'draft' ? 'drafts' : 'home'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function updatePost(User $user, array $data): array
    {
        $target = (string) ($data['target'] ?? '');
        $status = (string) ($data['intent'] ?? 'published');

        if (! $this->state->updatePost($target, [
            ...$this->postValues($user, $data),
            'status' => $status,
        ])) {
            throw ValidationException::withMessages(['target' => __('messages.this_publication_cannot_be_edited')]);
        }

        return [
            'message' => $status === 'draft' ? __('messages.changes_saved_as_a_draft') : __('messages.publication_updated'),
            'route' => 'home',
            'parameters' => ['feed' => $status === 'draft' ? 'drafts' : 'home'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function postValues(User $user, array $data): array
    {
        $actor = $user->socialActor()->firstOrFail();

        return [
            'identity' => $actor->actor_key,
            'identity_type' => 'user',
            'author' => $user->name,
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
            throw ValidationException::withMessages(['reaction' => __('messages.choose_an_available_reaction')]);
        }

        $selected = $this->state->setReaction($target, $reaction);

        return [
            'message' => $selected === null ? __('messages.reaction_removed') : __('messages.reaction_updated'),
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
            throw ValidationException::withMessages(['target' => __('messages.this_author_is_unavailable')]);
        }

        $muted = $this->state->toggle('muted-authors', (string) $post['author_key']);

        return [
            'message' => $muted
                ? __('messages.action.author_muted', ['author' => $post['author']])
                : __('messages.action.author_visible', ['author' => $post['author']]),
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
            throw ValidationException::withMessages(['target' => __('messages.this_author_is_unavailable')]);
        }

        $blocked = $this->state->toggle('blocked-authors', (string) $post['author_key']);

        return [
            'message' => $blocked
                ? __('messages.action.author_blocked', ['author' => $post['author']])
                : __('messages.action.author_unblocked', ['author' => $post['author']]),
            'route' => null,
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function repostPost(User $user, string $target): array
    {
        $post = $this->feed->post($target);

        if ($post === null || ($post['status'] ?? 'published') !== 'published') {
            throw ValidationException::withMessages(['target' => __('messages.this_publication_cannot_be_reposted')]);
        }

        $now = now()->toAtomString();

        $this->state->addPost([
            'key' => 'created-post-'.Str::lower((string) Str::ulid()),
            'identity' => $user->socialActor()->value('actor_key'),
            'identity_type' => 'user',
            'author' => $user->name,
            'format' => 'repost',
            'title' => '',
            'body' => __('messages.sharing_this_with_my_circle'),
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
            'message' => __('messages.repost_added_to_your_feed'),
            'route' => 'home',
            'parameters' => ['feed' => 'home'],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function movePost(string $target, string $status): array
    {
        if (! $this->state->movePost($target, $status)) {
            throw ValidationException::withMessages(['target' => __('messages.this_publication_cannot_be_moved')]);
        }

        return [
            'message' => $status === 'archived' ? __('messages.publication_moved_to_archive') : __('messages.publication_restored'),
            'route' => 'home',
            'parameters' => ['feed' => $status === 'archived' ? 'archive' : 'home'],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array<string, string>}
     */
    private function deletePost(string $target): array
    {
        if (! $this->state->deletePost($target)) {
            throw ValidationException::withMessages(['target' => __('messages.this_publication_cannot_be_deleted')]);
        }

        return [
            'message' => __('messages.publication_deleted_from_this_prototype'),
            'route' => 'home',
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
            throw ValidationException::withMessages(['target' => __('messages.this_publication_is_unavailable_to_report')]);
        }

        $this->state->addPostReport([
            'target' => $target,
            'reason' => (string) ($data['category'] ?? ''),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return [
            'message' => __('messages.your_private_report_was_received'),
            'route' => $context['route'],
            'parameters' => $context['route_parameters'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function createGroup(User $user, array $data): array
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
            'pet_identity' => (string) ($data['pet_identity'] ?? $user->actor_key),
            'posting_policy' => (string) ($data['posting_policy'] ?? 'members'),
        ]);

        return [
            'message' => __('messages.your_new_group_is_ready_in_the_directory'),
            'route' => 'groups.index',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string, parameters: array{petProfile: string}}
     */
    private function createPet(array $data): array
    {
        $profile = $this->createPetProfile->handle($data);

        return [
            'message' => __('messages.your_pet_was_added_to_brand'),
            'route' => 'pets.manage.show',
            'parameters' => ['petProfile' => $profile->profile_key],
        ];
    }

    /**
     * @return array{message: string, route: string, parameters: array{target: string}}
     */
    private function share(string $target, string $label): array
    {
        $this->requireTarget($target);

        if ($this->preview->shareData($target) === null) {
            throw ValidationException::withMessages(['target' => __('messages.this_item_is_unavailable_to_share')]);
        }

        return [
            'message' => __('messages.action.ready_to_share', ['label' => $label]),
            'route' => 'share.show',
            'parameters' => ['target' => $target],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function updateProfile(User $user, array $data): array
    {
        $updated = $this->updateCurrentUserProfile->handle($user, $data);
        $actor = $updated->socialActor()->firstOrFail();

        return [
            'message' => __('messages.your_profile_was_updated'),
            'route' => 'members.show',
            'parameters' => ['socialActor' => $actor->actor_key],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function updatePet(array $data): array
    {
        $pet = (string) ($data['target'] ?? '');
        $profile = $this->updatePetProfile->handle($pet, $data);

        return [
            'message' => __('messages.pet_profile_updated', ['pet' => $profile->name]),
            'route' => 'pets.manage.show',
            'parameters' => ['petProfile' => $profile->profile_key],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string}
     */
    private function updatePetPrivacy(array $data): array
    {
        $pet = (string) ($data['target'] ?? '');
        $profile = $this->updatePetProfilePrivacy->handle($pet, $data);

        return [
            'message' => __('messages.pet_profile_privacy_updated', ['pet' => $profile->name]),
            'route' => 'pets.manage.show',
            'parameters' => ['petProfile' => $profile->profile_key],
        ];
    }

    private function initials(string $name): string
    {
        return collect(preg_split('/\s+/u', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
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
            throw ValidationException::withMessages(['target' => __('messages.this_profile_is_unavailable_to_report')]);
        }

        $this->state->addProfileReport([
            'target' => $target,
            'reason' => (string) ($data['category'] ?? ''),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return [
            'message' => __('messages.your_report_was_received_privately'),
            'route' => $context['route'],
            'parameters' => $context['route_parameters'],
        ];
    }

    private function requireTarget(string $target): void
    {
        if ($target === '') {
            throw ValidationException::withMessages(['target' => __('messages.choose_an_item_first')]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function requireText(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));

        if ($value === '') {
            throw ValidationException::withMessages([$key => __('messages.this_field_is_required')]);
        }

        return $value;
    }
}

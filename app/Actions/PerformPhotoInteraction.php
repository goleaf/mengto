<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Services\FeedPresenter;
use App\Services\PhotoInteractionState;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Validation\ValidationException;

final class PerformPhotoInteraction
{
    public function __construct(
        private readonly FeedPresenter $feed,
        private readonly PhotoInteractionState $state,
        private readonly AuthFactory $auth,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string}
     */
    public function handle(array $data): array
    {
        $photoKey = (string) $data['photo'];
        $photo = $this->feed->photo($photoKey);

        if ($photo === null) {
            throw ValidationException::withMessages([
                'photo' => __('messages.photo_unavailable'),
            ]);
        }

        return match ((string) $data['action']) {
            'set-reaction' => $this->setReaction($photo, (string) $data['reaction']),
            'create-comment' => $this->createComment(
                $photo,
                (string) $data['body'],
                (string) $data['idempotency_key'],
            ),
            default => throw ValidationException::withMessages([
                'action' => __('messages.this_action_is_unavailable_c64fa3888d'),
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $photo
     * @return array{message: string}
     */
    private function setReaction(array $photo, string $reaction): array
    {
        if (! array_key_exists($reaction, $photo['reaction_options'])) {
            throw ValidationException::withMessages([
                'reaction' => __('messages.choose_an_available_reaction_c8a1ac8cff'),
            ]);
        }

        $selected = $this->state->setReaction($photo, $reaction);

        return [
            'message' => $selected === null
                ? __('messages.photo_reaction_removed')
                : __('messages.photo_reaction_updated'),
        ];
    }

    /**
     * @return array{message: string}
     */
    /**
     * @param  array<string, mixed>  $photo
     */
    private function createComment(array $photo, string $body, string $idempotencyKey): array
    {
        $user = $this->auth->guard()->user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        $this->state->addComment($photo, $body, $idempotencyKey);

        return ['message' => __('messages.photo_comment_added')];
    }
}

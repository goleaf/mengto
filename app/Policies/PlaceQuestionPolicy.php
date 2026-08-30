<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlaceQuestion;
use App\Models\PlaceQuestionAnswer;
use App\Models\User;

final readonly class PlaceQuestionPolicy
{
    public function __construct(private PlacePolicy $places) {}

    public function view(?User $user, PlaceQuestion $question): bool
    {
        return $this->places->view($user, $question->place);
    }

    public function answer(?User $user, PlaceQuestion $question): bool
    {
        return $user?->hasVerifiedEmail() === true
            && $this->places->update($user, $question->place);
    }

    public function updateAnswer(?User $user, PlaceQuestion $question, PlaceQuestionAnswer $answer): bool
    {
        return $user?->id === $answer->author_user_id
            && $user->hasVerifiedEmail()
            && $this->places->update($user, $question->place);
    }

    public function close(?User $user, PlaceQuestion $question): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && ($user->id === $question->author_user_id || $this->places->update($user, $question->place));
    }

    public function reopen(?User $user, PlaceQuestion $question): bool
    {
        return $this->close($user, $question);
    }

    public function moderate(?User $user, PlaceQuestion $question): bool
    {
        return $user?->isAdministrator() === true;
    }

    public function report(?User $user, PlaceQuestion $question): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $this->view($user, $question);
    }

    public function delete(?User $user, PlaceQuestion $question): bool
    {
        return false;
    }

    public function restore(?User $user, PlaceQuestion $question): bool
    {
        return false;
    }

    public function forceDelete(?User $user, PlaceQuestion $question): bool
    {
        return false;
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PlaceQuestion;
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

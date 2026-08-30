<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PlaceReviewModerationStatus;
use App\Models\Place;
use App\Models\PlaceReview;
use App\Models\User;

final readonly class PlaceReviewPolicy
{
    public function __construct(private PlacePolicy $places) {}

    public function view(?User $user, PlaceReview $review): bool
    {
        if (! $this->places->view($user, $review->place)) {
            return false;
        }

        return $review->moderation_status === PlaceReviewModerationStatus::Published
            || $review->author_user_id === $user?->id
            || $user?->isAdministrator() === true;
    }

    public function create(?User $user, Place $place): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $this->places->view($user, $place);
    }

    public function update(?User $user, PlaceReview $review): bool
    {
        return $this->isAuthor($user, $review)
            && $review->deleted_at === null
            && $review->moderation_status !== PlaceReviewModerationStatus::Removed;
    }

    public function delete(?User $user, PlaceReview $review): bool
    {
        return $this->isAuthor($user, $review) && $review->deleted_at === null;
    }

    public function restore(?User $user, PlaceReview $review): bool
    {
        return $this->isAuthor($user, $review)
            && $review->deleted_at !== null
            && $review->moderation_status !== PlaceReviewModerationStatus::Removed;
    }

    public function moderate(?User $user, PlaceReview $review): bool
    {
        return $user?->isAdministrator() === true;
    }

    private function isAuthor(?User $user, PlaceReview $review): bool
    {
        return $user?->isActive() === true && $review->author_user_id === $user->id;
    }
}

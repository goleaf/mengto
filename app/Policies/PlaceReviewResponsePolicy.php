<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PlaceManagementScope;
use App\Enums\PlaceReviewModerationStatus;
use App\Models\Place;
use App\Models\PlaceManagerAuthority;
use App\Models\PlaceReview;
use App\Models\PlaceReviewResponse;
use App\Models\User;

final readonly class PlaceReviewResponsePolicy
{
    public function __construct(private PlacePolicy $places) {}

    public function view(?User $user, PlaceReviewResponse $response): bool
    {
        return $this->places->view($user, $response->review->place);
    }

    public function respond(?User $user, PlaceReview $review): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $review->deleted_at === null
            && $review->moderation_status === PlaceReviewModerationStatus::Published
            && $this->hasVerifiedManagementScope($user, $review->place);
    }

    public function update(?User $user, PlaceReviewResponse $response): bool
    {
        return $user?->id === $response->author_user_id
            && $this->respond($user, $response->review);
    }

    private function hasVerifiedManagementScope(User $user, Place $place): bool
    {
        if ($this->places->update($user, $place)) {
            return true;
        }

        return PlaceManagerAuthority::query()
            ->active()
            ->where('place_id', $place->id)
            ->where('granted_to_user_id', $user->id)
            ->whereHas('scopes', static fn ($scopes) => $scopes->where('scope', PlaceManagementScope::OfficialResponses->value))
            ->exists();
    }
}

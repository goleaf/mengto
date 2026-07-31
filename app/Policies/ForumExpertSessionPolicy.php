<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumExpertSessionStatus;
use App\Models\ForumExpertSession;
use App\Models\User;
use App\Services\ForumExpertSessionHostEligibility;

final readonly class ForumExpertSessionPolicy
{
    public function __construct(
        private ForumExpertSessionHostEligibility $eligibility,
    ) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ForumExpertSession $session): bool
    {
        if ($user?->isAdministrator() === true || $user?->id === $session->created_by_user_id) {
            return true;
        }

        return $session->status === ForumExpertSessionStatus::Published
            && $session->archived_at === null;
    }

    public function create(?User $user): bool
    {
        return $user !== null && $this->eligibility->hasAnyProfile($user);
    }

    public function update(?User $user, ForumExpertSession $session): bool
    {
        if ($user?->isAdministrator() === true) {
            return $session->status !== ForumExpertSessionStatus::Archived;
        }

        return $user !== null
            && $session->isHost($user)
            && $session->status !== ForumExpertSessionStatus::Archived
            && $this->eligibility->allows(
                $user,
                $session->expertProfile,
                $session->professional_scope,
                $session->jurisdiction,
            );
    }

    public function submitQuestion(?User $user, ForumExpertSession $session): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && ! $session->isHost($user)
            && $this->view($user, $session)
            && $session->acceptsQuestions();
    }

    public function moderate(?User $user, ForumExpertSession $session): bool
    {
        return $this->update($user, $session);
    }

    public function answer(?User $user, ForumExpertSession $session): bool
    {
        return $user !== null
            && $session->isHost($user)
            && $session->status === ForumExpertSessionStatus::Published
            && $this->eligibility->allows(
                $user,
                $session->expertProfile,
                $session->professional_scope,
                $session->jurisdiction,
            );
    }

    public function archive(?User $user, ForumExpertSession $session): bool
    {
        return $this->update($user, $session);
    }

    public function report(?User $user, ForumExpertSession $session): bool
    {
        return $user?->isActive() === true && $this->view($user, $session);
    }

    public function delete(?User $user, ForumExpertSession $session): bool
    {
        return false;
    }

    public function restore(?User $user, ForumExpertSession $session): bool
    {
        return false;
    }

    public function forceDelete(?User $user, ForumExpertSession $session): bool
    {
        return false;
    }
}

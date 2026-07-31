<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumMentorshipState;
use App\Models\ForumMentorship;
use App\Models\User;

final class ForumMentorshipPolicy
{
    public function view(User $user, ForumMentorship $mentorship): bool
    {
        return $user->isActive()
            && ($user->isAdministrator() || $mentorship->isParticipant($user));
    }

    public function respond(User $user, ForumMentorship $mentorship): bool
    {
        return $user->isActive()
            && $mentorship->mentor_user_id === $user->id
            && $mentorship->state === ForumMentorshipState::Requested;
    }

    public function message(User $user, ForumMentorship $mentorship): bool
    {
        return $user->isActive()
            && $mentorship->isParticipant($user)
            && $mentorship->state->allowsMessages();
    }

    public function end(User $user, ForumMentorship $mentorship): bool
    {
        return $user->isActive()
            && $mentorship->isParticipant($user)
            && (
                $mentorship->state === ForumMentorshipState::Active
                || (
                    $mentorship->state === ForumMentorshipState::Requested
                    && $mentorship->mentee_user_id === $user->id
                )
            );
    }

    public function feedback(User $user, ForumMentorship $mentorship): bool
    {
        return $user->isActive()
            && $mentorship->isParticipant($user)
            && in_array($mentorship->state, [
                ForumMentorshipState::Completed,
                ForumMentorshipState::Ended,
            ], true);
    }

    public function validateCompletion(User $user, ForumMentorship $mentorship): bool
    {
        return $user->isAdministrator()
            && ! $mentorship->isParticipant($user)
            && $mentorship->state === ForumMentorshipState::Completed;
    }

    public function delete(User $user, ForumMentorship $mentorship): bool
    {
        return false;
    }
}

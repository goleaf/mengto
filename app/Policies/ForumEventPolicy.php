<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventVisibility;
use App\Models\ForumEvent;
use App\Models\User;

final class ForumEventPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ForumEvent $event): bool
    {
        if ($user?->isAdministrator() === true
            || ($user !== null && $event->isOrganizer($user))
        ) {
            return true;
        }

        if ($event->archived_at !== null || $event->status === ForumEventStatus::Archived) {
            return false;
        }

        return match ($event->visibility) {
            ForumEventVisibility::Public => true,
            ForumEventVisibility::Members => $user?->isActive() === true,
            ForumEventVisibility::Group => $user?->isActive() === true
                && $event->group()
                    ->where(function ($groups) use ($user): void {
                        $groups
                            ->where('owner_user_id', $user->id)
                            ->orWhereHas('memberships', function ($memberships) use ($user): void {
                                $memberships
                                    ->where('user_id', $user->id)
                                    ->where('state', 'active');
                            });
                    })
                    ->exists(),
            ForumEventVisibility::Private => $user?->isActive() === true
                && $event->invitations()
                    ->where('invited_user_id', $user->id)
                    ->where('status', ForumEventInvitationStatus::Accepted->value)
                    ->where('expires_at', '>', now())
                    ->exists(),
        };
    }

    public function create(?User $user): bool
    {
        return $user?->isActive() === true && $user->hasVerifiedEmail();
    }

    public function update(?User $user, ForumEvent $event): bool
    {
        return $user?->isActive() === true
            && ($user->isAdministrator() || $event->isOrganizer($user))
            && $event->status !== ForumEventStatus::Archived;
    }

    public function viewAccessDetails(?User $user, ForumEvent $event): bool
    {
        return $user?->isActive() === true
            && $event->canDiscloseAccessTo($user);
    }

    public function register(?User $user, ForumEvent $event): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && $this->view($user, $event)
            && ! $event->isOrganizer($user)
            && $event->status->acceptsRegistration()
            && $event->starts_at->isFuture();
    }

    public function manageRegistrations(?User $user, ForumEvent $event): bool
    {
        return $this->update($user, $event);
    }

    public function invite(?User $user, ForumEvent $event): bool
    {
        return $this->update($user, $event)
            && $event->status === ForumEventStatus::Scheduled;
    }

    public function respondToInvitation(?User $user, ForumEvent $event): bool
    {
        return $user?->isActive() === true
            && $event->invitations()
                ->where('invited_user_id', $user->id)
                ->where('status', ForumEventInvitationStatus::Pending->value)
                ->where('expires_at', '>', now())
                ->exists();
    }

    public function publishUpdate(?User $user, ForumEvent $event): bool
    {
        return $this->update($user, $event);
    }

    public function sendMessage(?User $user, ForumEvent $event): bool
    {
        if ($user?->isActive() !== true) {
            return false;
        }

        if ($user->isAdministrator() || $event->isOrganizer($user)) {
            return true;
        }

        return in_array(
            $event->registrationFor($user)?->status,
            [
                ForumEventRegistrationStatus::Confirmed,
                ForumEventRegistrationStatus::CheckedIn,
            ],
            true,
        );
    }

    public function review(?User $user, ForumEvent $event): bool
    {
        if ($user?->isActive() !== true || ! $event->hasEnded()) {
            return false;
        }

        return in_array(
            $event->registrationFor($user)?->status,
            [
                ForumEventRegistrationStatus::Confirmed,
                ForumEventRegistrationStatus::CheckedIn,
            ],
            true,
        );
    }

    public function report(?User $user, ForumEvent $event): bool
    {
        return $user?->isActive() === true && $this->view($user, $event);
    }

    public function delete(?User $user, ForumEvent $event): bool
    {
        return false;
    }

    public function restore(?User $user, ForumEvent $event): bool
    {
        return false;
    }

    public function forceDelete(?User $user, ForumEvent $event): bool
    {
        return false;
    }
}

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventTeamRole;
use App\Enums\ForumEventVisibility;
use App\Enums\ForumGroupStatus;
use App\Enums\OrganizationRestrictionCapability;
use App\Models\ForumEvent;
use App\Models\User;
use App\Services\ForumModerationGuard;
use App\Services\SocialBlockService;

final class ForumEventPolicy
{
    public function __construct(
        private readonly SocialBlockService $blocks,
        private readonly ForumModerationGuard $moderation,
        private readonly ForumGroupPolicy $groups,
    ) {}

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, ForumEvent $event): bool
    {
        if ($user?->isAdministrator() === true) {
            return true;
        }

        if ($this->moderation->hides($event)) {
            return false;
        }

        if ($user !== null && $this->canManage($user, $event)) {
            return true;
        }

        if ($event->archived_at !== null || $event->status === ForumEventStatus::Archived) {
            return false;
        }

        if (! $event->status->isDiscoverable()
            && $event->status !== ForumEventStatus::Cancelled
        ) {
            return false;
        }

        if ($user !== null && $this->blockedFromOrganizer($user, $event)) {
            return false;
        }

        if ($event->status === ForumEventStatus::Cancelled
            && $user?->isActive() === true
            && $event->registrations()
                ->where('user_id', $user->id)
                ->where('status', ForumEventRegistrationStatus::Cancelled->value)
                ->where('cancellation_reason_code', 'event-cancelled')
                ->exists()
        ) {
            return true;
        }

        return match ($event->visibility) {
            ForumEventVisibility::Public,
            ForumEventVisibility::Unlisted => true,
            ForumEventVisibility::Members => $user?->isActive() === true,
            ForumEventVisibility::Organization => $user?->isActive() === true
                && $event->responsibleOrganization?->membershipFor($user) !== null,
            ForumEventVisibility::Group => $user?->isActive() === true
                && $event->group()
                    ->where('status', ForumGroupStatus::Active->value)
                    ->whereNull('archived_at')
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
            ForumEventVisibility::Private,
            ForumEventVisibility::Invitation => $user?->isActive() === true
                && $this->hasOrganizationMembership($user, $event)
                && ($event->invitations()
                    ->where('invited_user_id', $user->id)
                    ->whereIn('status', [
                        ForumEventInvitationStatus::Pending->value,
                        ForumEventInvitationStatus::Accepted->value,
                    ])
                    ->where('expires_at', '>', now())
                    ->exists()
                    || $event->registrations()
                        ->where('user_id', $user->id)
                        ->whereIn('status', ForumEvent::participantAccessStatusValues())
                        ->exists()),
        };
    }

    public function create(?User $user): bool
    {
        return $this->canMutate($user);
    }

    public function update(?User $user, ForumEvent $event): bool
    {
        return $this->canMutate($user)
            && ($event->status !== ForumEventStatus::SafetySuspended || $user->isAdministrator())
            && ($user->isAdministrator() || $this->canManage($user, $event))
            && $event->status !== ForumEventStatus::Archived;
    }

    public function cancel(?User $user, ForumEvent $event): bool
    {
        return $this->canMutate($user)
            && ($user->isAdministrator() || $this->canManage($user, $event))
            && ! in_array($event->status, [
                ForumEventStatus::Draft,
                ForumEventStatus::Incomplete,
                ForumEventStatus::Completed,
                ForumEventStatus::Archived,
            ], true);
    }

    public function publish(?User $user, ForumEvent $event): bool
    {
        return $this->update($user, $event)
            && $this->organizationAllows(
                $event,
                OrganizationRestrictionCapability::PublishEvents,
            )
            && in_array($event->status, [
                ForumEventStatus::Draft,
                ForumEventStatus::Incomplete,
                ForumEventStatus::Scheduled,
            ], true);
    }

    public function viewAccessDetails(?User $user, ForumEvent $event): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && ! $this->moderation->userIsSuspended($user)
            && $this->view($user, $event)
            && ($event->canDiscloseAccessTo($user)
                || $this->hasTeamRole($user, $event, [
                    ForumEventTeamRole::Owner,
                    ForumEventTeamRole::Administrator,
                    ForumEventTeamRole::PrimaryOrganizer,
                    ForumEventTeamRole::CoOrganizer,
                    ForumEventTeamRole::CheckInOperator,
                    ForumEventTeamRole::SafetyLead,
                    ForumEventTeamRole::WelfareOfficer,
                    ForumEventTeamRole::MedicalContact,
                    ForumEventTeamRole::RouteLeader,
                ]));
    }

    public function register(?User $user, ForumEvent $event): bool
    {
        return $this->canMutate($user)
            && $this->view($user, $event)
            && ! $event->isOrganizer($user)
            && ($event->organizer === null
                || ($event->organizer->isActive()
                    && ! $this->moderation->userIsSuspended($event->organizer)))
            && $this->organizationAllows(
                $event,
                OrganizationRestrictionCapability::AcceptRegistrations,
            )
            && $event->status->acceptsRegistration()
            && $event->starts_at->isFuture();
    }

    public function manageRegistrations(?User $user, ForumEvent $event): bool
    {
        if (! $this->canMutate($user)) {
            return false;
        }

        $isEmergencyStaff = $this->hasAssignedTeamRole($user, $event, [
            ForumEventTeamRole::SafetyLead,
            ForumEventTeamRole::WelfareOfficer,
            ForumEventTeamRole::MedicalContact,
        ]);

        return ($user->isAdministrator() || $this->hasTeamRole($user, $event, [
            ForumEventTeamRole::Owner,
            ForumEventTeamRole::Administrator,
            ForumEventTeamRole::PrimaryOrganizer,
            ForumEventTeamRole::CoOrganizer,
            ForumEventTeamRole::RegistrationManager,
            ForumEventTeamRole::CheckInOperator,
            ForumEventTeamRole::SafetyLead,
            ForumEventTeamRole::WelfareOfficer,
        ]))
            && ($isEmergencyStaff || $this->organizationAllows(
                $event,
                OrganizationRestrictionCapability::AccessParticipantData,
            ));
    }

    public function manageTeam(?User $user, ForumEvent $event): bool
    {
        return $this->canMutate($user)
            && ($user->isAdministrator()
                || $this->hasTeamRole($user, $event, [
                    ForumEventTeamRole::Owner,
                    ForumEventTeamRole::Administrator,
                ]));
    }

    public function manageSchedule(?User $user, ForumEvent $event): bool
    {
        return $this->canMutate($user)
            && $event->status !== ForumEventStatus::Archived
            && ($user->isAdministrator() || $this->hasTeamRole($user, $event, [
                ForumEventTeamRole::Owner,
                ForumEventTeamRole::Administrator,
                ForumEventTeamRole::PrimaryOrganizer,
                ForumEventTeamRole::CoOrganizer,
                ForumEventTeamRole::ScheduleManager,
            ]));
    }

    public function overrideScheduleConflict(?User $user, ForumEvent $event): bool
    {
        return $this->canMutate($user)
            && ($user->isAdministrator()
                || $this->hasTeamRole($user, $event, [
                    ForumEventTeamRole::Owner,
                    ForumEventTeamRole::Administrator,
                    ForumEventTeamRole::PrimaryOrganizer,
                ]));
    }

    public function transition(
        ?User $user,
        ForumEvent $event,
        ForumEventStatus $next,
    ): bool {
        if ($event->status !== ForumEventStatus::SafetySuspended
            && $this->update($user, $event)
            && ($next !== ForumEventStatus::Published || $this->organizationAllows(
                $event,
                OrganizationRestrictionCapability::PublishEvents,
            ))
        ) {
            return true;
        }

        return $next === ForumEventStatus::SafetySuspended
            && $this->canMutate($user)
            && $this->hasTeamRole($user, $event, [
                ForumEventTeamRole::SafetyLead,
                ForumEventTeamRole::WelfareOfficer,
            ]);
    }

    public function invite(?User $user, ForumEvent $event): bool
    {
        return $this->update($user, $event)
            && $event->status === ForumEventStatus::Scheduled
            && $this->organizationAllows(
                $event,
                OrganizationRestrictionCapability::CreateInvitations,
            );
    }

    public function checkIn(?User $user, ForumEvent $event): bool
    {
        return $this->manageRegistrations($user, $event)
            && $this->organizationAllows(
                $event,
                OrganizationRestrictionCapability::RunCheckIn,
            );
    }

    public function respondToInvitation(?User $user, ForumEvent $event): bool
    {
        return $this->canMutate($user)
            && $this->view($user, $event)
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
        if (! $this->canMutate($user)) {
            return false;
        }

        if (! $this->view($user, $event)) {
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
                ForumEventRegistrationStatus::PartiallyCheckedIn,
                ForumEventRegistrationStatus::Attended,
            ],
            true,
        );
    }

    public function review(?User $user, ForumEvent $event): bool
    {
        if (! $this->canMutate($user)
            || ! $this->view($user, $event)
            || ! $event->hasEnded()
        ) {
            return false;
        }

        return in_array(
            $event->registrationFor($user)?->status,
            [
                ForumEventRegistrationStatus::Confirmed,
                ForumEventRegistrationStatus::CheckedIn,
                ForumEventRegistrationStatus::PartiallyCheckedIn,
                ForumEventRegistrationStatus::Attended,
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

    /** @param list<ForumEventTeamRole> $roles */
    private function hasTeamRole(User $user, ForumEvent $event, array $roles): bool
    {
        if (! $this->hasOrganizationMembership($user, $event)
            || ! $this->hasGroupAuthority($user, $event)
        ) {
            return false;
        }

        if ($event->isOwner($user) || $event->isOrganizer($user)) {
            return true;
        }

        return $event->teamMemberships()
            ->active()
            ->where('user_id', $user->id)
            ->whereIn('role', array_map(
                static fn (ForumEventTeamRole $role): string => $role->value,
                $roles,
            ))
            ->exists();
    }

    private function canManage(User $user, ForumEvent $event): bool
    {
        return $this->hasTeamRole($user, $event, [
            ForumEventTeamRole::Owner,
            ForumEventTeamRole::Administrator,
            ForumEventTeamRole::PrimaryOrganizer,
            ForumEventTeamRole::CoOrganizer,
        ]);
    }

    /** @param list<ForumEventTeamRole> $roles */
    private function hasAssignedTeamRole(User $user, ForumEvent $event, array $roles): bool
    {
        if (! $this->hasOrganizationMembership($user, $event)
            || ! $this->hasGroupAuthority($user, $event)
        ) {
            return false;
        }

        return $event->teamMemberships()
            ->active()
            ->where('user_id', $user->id)
            ->whereIn('role', array_map(
                static fn (ForumEventTeamRole $role): string => $role->value,
                $roles,
            ))
            ->exists();
    }

    private function organizationAllows(
        ForumEvent $event,
        OrganizationRestrictionCapability $capability,
    ): bool {
        return $event->responsibleOrganization === null
            || $event->responsibleOrganization->allows($capability);
    }

    private function hasOrganizationMembership(User $user, ForumEvent $event): bool
    {
        return $event->responsibleOrganization === null
            || $event->responsibleOrganization->membershipFor($user) !== null;
    }

    private function hasGroupAuthority(User $user, ForumEvent $event): bool
    {
        if ($event->forum_group_id === null) {
            return true;
        }

        $group = $event->group()->first();

        return $group !== null && $this->groups->createContent($user, $group);
    }

    private function canMutate(?User $user): bool
    {
        return $user?->isActive() === true
            && $user->hasVerifiedEmail()
            && ! $this->moderation->userIsSuspended($user);
    }

    private function blockedFromOrganizer(User $user, ForumEvent $event): bool
    {
        if ($event->organizer_user_id === null || $event->organizer_user_id === $user->id) {
            return false;
        }

        return $this->blocks->accountBlockedBetween(
            [$user->id],
            [$event->organizer_user_id],
        );
    }
}

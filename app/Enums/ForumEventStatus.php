<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventStatus: string
{
    case Draft = 'draft';
    case Incomplete = 'incomplete';
    case AwaitingOrganizerVerification = 'awaiting_organizer_verification';
    case AwaitingOrganizationApproval = 'awaiting_organization_approval';
    case AwaitingVenueConfirmation = 'awaiting_venue_confirmation';
    case AwaitingSafetyReview = 'awaiting_safety_review';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case RegistrationScheduled = 'registration_scheduled';
    case RegistrationOpen = 'registration_open';
    case RegistrationPaused = 'registration_paused';
    case RegistrationClosed = 'registration_closed';
    case Full = 'full';
    case WaitlistOnly = 'waitlist_only';
    case Postponed = 'postponed';
    case Moved = 'moved';
    case FormatChanged = 'format_changed';
    case Cancelled = 'cancelled';
    case SafetySuspended = 'safety_suspended';
    case Live = 'live';
    case Completed = 'completed';
    case ResultsPending = 'results_pending';
    case Archived = 'archived';
    case Rejected = 'rejected';
    case RetentionDeletionPending = 'retention_deletion_pending';

    public function label(): string
    {
        return __('forum_events.statuses.'.$this->value);
    }

    public function acceptsRegistration(): bool
    {
        return in_array($this, [self::Scheduled, self::RegistrationOpen], true);
    }

    public function isDiscoverable(): bool
    {
        return in_array($this, [
            self::Scheduled,
            self::Published,
            self::RegistrationScheduled,
            self::RegistrationOpen,
            self::RegistrationPaused,
            self::RegistrationClosed,
            self::Full,
            self::WaitlistOnly,
            self::Postponed,
            self::Moved,
            self::FormatChanged,
            self::Live,
            self::Completed,
            self::ResultsPending,
            self::Archived,
        ], true);
    }

    public function canTransitionTo(self $next): bool
    {
        if ($this === $next) {
            return true;
        }

        return in_array($next, match ($this) {
            self::Draft => [self::Incomplete, self::AwaitingOrganizerVerification, self::Scheduled, self::Rejected],
            self::Incomplete => [self::Draft, self::AwaitingOrganizerVerification, self::Rejected],
            self::AwaitingOrganizerVerification => [self::AwaitingOrganizationApproval, self::AwaitingVenueConfirmation, self::AwaitingSafetyReview, self::Scheduled, self::Rejected],
            self::AwaitingOrganizationApproval => [self::AwaitingVenueConfirmation, self::AwaitingSafetyReview, self::Scheduled, self::Rejected],
            self::AwaitingVenueConfirmation => [self::AwaitingSafetyReview, self::Scheduled, self::Rejected],
            self::AwaitingSafetyReview => [self::Scheduled, self::Rejected, self::SafetySuspended],
            self::Scheduled => [self::Published, self::RegistrationScheduled, self::RegistrationOpen, self::Postponed, self::Moved, self::FormatChanged, self::Cancelled, self::SafetySuspended, self::Live, self::Completed],
            self::Published => [self::RegistrationScheduled, self::RegistrationOpen, self::RegistrationClosed, self::Postponed, self::Moved, self::FormatChanged, self::Cancelled, self::SafetySuspended, self::Live, self::Completed],
            self::RegistrationScheduled => [self::RegistrationOpen, self::RegistrationPaused, self::RegistrationClosed, self::Postponed, self::Cancelled, self::SafetySuspended],
            self::RegistrationOpen => [self::RegistrationPaused, self::RegistrationClosed, self::Full, self::WaitlistOnly, self::Postponed, self::Moved, self::FormatChanged, self::Cancelled, self::SafetySuspended, self::Live],
            self::RegistrationPaused => [self::RegistrationOpen, self::RegistrationClosed, self::Cancelled, self::SafetySuspended],
            self::RegistrationClosed, self::Full, self::WaitlistOnly => [self::RegistrationOpen, self::Postponed, self::Moved, self::FormatChanged, self::Cancelled, self::SafetySuspended, self::Live, self::Completed],
            self::Postponed => [self::Scheduled, self::RegistrationScheduled, self::RegistrationOpen, self::Moved, self::FormatChanged, self::Cancelled, self::SafetySuspended],
            self::Moved, self::FormatChanged => [self::RegistrationOpen, self::RegistrationClosed, self::Cancelled, self::SafetySuspended, self::Live, self::Completed],
            self::SafetySuspended => [self::Scheduled, self::RegistrationPaused, self::Cancelled, self::Live],
            self::Live => [self::SafetySuspended, self::Cancelled, self::Completed, self::ResultsPending],
            self::Completed => [self::ResultsPending, self::Archived],
            self::ResultsPending => [self::Completed, self::Archived],
            self::Cancelled, self::Rejected => [self::Archived],
            self::Archived => [self::RetentionDeletionPending],
            self::RetentionDeletionPending => [],
        }, true);
    }
}

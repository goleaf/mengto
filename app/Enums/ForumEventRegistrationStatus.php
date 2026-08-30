<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventRegistrationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Incomplete = 'incomplete';
    case Pending = 'pending';
    case DocumentsRequired = 'documents_required';
    case PaymentRequired = 'payment_required';
    case PaymentPending = 'payment_pending';
    case Approved = 'approved';
    case ApprovedWithConditions = 'approved_with_conditions';
    case Confirmed = 'confirmed';
    case Waitlisted = 'waitlisted';
    case Reserved = 'reserved';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case CancelledByOrganizer = 'cancelled_by_organizer';
    case CheckedIn = 'checked_in';
    case PartiallyCheckedIn = 'partially_checked_in';
    case Attended = 'attended';
    case Completed = 'completed';
    case NoShow = 'no_show';
    case Withdrawn = 'withdrawn';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Refunded = 'refunded';
    case SuspendedForSafetyReview = 'suspended_for_safety_review';

    public function label(): string
    {
        return __('forum_events.registration_statuses.'.$this->value);
    }

    public function consumesSeat(): bool
    {
        return $this->consumesCapacity();
    }

    public function consumesCapacity(): bool
    {
        return in_array($this, [
            self::Confirmed,
            self::CheckedIn,
            self::PartiallyCheckedIn,
        ], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Declined,
            self::Cancelled,
            self::CancelledByOrganizer,
            self::Attended,
            self::Completed,
            self::NoShow,
            self::Withdrawn,
            self::Rejected,
            self::Expired,
            self::Refunded,
        ], true);
    }

    public function isActive(): bool
    {
        return ! $this->isTerminal() && $this !== self::Draft;
    }

    public function canTransitionTo(self $next): bool
    {
        if ($this === $next) {
            return true;
        }

        return in_array($next, match ($this) {
            self::Draft => [self::Submitted, self::Withdrawn, self::Expired],
            self::Submitted => [
                self::Incomplete,
                self::DocumentsRequired,
                self::Pending,
                self::Approved,
                self::ApprovedWithConditions,
                self::Waitlisted,
                self::Reserved,
                self::Confirmed,
                self::Rejected,
                self::Withdrawn,
                self::Expired,
                self::SuspendedForSafetyReview,
            ],
            self::Incomplete => [
                self::Submitted,
                self::DocumentsRequired,
                self::Withdrawn,
                self::Expired,
            ],
            self::DocumentsRequired => [
                self::Submitted,
                self::Pending,
                self::Withdrawn,
                self::Expired,
                self::SuspendedForSafetyReview,
            ],
            self::Pending => [
                self::Incomplete,
                self::DocumentsRequired,
                self::Approved,
                self::ApprovedWithConditions,
                self::Waitlisted,
                self::Reserved,
                self::Confirmed,
                self::Rejected,
                self::Declined,
                self::Withdrawn,
                self::Expired,
                self::SuspendedForSafetyReview,
            ],
            self::Approved, self::ApprovedWithConditions => [
                self::Submitted,
                self::DocumentsRequired,
                self::Reserved,
                self::Waitlisted,
                self::Confirmed,
                self::Withdrawn,
                self::CancelledByOrganizer,
                self::Expired,
                self::SuspendedForSafetyReview,
            ],
            self::Waitlisted => [
                self::Pending,
                self::Reserved,
                self::Confirmed,
                self::Withdrawn,
                self::CancelledByOrganizer,
                self::Expired,
                self::SuspendedForSafetyReview,
            ],
            self::Reserved => [
                self::Confirmed,
                self::Waitlisted,
                self::Withdrawn,
                self::CancelledByOrganizer,
                self::Expired,
                self::SuspendedForSafetyReview,
            ],
            self::Confirmed => [
                self::PartiallyCheckedIn,
                self::CheckedIn,
                self::Withdrawn,
                self::CancelledByOrganizer,
                self::NoShow,
                self::SuspendedForSafetyReview,
            ],
            self::SuspendedForSafetyReview => [
                self::Pending,
                self::DocumentsRequired,
                self::ApprovedWithConditions,
                self::Waitlisted,
                self::Reserved,
                self::Confirmed,
                self::Rejected,
                self::Withdrawn,
                self::CancelledByOrganizer,
                self::Expired,
            ],
            self::PartiallyCheckedIn => [self::CheckedIn, self::Attended, self::Completed],
            self::CheckedIn => [self::Attended, self::Completed],
            self::PaymentRequired, self::PaymentPending => [],
            default => [],
        }, true);
    }

    public function canCancel(): bool
    {
        return in_array($this, [
            self::Submitted,
            self::Incomplete,
            self::Pending,
            self::DocumentsRequired,
            self::Confirmed,
            self::Waitlisted,
            self::Approved,
            self::ApprovedWithConditions,
            self::Reserved,
            self::SuspendedForSafetyReview,
        ], true);
    }
}

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
        return in_array($this, [
            self::Confirmed,
            self::CheckedIn,
            self::PartiallyCheckedIn,
            self::Attended,
        ], true);
    }

    public function canCancel(): bool
    {
        return in_array($this, [
            self::Pending,
            self::Confirmed,
            self::Waitlisted,
            self::Approved,
            self::ApprovedWithConditions,
            self::Reserved,
        ], true);
    }
}

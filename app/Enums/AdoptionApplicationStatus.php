<?php

declare(strict_types=1);

namespace App\Enums;

enum AdoptionApplicationStatus: string
{
    case Submitted = 'submitted';
    case Screening = 'screening';
    case HomeCheck = 'home-check';
    case References = 'references';
    case Meeting = 'meeting';
    case Reserved = 'reserved';
    case ContractPending = 'contract-pending';
    case Trial = 'trial';
    case Adopted = 'adopted';
    case FollowUp = 'follow-up';
    case FosterPlaced = 'foster-placed';
    case Transferred = 'transferred';
    case Returned = 'returned';
    case Declined = 'declined';
    case Withdrawn = 'withdrawn';
    case Failed = 'failed';
    case Closed = 'closed';

    public function label(): string
    {
        return __("adoption.application_status.{$this->value}");
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Submitted => [self::Screening, self::Declined, self::Withdrawn],
            self::Screening => [
                self::HomeCheck,
                self::References,
                self::Meeting,
                self::Declined,
                self::Withdrawn,
            ],
            self::HomeCheck => [
                self::References,
                self::Meeting,
                self::Declined,
                self::Withdrawn,
            ],
            self::References => [self::Meeting, self::Declined, self::Withdrawn],
            self::Meeting => [
                self::Reserved,
                self::FosterPlaced,
                self::Declined,
                self::Withdrawn,
            ],
            self::Reserved => [
                self::ContractPending,
                self::FosterPlaced,
                self::Withdrawn,
            ],
            self::ContractPending => [
                self::Trial,
                self::Adopted,
                self::FosterPlaced,
                self::Failed,
            ],
            self::Trial => [self::Adopted, self::Returned, self::Failed],
            self::FosterPlaced => [
                self::Transferred,
                self::Adopted,
                self::Returned,
                self::Closed,
            ],
            self::Transferred => [
                self::FosterPlaced,
                self::Adopted,
                self::Returned,
                self::Closed,
            ],
            self::Adopted => [self::FollowUp, self::Returned, self::Closed],
            self::FollowUp => [self::Returned, self::Closed],
            self::Returned, self::Failed => [self::Screening, self::Closed],
            self::Declined, self::Withdrawn => [self::Closed],
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}

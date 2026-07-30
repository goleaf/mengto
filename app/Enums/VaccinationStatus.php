<?php

namespace App\Enums;

enum VaccinationStatus: string
{
    case Planned = 'planned';
    case Completed = 'completed';
    case BoosterDue = 'booster-due';
    case DueSoon = 'due-soon';
    case Overdue = 'overdue';
    case Deferred = 'deferred';
    case MedicalExemption = 'medical-exemption';
    case Unverified = 'unverified';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planned',
            self::Completed => 'Completed',
            self::BoosterDue => 'Booster due',
            self::DueSoon => 'Due soon',
            self::Overdue => 'Overdue',
            self::Deferred => 'Deferred',
            self::MedicalExemption => 'Medical exemption',
            self::Unverified => 'Document not verified',
        };
    }
}

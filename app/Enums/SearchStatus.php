<?php

declare(strict_types=1);

namespace App\Enums;

enum SearchStatus: string
{
    case Active = 'active';
    case PossibleSighting = 'possible-sighting';
    case PossibleFound = 'possible-found';
    case Safe = 'safe';
    case IdentityConfirmed = 'identity-confirmed';
    case Returned = 'returned';
    case SelfReturned = 'self-returned';
    case Reunited = 'reunited';
    case Paused = 'paused';
    case LongTerm = 'long-term';
    case Closed = 'closed';
    case FalseReport = 'false-report';

    public function label(): string
    {
        return __("lost_found.status.{$this->value}");
    }

    public function isUrgent(): bool
    {
        return in_array($this, [
            self::Active,
            self::PossibleSighting,
            self::PossibleFound,
            self::Paused,
        ], true);
    }

    public function isClosed(): bool
    {
        return in_array($this, [
            self::Returned,
            self::SelfReturned,
            self::Reunited,
            self::Closed,
            self::FalseReport,
        ], true);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Active, self::PossibleSighting => 'danger',
            self::PossibleFound, self::Safe, self::IdentityConfirmed => 'warning',
            self::Returned, self::SelfReturned, self::Reunited => 'success',
            default => 'neutral',
        };
    }
}

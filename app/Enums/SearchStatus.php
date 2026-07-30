<?php

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
    case Paused = 'paused';
    case LongTerm = 'long-term';
    case Closed = 'closed';
    case FalseReport = 'false-report';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active search',
            self::PossibleSighting => 'Possible sighting',
            self::PossibleFound => 'Possibly found',
            self::Safe => 'Animal is safe',
            self::IdentityConfirmed => 'Identity confirmed',
            self::Returned => 'Returned to owner',
            self::SelfReturned => 'Returned home',
            self::Paused => 'Search paused',
            self::LongTerm => 'Long-term search',
            self::Closed => 'Search closed',
            self::FalseReport => 'Incorrect report',
        };
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
            self::Closed,
            self::FalseReport,
        ], true);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Active, self::PossibleSighting => 'danger',
            self::PossibleFound, self::Safe, self::IdentityConfirmed => 'warning',
            self::Returned, self::SelfReturned => 'success',
            default => 'neutral',
        };
    }
}

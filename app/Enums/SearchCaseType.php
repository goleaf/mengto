<?php

declare(strict_types=1);

namespace App\Enums;

enum SearchCaseType: string
{
    case Lost = 'lost';
    case Found = 'found';
    case Sighted = 'sighted';
    case Stolen = 'stolen';

    public function label(): string
    {
        return __("lost_found.type.{$this->value}");
    }

    public function icon(): string
    {
        return match ($this) {
            self::Lost => 'scan-search',
            self::Found => 'shield-check',
            self::Sighted => 'binoculars',
            self::Stolen => 'shield-alert',
        };
    }
}

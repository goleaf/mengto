<?php

namespace App\Enums;

enum SearchCaseType: string
{
    case Lost = 'lost';
    case Found = 'found';

    public function label(): string
    {
        return match ($this) {
            self::Lost => 'Missing pet',
            self::Found => 'Found animal',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Lost => 'scan-search',
            self::Found => 'shield-check',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum PetLifeStage: string
{
    case Newborn = 'newborn';
    case Juvenile = 'juvenile';
    case Young = 'young';
    case Adult = 'adult';
    case Senior = 'senior';
    case Unknown = 'unknown';

    /** @return list<self> */
    public static function derivedStages(): array
    {
        return [
            self::Newborn,
            self::Juvenile,
            self::Young,
            self::Adult,
            self::Senior,
        ];
    }
}

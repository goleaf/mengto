<?php

namespace App\Enums;

enum MedicalSourceType: string
{
    case Owner = 'owner';
    case Clinic = 'clinic';
    case Veterinarian = 'veterinarian';
    case Laboratory = 'laboratory';
    case Shelter = 'shelter';
    case Device = 'device';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Added by owner',
            self::Clinic => 'Added by clinic',
            self::Veterinarian => 'Added by veterinarian',
            self::Laboratory => 'Added by laboratory',
            self::Shelter => 'Added by shelter',
            self::Device => 'Collected by device',
            self::Import => 'Imported record',
        };
    }
}

<?php

namespace App\Enums;

enum CareSourceType: string
{
    case Owner = 'owner';
    case Family = 'family';
    case Sitter = 'sitter';
    case Specialist = 'specialist';
    case Device = 'device';
    case Imported = 'imported';
    case Automatic = 'automatic';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner entry',
            self::Family => 'Family entry',
            self::Sitter => 'Sitter report',
            self::Specialist => 'Specialist entry',
            self::Device => 'Device reading',
            self::Imported => 'Imported',
            self::Automatic => 'Automatically created',
        };
    }

    public function verificationStatus(): string
    {
        return match ($this) {
            self::Owner, self::Family => 'person-reported',
            self::Sitter, self::Specialist => 'contributor-reported',
            self::Device, self::Automatic => 'device-unverified',
            self::Imported => 'imported-unverified',
        };
    }
}

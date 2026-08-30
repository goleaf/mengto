<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class PetProfileDuplicateIdentity
{
    public static function normalizeName(string $name): string
    {
        return Str::lower(Str::squish($name));
    }

    public static function nameHash(string $name): string
    {
        return hash('sha256', self::normalizeName($name));
    }
}

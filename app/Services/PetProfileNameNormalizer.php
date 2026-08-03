<?php

declare(strict_types=1);

namespace App\Services;

final class PetProfileNameNormalizer
{
    public function normalize(string $name): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim($name));

        return mb_strtolower(is_string($collapsed) ? $collapsed : trim($name));
    }
}

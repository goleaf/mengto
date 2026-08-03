<?php

declare(strict_types=1);

namespace App\Enums;

enum DiscoveryPreferenceScope: string
{
    case Item = 'item';
    case Category = 'category';
}

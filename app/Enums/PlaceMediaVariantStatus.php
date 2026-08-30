<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceMediaVariantStatus: string
{
    case Ready = 'ready';
    case Failed = 'failed';
}

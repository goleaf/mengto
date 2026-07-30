<?php

declare(strict_types=1);

namespace App\Enums;

enum CareSyncStatus: string
{
    case Direct = 'direct';
    case Synchronized = 'synchronized';
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceManagementClaimPurpose: string
{
    case Initial = 'initial';
    case Renewal = 'renewal';
    case ScopeChange = 'scope_change';
    case Transfer = 'transfer';
}

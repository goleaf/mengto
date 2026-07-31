<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentMediaStatus: string
{
    case Pending = 'pending';
    case Uploading = 'uploading';
    case Validating = 'validating';
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Quarantined = 'quarantined';
    case Deleted = 'deleted';
}

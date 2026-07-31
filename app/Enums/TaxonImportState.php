<?php

declare(strict_types=1);

namespace App\Enums;

enum TaxonImportState: string
{
    case Pending = 'pending';
    case Analyzing = 'analyzing';
    case Ready = 'ready';
    case Importing = 'importing';
    case Validating = 'validating';
    case Completed = 'completed';
    case Active = 'active';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case RolledBack = 'rolled-back';

    public function canProcess(): bool
    {
        return in_array($this, [
            self::Ready,
            self::Importing,
            self::Failed,
        ], true);
    }

    public function canActivate(): bool
    {
        return in_array($this, [self::Completed, self::RolledBack], true);
    }
}

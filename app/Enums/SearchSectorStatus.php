<?php

namespace App\Enums;

enum SearchSectorStatus: string
{
    case Unchecked = 'unchecked';
    case InProgress = 'in-progress';
    case Checked = 'checked';
    case Recheck = 'recheck';
    case Inaccessible = 'inaccessible';
    case Dangerous = 'dangerous';
    case PossibleSighting = 'possible-sighting';
    case Completed = 'completed';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}

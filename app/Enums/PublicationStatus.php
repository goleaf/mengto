<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';
    case Outdated = 'outdated';
    case Hidden = 'hidden';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}

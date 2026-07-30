<?php

namespace App\Enums;

enum SearchVolunteerStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Left = 'left';
    case Blocked = 'blocked';

    public function label(): string
    {
        return str($this->value)->headline()->toString();
    }
}

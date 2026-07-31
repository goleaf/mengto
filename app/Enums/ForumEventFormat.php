<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventFormat: string
{
    case Physical = 'physical';
    case Online = 'online';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return __('forum_events.formats.'.$this->value);
    }
}

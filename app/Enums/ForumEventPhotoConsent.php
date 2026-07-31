<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventPhotoConsent: string
{
    case AskFirst = 'ask_first';
    case Granted = 'granted';
    case Declined = 'declined';

    public function label(): string
    {
        return __('forum_events.photo_consent.'.$this->value);
    }
}

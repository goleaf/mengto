<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentMediaType: string
{
    case Image = 'image';
    case Video = 'video';
    case Audio = 'audio';
    case Document = 'document';
}

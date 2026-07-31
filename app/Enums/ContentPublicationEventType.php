<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentPublicationEventType: string
{
    case Created = 'created';
    case Published = 'published';
    case StatusChanged = 'status-changed';
    case AudienceChanged = 'audience-changed';
    case InteractionSettingsChanged = 'interaction-settings-changed';
    case DomainLinked = 'domain-linked';
    case MediaAttached = 'media-attached';
    case Restored = 'restored';
}

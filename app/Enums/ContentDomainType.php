<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentDomainType: string
{
    case Pet = 'pet';
    case Group = 'group';
    case Event = 'event';
    case Place = 'place';
    case Service = 'service';
    case Marketplace = 'marketplace';
    case Adoption = 'adoption';
    case LostFound = 'lost-found';
    case ForumTopic = 'forum-topic';
    case KnowledgeArticle = 'knowledge-article';
    case CareRecord = 'care-record';
    case MedicalSummary = 'medical-summary';
}

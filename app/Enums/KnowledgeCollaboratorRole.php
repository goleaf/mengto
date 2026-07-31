<?php

declare(strict_types=1);

namespace App\Enums;

enum KnowledgeCollaboratorRole: string
{
    case Maintainer = 'maintainer';
    case Contributor = 'contributor';
    case CommunityReviewer = 'community-reviewer';
    case ExpertReviewer = 'expert-reviewer';

    public function label(): string
    {
        return __("knowledge.collaborator_roles.{$this->value}");
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum KnowledgeWorkflowEventType: string
{
    case Created = 'created';
    case ContentRevised = 'content-revised';
    case StatusChanged = 'status-changed';
    case CollaboratorAdded = 'collaborator-added';
    case CollaboratorRemoved = 'collaborator-removed';
    case CorrectionReviewed = 'correction-reviewed';
    case RolledBack = 'rolled-back';
    case EditorialLocked = 'editorial-locked';
    case EditorialUnlocked = 'editorial-unlocked';
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumJournalCollaboratorState: string
{
    case Active = 'active';
    case Revoked = 'revoked';

    public function label(): string
    {
        return __("forum_journals.collaborator_states.{$this->value}");
    }
}

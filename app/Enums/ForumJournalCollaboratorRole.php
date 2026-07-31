<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumJournalCollaboratorRole: string
{
    case Viewer = 'viewer';
    case Editor = 'editor';

    public function label(): string
    {
        return __("forum_journals.collaborator_roles.{$this->value}");
    }

    public function canEdit(): bool
    {
        return $this === self::Editor;
    }
}

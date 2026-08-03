<?php

declare(strict_types=1);

namespace App\Enums;

enum PetWorkspaceFilter: string
{
    case All = 'all';
    case Owned = 'owned';
    case Shared = 'shared';
    case Drafts = 'drafts';
    case Discoverable = 'discoverable';

    public function label(): string
    {
        return __("pet_workspace.filters.{$this->value}");
    }
}

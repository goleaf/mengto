<?php

declare(strict_types=1);

namespace App\Enums;

enum PetWorkspaceSort: string
{
    case Recent = 'recent';
    case Name = 'name';
    case Status = 'status';

    public function label(): string
    {
        return __("pet_workspace.sorts.{$this->value}");
    }
}

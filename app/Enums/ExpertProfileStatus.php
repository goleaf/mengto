<?php

declare(strict_types=1);

namespace App\Enums;

enum ExpertProfileStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Published = 'published';
    case Paused = 'paused';
    case Suspended = 'suspended';

    public function label(): string
    {
        return __("experts.profile_statuses.{$this->value}");
    }
}

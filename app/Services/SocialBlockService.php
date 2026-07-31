<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialRelationshipType;
use App\Models\SocialActor;
use App\Models\SocialRelationship;

final class SocialBlockService
{
    public function blockedBetween(SocialActor $first, SocialActor $second): bool
    {
        return SocialRelationship::query()
            ->active()
            ->where('relationship_type', SocialRelationshipType::Block->value)
            ->where(function ($query) use ($first, $second): void {
                $query
                    ->where(function ($direct) use ($first, $second): void {
                        $direct
                            ->where('source_actor_id', $first->id)
                            ->where('target_actor_id', $second->id);
                    })
                    ->orWhere(function ($reverse) use ($first, $second): void {
                        $reverse
                            ->where('source_actor_id', $second->id)
                            ->where('target_actor_id', $first->id);
                    });
            })
            ->exists();
    }
}

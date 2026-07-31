<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\SocialRelationshipType;
use App\Models\SocialRelationship;
use App\Models\User;
use App\Services\SocialActorAccess;

final class SocialRelationshipPolicy
{
    public function __construct(private readonly SocialActorAccess $access) {}

    public function view(User $user, SocialRelationship $relationship): bool
    {
        $relationship->loadMissing(['sourceActor', 'targetActor']);

        if (in_array($relationship->relationship_type, [
            SocialRelationshipType::Block,
            SocialRelationshipType::Restrict,
            SocialRelationshipType::Mute,
            SocialRelationshipType::CloseCircle,
        ], true)) {
            return $this->access->canRepresent($relationship->sourceActor, $user);
        }

        return $this->access->canRepresent($relationship->sourceActor, $user)
            || $this->access->canRepresent($relationship->targetActor, $user);
    }

    public function end(User $user, SocialRelationship $relationship): bool
    {
        $relationship->loadMissing(['sourceActor', 'targetActor']);

        if (in_array($relationship->relationship_type, [
            SocialRelationshipType::Block,
            SocialRelationshipType::Restrict,
            SocialRelationshipType::Mute,
            SocialRelationshipType::CloseCircle,
        ], true)) {
            return $this->access->canRepresent($relationship->sourceActor, $user);
        }

        return $this->access->canRepresent($relationship->sourceActor, $user)
            || $this->access->canRepresent($relationship->targetActor, $user);
    }
}

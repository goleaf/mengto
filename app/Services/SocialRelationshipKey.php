<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialRelationshipDirection;
use App\Enums\SocialRelationshipType;

final class SocialRelationshipKey
{
    public static function forRelationship(
        int $sourceActorId,
        int $targetActorId,
        SocialRelationshipType $type,
    ): string {
        [$source, $target] = self::orderedEndpoints($sourceActorId, $targetActorId, $type);

        return implode(':', ['relationship', $type->value, $source, $target]);
    }

    public static function forRequest(
        int $sourceActorId,
        int $targetActorId,
        SocialRelationshipType $type,
    ): string {
        [$source, $target] = self::orderedEndpoints($sourceActorId, $targetActorId, $type);

        return implode(':', ['request', $type->value, $source, $target]);
    }

    /** @return array{0: int, 1: int} */
    private static function orderedEndpoints(
        int $sourceActorId,
        int $targetActorId,
        SocialRelationshipType $type,
    ): array {
        if ($type->direction() === SocialRelationshipDirection::Symmetric && $sourceActorId > $targetActorId) {
            return [$targetActorId, $sourceActorId];
        }

        return [$sourceActorId, $targetActorId];
    }
}

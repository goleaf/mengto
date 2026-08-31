<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Canonical relationship data lives in SocialActor relationships. Until the
 * legacy connection center is migrated to those queries it must not invent a
 * social graph for every account.
 */
final class ConnectionCatalog
{
    /** @return array<string, array<string, mixed>> */
    public function records(): array
    {
        return [];
    }

    /** @return array<string, mixed>|null */
    public function find(string $target): ?array
    {
        return $this->records()[$target] ?? null;
    }

    /** @return array<int, string> */
    public function followerTargets(): array
    {
        return [];
    }

    /** @return array<int, string> */
    public function incomingRequestTargets(): array
    {
        return [];
    }

    /** @return array<int, array{target: string, group: string, reason: string, signals: array<int, string>}> */
    public function recommendations(): array
    {
        return [];
    }
}

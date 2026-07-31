<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SocialActor;
use Illuminate\Contracts\Cache\Repository;

final class SocialGraphCache
{
    public function __construct(private readonly Repository $cache) {}

    public function invalidate(SocialActor ...$actors): void
    {
        foreach ($actors as $actor) {
            foreach ([
                "social:actor:{$actor->id}:relationships",
                "social:actor:{$actor->id}:requests",
                "social:actor:{$actor->id}:counts",
                "social:actor:{$actor->id}:recommendations",
                "social:actor:{$actor->id}:search",
            ] as $key) {
                $this->cache->forget($key);
            }

            $this->cache->forever(
                "social:actor:{$actor->id}:projection-version",
                $actor->lock_version,
            );
        }
    }
}

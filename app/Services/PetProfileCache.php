<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PetProfile;
use Illuminate\Contracts\Cache\Repository;

final class PetProfileCache
{
    public function __construct(private readonly Repository $cache) {}

    public function invalidate(PetProfile $profile): void
    {
        foreach ([
            "pet-profile:{$profile->id}:public",
            "pet-profile:{$profile->profile_key}:canonical",
            'pet-profile:directory:public',
            'pet-profile:search:public',
            'pet-profile:recommendations:public',
        ] as $key) {
            $this->cache->forget($key);
        }

        $this->cache->forever(
            "pet-profile:{$profile->id}:projection-version",
            $profile->lock_version,
        );
    }
}

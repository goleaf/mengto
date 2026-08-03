<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DiscoveryCategory;
use App\Enums\DiscoveryPreferenceScope;
use App\Models\DiscoveryPreference;
use App\Models\User;

final class UpdateDiscoveryPreference
{
    public function hide(
        User $user,
        DiscoveryPreferenceScope $scope,
        DiscoveryCategory $category,
        ?string $targetKey,
        ?string $reasonCode,
    ): DiscoveryPreference {
        return DiscoveryPreference::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'scope' => $scope->value,
                'category' => $category->value,
                'target_key' => $scope === DiscoveryPreferenceScope::Category
                    ? '*'
                    : (string) $targetKey,
            ],
            ['reason_code' => $reasonCode ?? 'not_relevant'],
        );
    }

    public function reset(User $user): int
    {
        return DiscoveryPreference::query()->forUser($user)->delete();
    }
}

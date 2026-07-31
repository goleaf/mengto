<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ExpertProfileStatus;
use App\Enums\VerificationStatus;
use App\Models\ExpertProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class ForumProfessionalAccess
{
    public function allows(User $user): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return ExpertProfile::query()
            ->where('owner_id', $user->id)
            ->where('status', ExpertProfileStatus::Published->value)
            ->whereIn('verification_status', [
                VerificationStatus::Verified->value,
                VerificationStatus::Expiring->value,
            ])
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('verification_expires_at')
                    ->orWhere('verification_expires_at', '>', now());
            })
            ->exists();
    }
}

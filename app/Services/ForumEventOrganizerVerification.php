<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ExpertProfileStatus;
use App\Enums\VerificationStatus;
use App\Models\ExpertProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class ForumEventOrganizerVerification
{
    /**
     * @param  iterable<int, int>  $userIds
     * @return array<int, bool>
     */
    public function verifiedUserIds(iterable $userIds): array
    {
        $ids = collect($userIds)
            ->filter(static fn (mixed $id): bool => is_int($id) && $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return ExpertProfile::query()
            ->select(['id', 'owner_id'])
            ->whereIn('owner_id', $ids)
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
            ->pluck('owner_id')
            ->mapWithKeys(static fn (int $id): array => [$id => true])
            ->all();
    }

    public function allows(?User $user): bool
    {
        return $user?->isActive() === true
            && isset($this->verifiedUserIds([$user->id])[$user->id]);
    }
}

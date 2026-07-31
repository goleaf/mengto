<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExpertProfile;
use App\Models\ForumGroup;
use App\Models\PetProfile;
use App\Models\User;
use App\Models\UserDomainState;

final class SocialActorFoundationBackfill
{
    public function __construct(private readonly SocialActorResolver $resolver) {}

    /** @return array{users: int, pets: int, experts: int, groups: int, legacy_state_rows: int} */
    public function run(bool $dryRun = false, int $chunkSize = 200): array
    {
        $chunkSize = max(1, min(1000, $chunkSize));
        $counts = [
            'users' => User::query()->count(),
            'pets' => PetProfile::query()->withTrashed()->count(),
            'experts' => ExpertProfile::query()->count(),
            'groups' => ForumGroup::query()->count(),
            'legacy_state_rows' => UserDomainState::query()
                ->whereIn('namespace', ['prototype.state.v1', 'pet-friends.state.v1'])
                ->count(),
        ];

        if ($dryRun) {
            return $counts;
        }

        User::query()
            ->select(['id'])
            ->chunkById($chunkSize, function ($users): void {
                foreach ($users as $user) {
                    $this->resolver->forUser($user);
                }
            });
        PetProfile::query()
            ->withTrashed()
            ->select(['id'])
            ->chunkById($chunkSize, function ($profiles): void {
                foreach ($profiles as $profile) {
                    $this->resolver->forPet($profile);
                }
            });
        ExpertProfile::query()
            ->select(['id'])
            ->chunkById($chunkSize, function ($profiles): void {
                foreach ($profiles as $profile) {
                    $this->resolver->forExpert($profile);
                }
            });
        ForumGroup::query()
            ->select(['id'])
            ->chunkById($chunkSize, function ($groups): void {
                foreach ($groups as $group) {
                    $this->resolver->forGroup($group);
                }
            });

        return $counts;
    }
}

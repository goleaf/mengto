<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumReviewAssignmentState;
use App\Enums\UserStatus;
use App\Models\ForumReviewPanel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CommunityReviewEligibility
{
    private const PROPOSER_LEVELS = [
        'trusted-contributor',
        'mentor',
        'community-reviewer',
        'category-steward',
        'moderator',
        'senior-moderator',
    ];

    private const REVIEWER_LEVELS = [
        'community-reviewer',
        'category-steward',
        'moderator',
        'senior-moderator',
    ];

    public function canPropose(User $user): bool
    {
        return $user->isAdministrator()
            || ($user->isActive() && $this->hasCurrentLevel($user, self::PROPOSER_LEVELS));
    }

    public function canReview(User $user): bool
    {
        return $user->isActive() && $this->hasCurrentLevel($user, self::REVIEWER_LEVELS);
    }

    /**
     * @param  list<int>  $excludedUserIds
     * @return Collection<int, User>
     */
    public function balancedReviewers(
        ForumReviewPanel $panel,
        array $excludedUserIds,
        int $limit,
    ): Collection {
        $alreadyAssigned = $panel->assignments()
            ->pluck('reviewer_user_id')
            ->all();
        $excluded = array_values(array_unique([
            ...$excludedUserIds,
            ...array_map('intval', $alreadyAssigned),
        ]));

        return User::query()
            ->select(['id', 'actor_key', 'created_at'])
            ->where('status', UserStatus::Active->value)
            ->whereNotNull('email_verified_at')
            ->when($excluded !== [], fn (Builder $query) => $query->whereNotIn('id', $excluded))
            ->whereHas(
                'forumTrustAssignments',
                fn (Builder $query) => $query
                    ->where(static function (Builder $expiryQuery): void {
                        $expiryQuery
                            ->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->whereHas(
                        'level',
                        fn (Builder $levelQuery) => $levelQuery
                            ->where('is_active', true)
                            ->whereIn('stable_key', self::REVIEWER_LEVELS),
                    ),
            )
            ->withCount([
                'forumReviewAssignments as active_review_assignments_count' => fn (Builder $query) => $query
                    ->where('state', ForumReviewAssignmentState::Assigned->value)
                    ->where('review_deadline_at', '>', now()),
            ])
            ->orderBy('active_review_assignments_count')
            ->oldest('created_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /** @param list<string> $levelKeys */
    private function hasCurrentLevel(User $user, array $levelKeys): bool
    {
        return $user->forumTrustAssignments()
            ->where(static function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas(
                'level',
                fn (Builder $query) => $query
                    ->where('is_active', true)
                    ->whereIn('stable_key', $levelKeys),
            )
            ->exists();
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\MentorMatchData;
use App\Enums\ForumMentorProfileState;
use App\Enums\ForumMentorshipState;
use App\Enums\ForumMentorshipType;
use App\Enums\UserStatus;
use App\Models\ForumBlock;
use App\Models\ForumCategoryTranslation;
use App\Models\ForumMentorScope;
use App\Models\ForumReputationAggregate;
use App\Models\ForumUserTrustLevel;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class MentorMatcher
{
    public function __construct(private MentorshipEligibility $eligibility) {}

    /**
     * @return Collection<int, MentorMatchData>
     */
    public function find(
        User $requester,
        ForumMentorshipType $type,
        string $language,
        string $communicationPreference,
        ?int $forumCategoryId = null,
        ?int $taxonId = null,
        ?string $locationScope = null,
        int $limit = 12,
    ): Collection {
        $boundedLimit = max(1, min($limit, 24));
        $scopes = ForumMentorScope::query()
            ->with([
                'profile.user:id,name,actor_key,status,email_verified_at',
                'category.translations',
                'taxon.activeVersion',
            ])
            ->where('mentorship_type', $type->value)
            ->where('is_active', true)
            ->whereHas('profile', function ($query): void {
                $query
                    ->where('state', ForumMentorProfileState::Active->value)
                    ->where('is_public', true)
                    ->whereNotNull('safety_acknowledged_at');
            })
            ->whereHas('profile.user', function ($query) use ($requester): void {
                $query
                    ->whereKeyNot($requester->id)
                    ->where('status', UserStatus::Active->value)
                    ->whereNotNull('email_verified_at');
            })
            ->orderBy('id')
            ->limit(80)
            ->get();

        $scopes = $scopes->filter(function (ForumMentorScope $scope) use (
            $communicationPreference,
            $forumCategoryId,
            $language,
            $locationScope,
            $taxonId,
        ): bool {
            $profile = $scope->profile;

            return in_array($language, $profile->languages, true)
                && in_array($communicationPreference, $profile->communication_preferences, true)
                && ($scope->forum_category_id === null || $scope->forum_category_id === $forumCategoryId)
                && ($scope->taxon_id === null || $scope->taxon_id === $taxonId)
                && (
                    $locationScope === null
                    || $profile->location_scope === null
                    || $profile->location_scope === $locationScope
                );
        });

        $userIds = $scopes->pluck('profile.user_id')->unique()->values()->all();
        $blockedIds = $this->blockedUserIds($requester, $scopes);
        $verifiedIds = $this->eligibility->professionallyVerifiedUserIds($userIds);
        $activeCounts = User::query()
            ->whereIn('id', $userIds)
            ->withCount([
                'mentorshipsAsMentor as open_mentorships_count' => fn ($query) => $query
                    ->whereIn('state', [
                        ForumMentorshipState::Requested->value,
                        ForumMentorshipState::Active->value,
                    ]),
            ])
            ->get(['id'])
            ->pluck('open_mentorships_count', 'id');
        $reputation = ForumReputationAggregate::query()
            ->select(['user_id', 'total'])
            ->whereIn('user_id', $userIds)
            ->whereNull('forum_category_id')
            ->whereNull('taxon_id')
            ->whereNull('location_scope_key')
            ->whereHas('dimension', fn ($query) => $query->where('stable_key', 'mentoring'))
            ->pluck('total', 'user_id');
        $trustPositions = ForumUserTrustLevel::query()
            ->with('level:id,position,is_active')
            ->whereIn('user_id', $userIds)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->filter(fn (ForumUserTrustLevel $assignment): bool => $assignment->level->is_active)
            ->groupBy('user_id')
            ->map(fn (Collection $assignments): int => (int) $assignments->max('level.position'));

        return $scopes
            ->reject(function (ForumMentorScope $scope) use (
                $activeCounts,
                $blockedIds,
                $verifiedIds,
            ): bool {
                $mentorId = $scope->profile->user_id;

                return in_array($mentorId, $blockedIds, true)
                    || (int) $activeCounts->get($mentorId, 0) >= $scope->profile->capacity
                    || (
                        $scope->requires_verified_expertise
                        && ! in_array($mentorId, $verifiedIds, true)
                    );
            })
            ->map(function (ForumMentorScope $scope) use (

                $forumCategoryId,
                $locationScope,
                $reputation,
                $taxonId,
                $trustPositions,
                $verifiedIds,
            ): MentorMatchData {
                $profile = $scope->profile;
                $reputationScore = min(10, max(0, (int) $reputation->get($profile->user_id, 0)));
                $trustScore = min(10, max(0, (int) $trustPositions->get($profile->user_id, 0)));
                $score = 100 + $reputationScore + $trustScore;
                $reasons = [
                    'forum_mentorship.match_reasons.type',
                    'forum_mentorship.match_reasons.language',
                    'forum_mentorship.match_reasons.communication',
                ];

                if ($forumCategoryId !== null && $scope->forum_category_id === $forumCategoryId) {
                    $score += 20;
                    $reasons[] = 'forum_mentorship.match_reasons.category';
                }

                if ($taxonId !== null && $scope->taxon_id === $taxonId) {
                    $score += 20;
                    $reasons[] = 'forum_mentorship.match_reasons.taxon';
                }

                if ($locationScope !== null && $profile->location_scope === $locationScope) {
                    $score += 10;
                    $reasons[] = 'forum_mentorship.match_reasons.location';
                }

                if ($reputationScore > 0) {
                    $reasons[] = 'forum_mentorship.match_reasons.mentoring';
                }

                if ($trustScore > 0) {
                    $reasons[] = 'forum_mentorship.match_reasons.trust';
                }

                $professionallyVerified = in_array($profile->user_id, $verifiedIds, true);

                if ($professionallyVerified) {
                    $reasons[] = 'forum_mentorship.match_reasons.professional';
                }

                $categoryName = $this->categoryName($scope);

                return new MentorMatchData(
                    scopeId: $scope->id,
                    mentorUserId: $profile->user_id,
                    mentorName: $profile->user->name,
                    headline: $profile->headline,
                    summary: $profile->summary,
                    type: $scope->mentorship_type,
                    languages: $profile->languages,
                    communicationPreferences: $profile->communication_preferences,
                    locationScope: $profile->location_scope,
                    categoryName: $categoryName,
                    scientificName: $scope->taxon?->activeVersion?->scientific_name,
                    professionallyVerified: $professionallyVerified,
                    score: $score,
                    reasonTranslationKeys: $reasons,
                );
            })
            ->sortByDesc('score')
            ->take($boundedLimit)
            ->values();
    }

    /**
     * @param  Collection<int, ForumMentorScope>  $scopes
     * @return list<int>
     */
    private function blockedUserIds(User $requester, Collection $scopes): array
    {
        $users = $scopes
            ->mapWithKeys(fn (ForumMentorScope $scope): array => [
                $scope->profile->user->actor_key => $scope->profile->user_id,
            ]);

        if ($users->isEmpty()) {
            return [];
        }

        $blockedActorKeys = ForumBlock::query()
            ->where(function ($query) use ($requester, $users): void {
                $query
                    ->where('user_key', $requester->actor_key)
                    ->whereIn('blocked_author_key', $users->keys());
            })
            ->orWhere(function ($query) use ($requester, $users): void {
                $query
                    ->whereIn('user_key', $users->keys())
                    ->where('blocked_author_key', $requester->actor_key);
            })
            ->get(['user_key', 'blocked_author_key'])
            ->flatMap(fn (ForumBlock $block): array => [
                $block->user_key,
                $block->blocked_author_key,
            ])
            ->reject(fn (string $key): bool => $key === $requester->actor_key)
            ->unique();

        return $blockedActorKeys
            ->map(fn (string $key): ?int => $users->get($key))
            ->filter(static fn (?int $id): bool => $id !== null)
            ->values()
            ->all();
    }

    private function categoryName(ForumMentorScope $scope): ?string
    {
        if ($scope->category === null) {
            return null;
        }

        $localized = $scope->category->translations
            ->firstWhere('locale', app()->getLocale());

        if ($localized instanceof ForumCategoryTranslation) {
            return $localized->name;
        }

        $fallback = $scope->category->translations
            ->firstWhere('locale', config('app.fallback_locale'));

        return $fallback instanceof ForumCategoryTranslation
            ? $fallback->name
            : null;
    }
}

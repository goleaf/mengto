<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ForumTopicLifecycleSnapshot;
use App\Enums\ForumTopicStatus;
use App\Models\ForumCategoryLifecycleRule;
use App\Models\ForumTopic;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final class ForumTopicLifecycleProjection
{
    public function snapshot(
        ForumTopic $topic,
        ?CarbonImmutable $now = null,
    ): ForumTopicLifecycleSnapshot {
        $now ??= CarbonImmutable::now();
        $rule = $this->rule($topic);
        $referenceAt = $this->referenceAt($topic);
        $status = $topic->status->canonical();
        $ageInDays = $referenceAt->diffInDays($now);
        $staleAfterDays = $this->integerRule($rule, 'stale_after_days');
        $necropostAfterDays = $this->integerRule($rule, 'necropost_after_days');
        $archiveAfterDays = $this->nullableIntegerRule($rule, 'archive_review_after_days');
        $retentionAfterDays = $this->nullableIntegerRule($rule, 'retention_review_after_days');
        $lastBumpedAt = $this->immutable($topic->last_bumped_at);
        $nextBumpAt = $lastBumpedAt?->addHours(
            $this->integerRule($rule, 'bump_cooldown_hours'),
        );
        $eligibleForAgeWarnings = $status->isPubliclyVisible()
            && ! in_array($status, [
                ForumTopicStatus::Locked,
                ForumTopicStatus::Archived,
                ForumTopicStatus::Merged,
                ForumTopicStatus::Redirected,
                ForumTopicStatus::Removed,
            ], true);

        return new ForumTopicLifecycleSnapshot(
            status: $status,
            isStale: $eligibleForAgeWarnings && $ageInDays >= $staleAfterDays,
            showsNecropostWarning: $eligibleForAgeWarnings
                && $ageInDays >= $necropostAfterDays,
            archiveReviewDue: $archiveAfterDays !== null
                && $ageInDays >= $archiveAfterDays
                && ! in_array($status, [
                    ForumTopicStatus::Archived,
                    ForumTopicStatus::Removed,
                    ForumTopicStatus::Merged,
                    ForumTopicStatus::Redirected,
                ], true),
            retentionReviewDue: $topic->retention_until?->isPast()
                ?? ($retentionAfterDays !== null && $ageInDays >= $retentionAfterDays),
            canBump: $this->booleanRule($rule, 'allow_bumping')
                && $status->isPubliclyVisible()
                && ($nextBumpAt === null || $nextBumpAt->lessThanOrEqualTo($now)),
            allowsAuthorReopen: $this->booleanRule($rule, 'allow_author_reopen'),
            allowsAuthorArchive: $this->booleanRule($rule, 'allow_author_archive'),
            allowsAuthorRemove: $this->booleanRule($rule, 'allow_author_remove'),
            hasLegalHold: $topic->hasActiveLegalHold(),
            lockVersion: $topic->lock_version,
            nextBumpAt: $nextBumpAt,
            referenceAt: $referenceAt,
        );
    }

    private function rule(ForumTopic $topic): ?ForumCategoryLifecycleRule
    {
        if ($topic->forum_category_id === null) {
            return null;
        }

        return ForumCategoryLifecycleRule::query()
            ->select([
                'id',
                'forum_category_id',
                'stale_after_days',
                'necropost_after_days',
                'archive_review_after_days',
                'retention_review_after_days',
                'bump_cooldown_hours',
                'allow_author_reopen',
                'allow_author_archive',
                'allow_author_remove',
                'allow_bumping',
            ])
            ->where('forum_category_id', $topic->forum_category_id)
            ->first();
    }

    private function referenceAt(ForumTopic $topic): CarbonImmutable
    {
        return $this->immutable($topic->last_author_update_at)
            ?? $this->immutable($topic->last_activity_at)
            ?? $this->immutable($topic->published_at)
            ?? $this->immutable($topic->created_at)
            ?? CarbonImmutable::now();
    }

    private function immutable(DateTimeInterface|string|null $value): ?CarbonImmutable
    {
        if ($value === null) {
            return null;
        }

        return is_string($value)
            ? CarbonImmutable::parse($value)
            : CarbonImmutable::instance($value);
    }

    private function integerRule(
        ?ForumCategoryLifecycleRule $rule,
        string $key,
    ): int {
        $value = $rule instanceof ForumCategoryLifecycleRule
            ? $rule->getAttribute($key)
            : null;

        return (int) ($value ?? config("forum.lifecycle.{$key}"));
    }

    private function nullableIntegerRule(
        ?ForumCategoryLifecycleRule $rule,
        string $key,
    ): ?int {
        $value = $rule instanceof ForumCategoryLifecycleRule
            ? $rule->getAttribute($key)
            : null;
        $value ??= config("forum.lifecycle.{$key}");

        return $value === null ? null : (int) $value;
    }

    private function booleanRule(
        ?ForumCategoryLifecycleRule $rule,
        string $key,
    ): bool {
        $value = $rule instanceof ForumCategoryLifecycleRule
            ? $rule->getAttribute($key)
            : null;

        return (bool) ($value ?? config("forum.lifecycle.{$key}"));
    }
}

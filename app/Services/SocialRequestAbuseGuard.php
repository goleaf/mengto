<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SocialRelationshipType;
use App\Enums\SocialRequestStatus;
use App\Models\SocialActor;
use App\Models\SocialRelationshipRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

final class SocialRequestAbuseGuard
{
    public function __construct(private readonly SocialAccountActorQuery $accountActors) {}

    /**
     * @return array{
     *     message: string|null,
     *     message_fingerprint: string|null,
     *     risk_level: string,
     *     risk_signals: list<string>|null
     * }
     */
    public function assess(
        User $user,
        SocialActor $target,
        SocialRelationshipType $type,
        ?string $message,
    ): array {
        $normalizedMessage = $this->normalizeMessage($message);
        $fingerprint = $normalizedMessage === null
            ? null
            : hash('sha256', mb_strtolower($normalizedMessage));
        $targetUserIds = $this->accountActors->controllerUserIds($target);
        $targetActorIds = $this->accountActors
            ->controlledByUserIds($targetUserIds)
            ->modelKeys();

        if ($targetActorIds === []) {
            $targetActorIds = [$target->id];
        }

        $this->ensureRepeatAllowed($user, $targetActorIds, $type);
        $this->ensureRateAllowed($user);
        $this->ensureMessageAllowed($user, $normalizedMessage, $fingerprint);

        $riskSignals = $this->riskSignals($user);

        return [
            'message' => $normalizedMessage,
            'message_fingerprint' => $fingerprint,
            'risk_level' => $riskSignals === [] ? 'normal' : 'elevated',
            'risk_signals' => $riskSignals === [] ? null : $riskSignals,
        ];
    }

    /** @param list<int> $targetActorIds */
    private function ensureRepeatAllowed(
        User $user,
        array $targetActorIds,
        SocialRelationshipType $type,
    ): void {
        $blocked = SocialRelationshipRequest::query()
            ->where('created_by_user_id', $user->id)
            ->whereIn('target_actor_id', $targetActorIds)
            ->where('relationship_type', $type->value)
            ->where(function ($query): void {
                $query
                    ->where('prevent_repeats', true)
                    ->orWhere(function ($cooldown): void {
                        $cooldown
                            ->whereNotNull('repeat_after')
                            ->where('repeat_after', '>', now());
                    });
            })
            ->exists();

        if ($blocked) {
            throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.request_cooldown'),
            ]);
        }
    }

    private function ensureRateAllowed(User $user): void
    {
        $newAccountDays = (int) config('social_relationships.request_limits.new_account_days', 7);
        $isNewOrUnverified = $user->email_verified_at === null
            || $user->created_at->greaterThan(now()->subDays($newAccountDays));
        $hourLimit = (int) config(
            $isNewOrUnverified
                ? 'social_relationships.request_limits.new_hour'
                : 'social_relationships.request_limits.verified_hour',
            $isNewOrUnverified ? 5 : 20,
        );
        $dayLimit = (int) config(
            $isNewOrUnverified
                ? 'social_relationships.request_limits.new_day'
                : 'social_relationships.request_limits.verified_day',
            $isNewOrUnverified ? 10 : 60,
        );
        $hourCount = SocialRelationshipRequest::query()
            ->where('created_by_user_id', $user->id)
            ->where('sent_at', '>=', now()->subHour())
            ->count();
        $dayCount = SocialRelationshipRequest::query()
            ->where('created_by_user_id', $user->id)
            ->where('sent_at', '>=', now()->subDay())
            ->count();

        if ($hourCount >= $hourLimit || $dayCount >= $dayLimit) {
            throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.request_rate_limited'),
            ]);
        }

        $minimumSample = (int) config('social_relationships.request_limits.low_acceptance_minimum', 10);
        $recent = SocialRelationshipRequest::query()
            ->select(['id', 'status'])
            ->where('created_by_user_id', $user->id)
            ->whereIn('status', [
                SocialRequestStatus::Accepted->value,
                SocialRequestStatus::Declined->value,
                SocialRequestStatus::Hidden->value,
                SocialRequestStatus::Blocked->value,
                SocialRequestStatus::RemovedAfterReport->value,
                SocialRequestStatus::Expired->value,
            ])
            ->latest('id')
            ->limit(100)
            ->get();

        if ($recent->count() < $minimumSample) {
            return;
        }

        $accepted = $recent->where('status', SocialRequestStatus::Accepted)->count();
        $floor = (float) config('social_relationships.request_limits.low_acceptance_floor', 0.10);
        $lowAcceptanceHourLimit = (int) config(
            'social_relationships.request_limits.low_acceptance_hour',
            3,
        );

        if (($accepted / $recent->count()) < $floor && $hourCount >= $lowAcceptanceHourLimit) {
            throw ValidationException::withMessages([
                'target' => __('social_relationships.validation.request_rate_limited'),
            ]);
        }
    }

    private function ensureMessageAllowed(
        User $user,
        ?string $message,
        ?string $fingerprint,
    ): void {
        if ($message === null) {
            return;
        }

        if (mb_strlen($message) > (int) config('social_relationships.request_message_max', 240)) {
            throw ValidationException::withMessages([
                'message' => __('social_relationships.validation.request_message_too_long'),
            ]);
        }

        if (preg_match('/(?:https?:\/\/|www\.|\b[^\s@]+@[^\s@]+\.[^\s@]+|\+\d[\d\s().-]{7,}\d|\b\d{3}[\s.-]\d{3}[\s.-]\d{2,4}\b)/iu', $message) === 1) {
            throw ValidationException::withMessages([
                'message' => __('social_relationships.validation.request_message_contact_details'),
            ]);
        }

        if ($fingerprint === null) {
            return;
        }

        $duplicates = SocialRelationshipRequest::query()
            ->where('created_by_user_id', $user->id)
            ->where('message_fingerprint', $fingerprint)
            ->where('sent_at', '>=', now()->subDay())
            ->count();

        if ($duplicates >= (int) config('social_relationships.request_limits.duplicate_message_day', 3)) {
            throw ValidationException::withMessages([
                'message' => __('social_relationships.validation.request_message_repeated'),
            ]);
        }
    }

    /** @return list<string> */
    private function riskSignals(User $user): array
    {
        $signals = [];

        if ($user->email_verified_at === null) {
            $signals[] = 'unverified-account';
        }

        if ($user->created_at->greaterThan(
            now()->subDays((int) config('social_relationships.request_limits.new_account_days', 7)),
        )) {
            $signals[] = 'new-account';
        }

        return $signals;
    }

    private function normalizeMessage(?string $message): ?string
    {
        if ($message === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', trim($message));

        return $normalized === '' ? null : $normalized;
    }
}

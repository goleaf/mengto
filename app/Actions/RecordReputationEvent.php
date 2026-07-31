<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ReputationEventData;
use App\Enums\ReputationEventStatus;
use App\Models\ForumReputationAggregate;
use App\Models\ForumReputationDimension;
use App\Models\ForumReputationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RecordReputationEvent
{
    public function handle(ReputationEventData $data): ForumReputationEvent
    {
        $existing = ForumReputationEvent::query()
            ->where('idempotency_key', $data->idempotencyKey)
            ->first();

        if ($existing instanceof ForumReputationEvent) {
            return $existing;
        }

        $dimension = ForumReputationDimension::query()
            ->where('stable_key', $data->dimension)
            ->where('is_active', true)
            ->firstOrFail();

        if (
            $data->amount > 0
            && $data->actor?->id !== null
            && $data->actor->id === $data->recipient->id
        ) {
            throw ValidationException::withMessages([
                'reputation' => __('forum_reputation.messages.self_award_forbidden'),
            ]);
        }

        return DB::transaction(function () use ($data, $dimension): ForumReputationEvent {
            $this->enforceLimits($data, $dimension);
            $event = ForumReputationEvent::query()->createOrFirst(
                ['idempotency_key' => $data->idempotencyKey],
                [
                    'user_id' => $data->recipient->id,
                    'forum_reputation_dimension_id' => $dimension->id,
                    'actor_user_id' => $data->actor?->id,
                    'forum_category_id' => $data->forumCategoryId,
                    'taxon_id' => $data->taxonId,
                    'reversal_of_event_id' => $data->reversalOfEventId,
                    'event_type' => $data->eventType,
                    'source_entity_type' => $data->sourceEntityType,
                    'source_entity_id' => $data->sourceEntityId,
                    'amount' => $data->amount,
                    'reason_code' => $data->reasonCode,
                    'explanation_translation_key' => $data->explanationTranslationKey,
                    'location_scope_key' => $data->locationScopeKey,
                    'status' => ReputationEventStatus::Active,
                    'metadata' => $data->metadata,
                    'effective_at' => $data->effectiveAt ?? now(),
                    'expires_at' => $data->expiresAt,
                    'review_at' => $data->reviewAt,
                ],
            );

            if (! $event->wasRecentlyCreated) {
                return $event;
            }

            $this->applyAggregate(
                event: $event,
                dimension: $dimension,
                categoryId: null,
                taxonId: null,
                locationScopeKey: null,
            );

            if (
                $data->forumCategoryId !== null
                || $data->taxonId !== null
                || $data->locationScopeKey !== null
            ) {
                $this->applyAggregate(
                    event: $event,
                    dimension: $dimension,
                    categoryId: $data->forumCategoryId,
                    taxonId: $data->taxonId,
                    locationScopeKey: $data->locationScopeKey,
                );
            }

            return $event;
        }, 3);
    }

    private function enforceLimits(
        ReputationEventData $data,
        ForumReputationDimension $dimension,
    ): void {
        if ($data->amount <= 0 || $data->actor === null) {
            return;
        }

        $base = ForumReputationEvent::query()
            ->where('actor_user_id', $data->actor->id)
            ->where('user_id', $data->recipient->id)
            ->where('forum_reputation_dimension_id', $dimension->id)
            ->where('status', ReputationEventStatus::Active->value)
            ->where('amount', '>', 0);
        $daily = (clone $base)
            ->where('effective_at', '>=', now()->startOfDay())
            ->sum('amount');
        $relationship = (clone $base)
            ->where('effective_at', '>=', now()->subDays(30))
            ->sum('amount');

        if (
            $daily + $data->amount > $dimension->daily_actor_recipient_cap
            || $relationship + $data->amount > $dimension->relationship_cap
        ) {
            throw ValidationException::withMessages([
                'reputation' => __('forum_reputation.messages.relationship_limit_reached'),
            ]);
        }
    }

    private function applyAggregate(
        ForumReputationEvent $event,
        ForumReputationDimension $dimension,
        ?int $categoryId,
        ?int $taxonId,
        ?string $locationScopeKey,
    ): void {
        $scopeKey = hash('sha256', implode('|', [
            (string) ($categoryId ?? 0),
            (string) ($taxonId ?? 0),
            $locationScopeKey ?? '',
        ]));
        ForumReputationAggregate::query()->firstOrCreate(
            [
                'user_id' => $event->user_id,
                'forum_reputation_dimension_id' => $dimension->id,
                'scope_key' => $scopeKey,
            ],
            [
                'forum_category_id' => $categoryId,
                'taxon_id' => $taxonId,
                'location_scope_key' => $locationScopeKey,
                'total' => 0,
            ],
        );
        $aggregate = ForumReputationAggregate::query()
            ->where('user_id', $event->user_id)
            ->where('forum_reputation_dimension_id', $dimension->id)
            ->where('scope_key', $scopeKey)
            ->lockForUpdate()
            ->firstOrFail();
        $aggregate->forceFill([
            'total' => $aggregate->total + $event->amount,
            'last_event_at' => $event->effective_at,
        ])->save();
    }
}

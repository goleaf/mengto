<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ReputationEventData;
use App\Enums\ReputationEventStatus;
use App\Models\ForumReputationEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class ReverseReputationEvent
{
    public function __construct(
        private RecordReputationEvent $record,
    ) {}

    public function handle(
        ForumReputationEvent $event,
        string $reasonCode,
        ?User $actor = null,
    ): ForumReputationEvent {
        return DB::transaction(function () use ($actor, $event, $reasonCode): ForumReputationEvent {
            $event = ForumReputationEvent::query()
                ->with(['dimension', 'user'])
                ->lockForUpdate()
                ->findOrFail($event->id);
            $existing = $event->reversal()->first();

            if ($existing instanceof ForumReputationEvent) {
                return $existing;
            }

            $reversal = $this->record->handle(new ReputationEventData(
                recipient: $event->user,
                dimension: $event->dimension->stable_key,
                eventType: 'reversal',
                sourceEntityType: $event->source_entity_type,
                sourceEntityId: $event->source_entity_id,
                amount: -$event->amount,
                reasonCode: $reasonCode,
                explanationTranslationKey: 'forum_reputation.events.reversal',
                idempotencyKey: 'reversal:'.$event->id,
                actor: $actor,
                forumCategoryId: $event->forum_category_id,
                taxonId: $event->taxon_id,
                locationScopeKey: $event->location_scope_key,
                reversalOfEventId: $event->id,
                metadata: ['original_reason_code' => $event->reason_code],
            ));

            $event->forceFill(['status' => ReputationEventStatus::Reversed])->save();

            return $reversal;
        }, 3);
    }
}

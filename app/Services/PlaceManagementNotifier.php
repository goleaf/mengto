<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\DeliverPlaceManagementNotification;
use App\Models\PlaceManagementClaimEvent;
use App\Models\PlaceManagementNotificationIntent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class PlaceManagementNotifier
{
    /**
     * @param iterable<int, User> $recipients
     * @param array<string, mixed> $safePayload
     */
    public function record(
        PlaceManagementClaimEvent $event,
        iterable $recipients,
        string $kind,
        string $messageKey,
        array $safePayload,
    ): void {
        $intentIds = [];
        $seen = [];

        foreach ($recipients as $recipient) {
            if (isset($seen[$recipient->id])) {
                continue;
            }

            $seen[$recipient->id] = true;
            $deduplicationKey = hash('sha256', implode('|', [
                (string) $event->id,
                (string) $recipient->id,
                $kind,
            ]));
            $intent = PlaceManagementNotificationIntent::query()->firstOrCreate(
                [
                    'place_management_claim_event_id' => $event->id,
                    'recipient_user_id' => $recipient->id,
                    'notification_kind' => $kind,
                ],
                [
                    'message_key' => $messageKey,
                    'safe_payload' => $safePayload,
                    'deduplication_key' => $deduplicationKey,
                    'created_at' => now(),
                ],
            );
            $intentIds[] = $intent->id;
        }

        DB::afterCommit(static function () use ($intentIds): void {
            foreach ($intentIds as $intentId) {
                app(DeliverPlaceManagementNotification::class)->handle($intentId);
            }
        });
    }
}

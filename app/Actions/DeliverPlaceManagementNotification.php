<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceManagementNotificationStatus;
use App\Models\PlaceManagementNotificationIntent;
use App\Models\User;
use App\Notifications\PlaceManagementTransitionNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Throwable;

final class DeliverPlaceManagementNotification
{
    public function handle(int $intentId): void
    {
        try {
            DB::transaction(function () use ($intentId): void {
                $intent = PlaceManagementNotificationIntent::query()
                    ->lockForUpdate()
                    ->find($intentId);

                if (! $intent instanceof PlaceManagementNotificationIntent
                    || in_array($intent->status, [
                        PlaceManagementNotificationStatus::Delivered,
                        PlaceManagementNotificationStatus::Cancelled,
                    ], true)) {
                    return;
                }

                $recipient = User::query()->find($intent->recipient_user_id);
                if (! $recipient instanceof User || ! $recipient->isActive()) {
                    $intent->forceFill([
                        'status' => PlaceManagementNotificationStatus::Cancelled,
                        'last_attempted_at' => now(),
                        'updated_at' => now(),
                    ])->save();

                    return;
                }

                $notificationId = Uuid::uuid5(
                    Uuid::NAMESPACE_URL,
                    'pawcircle:place-management:'.$intent->deduplication_key,
                )->toString();

                if (! DB::table('notifications')->where('id', $notificationId)->exists()) {
                    try {
                        $recipient->notify(new PlaceManagementTransitionNotification(
                            id: $notificationId,
                            messageKey: $intent->message_key,
                            safePayload: $intent->safe_payload,
                        ));
                    } catch (QueryException $exception) {
                        if (! DB::table('notifications')->where('id', $notificationId)->exists()) {
                            throw $exception;
                        }
                    }
                }

                $intent->forceFill([
                    'status' => PlaceManagementNotificationStatus::Delivered,
                    'attempt_count' => $intent->attempt_count + 1,
                    'last_attempted_at' => now(),
                    'delivered_at' => now(),
                    'last_error' => null,
                    'updated_at' => now(),
                ])->save();
            }, 3);
        } catch (Throwable $exception) {
            PlaceManagementNotificationIntent::query()
                ->whereKey($intentId)
                ->whereNot('status', PlaceManagementNotificationStatus::Delivered->value)
                ->update([
                    'status' => PlaceManagementNotificationStatus::Failed->value,
                    'last_attempted_at' => now(),
                    'last_error' => mb_substr($exception::class, 0, 255),
                    'updated_at' => now(),
                ]);

            throw $exception;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class PlaceManagementTransitionNotification extends Notification
{
    use Queueable;

    /**
     * @param array<string, mixed> $safePayload
     */
    public function __construct(
        string $id,
        public readonly string $messageKey,
        public readonly array $safePayload,
    ) {
        $this->id = $id;
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return ['message_key' => $this->messageKey, ...$this->safePayload];
    }
}

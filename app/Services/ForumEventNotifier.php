<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumEvent;
use App\Models\ForumNotification;
use App\Models\User;

final class ForumEventNotifier
{
    /**
     * @param  array<string, string>  $replace
     */
    public function send(
        User $user,
        ForumEvent $event,
        string $type,
        string $titleKey,
        string $bodyKey,
        string $deduplicationKey,
        array $replace = [],
    ): void {
        $previousLocale = app()->getLocale();
        app()->setLocale($user->locale);

        try {
            ForumNotification::query()->firstOrCreate(
                ['deduplication_key' => $deduplicationKey],
                [
                    'user_key' => $user->actor_key,
                    'type' => $type,
                    'title' => __($titleKey),
                    'body' => __($bodyKey, [
                        'event' => $event->title,
                        ...$replace,
                    ]),
                ],
            );
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}

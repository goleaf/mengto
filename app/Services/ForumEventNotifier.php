<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumEvent;
use App\Models\ForumNotification;
use App\Models\User;
use Illuminate\Contracts\Translation\Translator;

final class ForumEventNotifier
{
    public function __construct(
        private readonly Translator $translator,
        private readonly SocialBlockService $blocks,
    ) {}

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
        if (! $user->isActive()
            || ($event->organizer_user_id !== null
                && $event->organizer_user_id !== $user->id
                && $this->blocks->accountBlockedBetween(
                    [$event->organizer_user_id],
                    [$user->id],
                ))
        ) {
            return;
        }

        ForumNotification::query()->firstOrCreate(
            ['deduplication_key' => $deduplicationKey],
            [
                'user_key' => $user->actor_key,
                'type' => $type,
                'title' => $this->translator->get(
                    $titleKey,
                    locale: $user->locale,
                ),
                'body' => $this->translator->get(
                    $bodyKey,
                    [
                        'event' => $event->title,
                        ...$replace,
                    ],
                    $user->locale,
                ),
            ],
        );
    }
}

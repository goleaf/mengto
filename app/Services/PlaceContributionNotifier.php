<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ForumNotification;
use App\Models\User;
use Illuminate\Contracts\Translation\Translator;

final readonly class PlaceContributionNotifier
{
    public function __construct(private Translator $translator) {}

    /** @param array<string, string|int> $replace */
    public function send(
        User $recipient,
        string $type,
        string $titleKey,
        string $bodyKey,
        string $deduplicationKey,
        array $replace = [],
    ): void {
        ForumNotification::query()->firstOrCreate(
            ['deduplication_key' => $deduplicationKey],
            [
                'user_key' => $recipient->actor_key,
                'type' => $type,
                'title' => $this->translator->get($titleKey, $replace, $recipient->locale),
                'body' => $this->translator->get($bodyKey, $replace, $recipient->locale),
            ],
        );
    }
}

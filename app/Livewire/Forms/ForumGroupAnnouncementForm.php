<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumGroupAnnouncementData;
use Carbon\CarbonImmutable;
use Livewire\Form;

final class ForumGroupAnnouncementForm extends Form
{
    public string $title = '';

    public string $body = '';

    public string $expiresAt = '';

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
            'expiresAt' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function toData(string $idempotencyKey): CreateForumGroupAnnouncementData
    {
        $validated = $this->validate();

        return new CreateForumGroupAnnouncementData(
            title: trim((string) $validated['title']),
            body: trim((string) $validated['body']),
            publishedAt: CarbonImmutable::now(),
            expiresAt: filled($validated['expiresAt'] ?? null)
                ? CarbonImmutable::parse((string) $validated['expiresAt'])
                : null,
            idempotencyKey: $idempotencyKey,
        );
    }
}

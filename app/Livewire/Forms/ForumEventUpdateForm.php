<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumEventUpdateForm extends Form
{
    public string $type = 'general';

    public string $audience = 'public';

    public string $title = '';

    public string $body = '';

    public string $idempotencyKey = '';

    /** @return array{type: ForumEventUpdateType, audience: ForumEventUpdateAudience, title: string, body: string, idempotency_key: string} */
    public function data(): array
    {
        $validated = $this->validate([
            'type' => ['required', Rule::enum(ForumEventUpdateType::class)],
            'audience' => ['required', Rule::enum(ForumEventUpdateAudience::class)],
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);

        return [
            'type' => ForumEventUpdateType::from((string) $validated['type']),
            'audience' => ForumEventUpdateAudience::from((string) $validated['audience']),
            'title' => trim((string) $validated['title']),
            'body' => trim((string) $validated['body']),
            'idempotency_key' => (string) $validated['idempotencyKey'],
        ];
    }
}

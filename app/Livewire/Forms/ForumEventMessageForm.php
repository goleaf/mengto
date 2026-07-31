<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\ForumEventMessageAudience;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumEventMessageForm extends Form
{
    public string $audience = 'organizers';

    public string $body = '';

    public string $idempotencyKey = '';

    /** @return array{audience: ForumEventMessageAudience, body: string, idempotency_key: string} */
    public function data(): array
    {
        $validated = $this->validate([
            'audience' => ['required', Rule::enum(ForumEventMessageAudience::class)],
            'body' => ['required', 'string', 'min:1', 'max:3000'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);

        return [
            'audience' => ForumEventMessageAudience::from((string) $validated['audience']),
            'body' => trim((string) $validated['body']),
            'idempotency_key' => (string) $validated['idempotencyKey'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Form;

final class ForumExpertQuestionForm extends Form
{
    public string $body = '';

    public string $idempotencyKey = '';

    /** @return array{body: string, idempotency_key: string} */
    public function data(): array
    {
        /** @var array{body: string, idempotencyKey: string} $validated */
        $validated = $this->validate([
            'body' => ['required', 'string', 'min:10', 'max:4000'],
            'idempotencyKey' => ['required', 'uuid'],
        ]);

        return [
            'body' => trim($validated['body']),
            'idempotency_key' => $validated['idempotencyKey'],
        ];
    }
}

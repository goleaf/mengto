<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Form;

final class ForumJournalCommentForm extends Form
{
    public string $body = '';

    public string $idempotencyKey = '';

    /** @return array<string, list<string>> */
    protected function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:2', 'max:1500'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ];
    }

    /** @return array{body: string, idempotency_key: string} */
    public function data(): array
    {
        $validated = $this->validate();

        return [
            'body' => trim((string) $validated['body']),
            'idempotency_key' => (string) $validated['idempotencyKey'],
        ];
    }
}

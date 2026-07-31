<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Form;

final class ForumEventReviewForm extends Form
{
    public int $rating = 5;

    public string $title = '';

    public string $body = '';

    public string $idempotencyKey = '';

    /** @return array{rating: int, title: string, body: string, idempotency_key: string} */
    public function data(): array
    {
        $validated = $this->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);

        return [
            'rating' => (int) $validated['rating'],
            'title' => trim((string) $validated['title']),
            'body' => trim((string) $validated['body']),
            'idempotency_key' => (string) $validated['idempotencyKey'],
        ];
    }
}

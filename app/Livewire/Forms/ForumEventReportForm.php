<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Services\ForumReportReasonCatalog;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumEventReportForm extends Form
{
    public string $reason = '';

    public string $description = '';

    public bool $immediateSafety = false;

    public bool $truthfulnessConfirmed = false;

    /** @return array{reason: string, description: string|null, immediate_safety: bool, truthfulness_confirmed: bool} */
    public function data(ForumReportReasonCatalog $catalog): array
    {
        $validated = $this->validate([
            'reason' => ['required', Rule::in($catalog->acceptedInputKeys())],
            'description' => ['nullable', 'string', 'max:1200'],
            'immediateSafety' => ['boolean'],
            'truthfulnessConfirmed' => ['accepted'],
        ]);

        return [
            'reason' => (string) $validated['reason'],
            'description' => filled($validated['description'] ?? null)
                ? trim((string) $validated['description'])
                : null,
            'immediate_safety' => (bool) $validated['immediateSafety'],
            'truthfulness_confirmed' => (bool) $validated['truthfulnessConfirmed'],
        ];
    }
}

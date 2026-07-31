<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumExpertModerationForm extends Form
{
    public string $decision = 'approve';

    public string $reason = '';

    public int $expectedLockVersion = 0;

    /** @return array{decision: string, reason: string|null, expected_lock_version: int} */
    public function data(): array
    {
        /** @var array{decision: string, reason: string, expectedLockVersion: int} $validated */
        $validated = $this->validate([
            'decision' => ['required', Rule::in(['approve', 'select', 'decline', 'remove'])],
            'reason' => [
                Rule::requiredIf(in_array($this->decision, ['decline', 'remove'], true)),
                'nullable',
                'string',
                'max:1000',
            ],
            'expectedLockVersion' => ['required', 'integer', 'min:0'],
        ]);

        return [
            'decision' => $validated['decision'],
            'reason' => filled($validated['reason']) ? trim($validated['reason']) : null,
            'expected_lock_version' => $validated['expectedLockVersion'],
        ];
    }
}

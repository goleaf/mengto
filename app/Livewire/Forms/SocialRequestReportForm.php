<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;

final class SocialRequestReportForm extends Form
{
    /** @var list<string> */
    public const REASONS = [
        'unwanted-contact',
        'spam',
        'targeted-harassment',
        'stalking',
        'scam',
        'phishing',
        'impersonation',
    ];

    public string $requestKey = '';

    public string $reason = 'unwanted-contact';

    public string $details = '';

    public bool $truthfulnessConfirmed = false;

    public bool $blockAccount = true;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'requestKey' => ['required', 'uuid'],
            'reason' => ['required', Rule::in(self::REASONS)],
            'details' => ['nullable', 'string', 'max:2000'],
            'truthfulnessConfirmed' => ['accepted'],
            'blockAccount' => ['boolean'],
        ];
    }

    public function clear(): void
    {
        $this->reset();
        $this->reason = 'unwanted-contact';
        $this->blockAccount = true;
    }
}

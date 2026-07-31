<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Carbon\CarbonImmutable;
use Livewire\Form;

final class ForumEventInvitationForm extends Form
{
    public string $recipientEmail = '';

    public string $expiresAt = '';

    public string $idempotencyKey = '';

    /** @return array{recipient_email: string, expires_at: CarbonImmutable, idempotency_key: string} */
    public function data(string $timezone): array
    {
        $validated = $this->validate([
            'recipientEmail' => ['required', 'email:rfc', 'max:255'],
            'expiresAt' => ['required', 'date', 'after:now'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);

        return [
            'recipient_email' => mb_strtolower(trim((string) $validated['recipientEmail'])),
            'expires_at' => CarbonImmutable::parse((string) $validated['expiresAt'], $timezone),
            'idempotency_key' => (string) $validated['idempotencyKey'],
        ];
    }
}

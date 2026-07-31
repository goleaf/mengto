<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Carbon\CarbonImmutable;
use Livewire\Form;

final class ForumEventRescheduleForm extends Form
{
    public string $startsAt = '';

    public string $endsAt = '';

    public string $timezone = 'UTC';

    public string $explanation = '';

    public string $idempotencyKey = '';

    /** @return array{starts_at: CarbonImmutable, ends_at: CarbonImmutable, timezone: string, explanation: string, idempotency_key: string} */
    public function data(): array
    {
        $validated = $this->validate([
            'startsAt' => ['required', 'date', 'after:now'],
            'endsAt' => ['required', 'date', 'after:startsAt'],
            'timezone' => ['required', 'timezone:all'],
            'explanation' => ['required', 'string', 'min:10', 'max:5000'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);
        $timezone = (string) $validated['timezone'];

        return [
            'starts_at' => CarbonImmutable::parse((string) $validated['startsAt'], $timezone),
            'ends_at' => CarbonImmutable::parse((string) $validated['endsAt'], $timezone),
            'timezone' => $timezone,
            'explanation' => trim((string) $validated['explanation']),
            'idempotency_key' => (string) $validated['idempotencyKey'],
        ];
    }
}

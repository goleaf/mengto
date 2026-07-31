<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumGroupActivityData;
use App\Enums\ForumGroupActivityFormat;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumGroupActivityForm extends Form
{
    public string $title = '';

    public string $summary = '';

    public string $format = 'physical';

    public string $startsAt = '';

    public string $endsAt = '';

    public string $timezone = 'UTC';

    public string $locationScope = '';

    public ?int $capacity = null;

    public string $participationNotes = '';

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'summary' => ['required', 'string', 'min:10', 'max:3000'],
            'format' => ['required', Rule::enum(ForumGroupActivityFormat::class)],
            'startsAt' => ['required', 'date', 'after:now'],
            'endsAt' => ['required', 'date', 'after:startsAt'],
            'timezone' => ['required', 'timezone:all'],
            'locationScope' => ['nullable', 'string', 'max:160'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'participationNotes' => ['nullable', 'string', 'max:3000'],
        ];
    }

    public function toData(string $idempotencyKey): CreateForumGroupActivityData
    {
        $validated = $this->validate();
        $timezone = (string) $validated['timezone'];

        return new CreateForumGroupActivityData(
            title: trim((string) $validated['title']),
            summary: trim((string) $validated['summary']),
            format: ForumGroupActivityFormat::from((string) $validated['format']),
            startsAt: CarbonImmutable::parse((string) $validated['startsAt'], $timezone),
            endsAt: CarbonImmutable::parse((string) $validated['endsAt'], $timezone),
            timezone: $timezone,
            locationScope: filled($validated['locationScope'] ?? null)
                ? trim((string) $validated['locationScope'])
                : null,
            capacity: isset($validated['capacity']) ? (int) $validated['capacity'] : null,
            participationNotes: filled($validated['participationNotes'] ?? null)
                ? trim((string) $validated['participationNotes'])
                : null,
            idempotencyKey: $idempotencyKey,
        );
    }
}

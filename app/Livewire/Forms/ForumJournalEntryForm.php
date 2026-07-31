<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumJournalEntryData;
use App\Data\UpdateForumJournalEntryData;
use App\Enums\ForumJournalEntryKind;
use App\Models\ForumJournalEntry;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumJournalEntryForm extends Form
{
    public string $kind = 'entry';

    public string $title = '';

    public string $body = '';

    public string $occurredAt = '';

    public string $timezone = 'UTC';

    /** @var array<string, int|float|string|null> */
    public array $metricValues = [];

    public string $idempotencyKey = '';

    public int $expectedVersion = 0;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'kind' => ['required', Rule::enum(ForumJournalEntryKind::class)],
            'title' => ['required', 'string', 'min:2', 'max:180'],
            'body' => ['required', 'string', 'min:2', 'max:10000'],
            'occurredAt' => ['required', 'date'],
            'timezone' => ['required', 'timezone:all'],
            'metricValues' => ['array', 'max:8'],
            'metricValues.*' => ['nullable', 'numeric'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
            'expectedVersion' => ['integer', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'kind' => __('forum_journals.fields.entry_kind'),
            'title' => __('forum_journals.fields.entry_title'),
            'body' => __('forum_journals.fields.entry_body'),
            'occurredAt' => __('forum_journals.fields.occurred_at'),
            'timezone' => __('forum_journals.fields.timezone'),
            'metricValues' => __('forum_journals.fields.measurements'),
        ];
    }

    public function createData(): CreateForumJournalEntryData
    {
        $validated = $this->validate();
        $timezone = (string) $validated['timezone'];

        return new CreateForumJournalEntryData(
            kind: ForumJournalEntryKind::from((string) $validated['kind']),
            title: trim((string) $validated['title']),
            body: trim((string) $validated['body']),
            occurredAt: CarbonImmutable::parse((string) $validated['occurredAt'], $timezone),
            timezone: $timezone,
            measurements: $this->measurements($validated['metricValues']),
            idempotencyKey: (string) $validated['idempotencyKey'],
        );
    }

    public function updateData(): UpdateForumJournalEntryData
    {
        $validated = $this->validate();
        $timezone = (string) $validated['timezone'];

        return new UpdateForumJournalEntryData(
            kind: ForumJournalEntryKind::from((string) $validated['kind']),
            title: trim((string) $validated['title']),
            body: trim((string) $validated['body']),
            occurredAt: CarbonImmutable::parse((string) $validated['occurredAt'], $timezone),
            timezone: $timezone,
            measurements: $this->measurements($validated['metricValues']),
            expectedVersion: (int) $validated['expectedVersion'],
        );
    }

    public function fillFromEntry(ForumJournalEntry $entry): void
    {
        $this->kind = $entry->kind->value;
        $this->title = $entry->title;
        $this->body = $entry->body;
        $this->occurredAt = $entry->occurred_at
            ->setTimezone($entry->timezone)
            ->format('Y-m-d\TH:i');
        $this->timezone = $entry->timezone;
        $this->metricValues = $entry->measurements
            ->mapWithKeys(static fn ($measurement): array => [
                $measurement->metric_key => $measurement->numeric_value,
            ])
            ->all();
        $this->expectedVersion = $entry->lock_version;
    }

    /**
     * @param  array<string, int|float|string|null>  $values
     * @return list<array{key: string, value: int|float|string}>
     */
    private function measurements(array $values): array
    {
        $measurements = [];

        foreach ($values as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $measurements[] = [
                'key' => (string) $key,
                'value' => $value,
            ];
        }

        return $measurements;
    }
}

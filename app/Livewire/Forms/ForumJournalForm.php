<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumJournalData;
use App\Enums\ForumJournalType;
use App\Enums\ForumVisibility;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumJournalForm extends Form
{
    public string $title = '';

    public string $body = '';

    public string $categoryKey = '';

    public string $type = 'general';

    public string $visibility = 'public';

    public string $startedOn = '';

    public string $timezone = 'UTC';

    public string $locale = 'en';

    public string $idempotencyKey = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'body' => ['required', 'string', 'min:10', 'max:10000'],
            'categoryKey' => [
                'required',
                'string',
                Rule::exists('forum_categories', 'slug')
                    ->where('is_active', true)
                    ->whereNull('archived_at'),
            ],
            'type' => ['required', Rule::enum(ForumJournalType::class)],
            'visibility' => [
                'required',
                Rule::in([
                    ForumVisibility::Public->value,
                    ForumVisibility::Members->value,
                    ForumVisibility::Experts->value,
                    ForumVisibility::Link->value,
                    ForumVisibility::Private->value,
                ]),
            ],
            'startedOn' => [
                'required',
                'date',
                'after_or_equal:1900-01-01',
                'before_or_equal:tomorrow',
            ],
            'timezone' => ['required', 'timezone:all'],
            'locale' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'title' => __('forum_journals.fields.title'),
            'body' => __('forum_journals.fields.description'),
            'categoryKey' => __('forum_journals.fields.category'),
            'type' => __('forum_journals.fields.type'),
            'visibility' => __('forum_journals.fields.visibility'),
            'startedOn' => __('forum_journals.fields.started_on'),
            'timezone' => __('forum_journals.fields.timezone'),
            'locale' => __('forum_journals.fields.language'),
        ];
    }

    public function data(): CreateForumJournalData
    {
        $validated = $this->validate();
        $timezone = (string) $validated['timezone'];

        return new CreateForumJournalData(
            title: trim((string) $validated['title']),
            body: trim((string) $validated['body']),
            categoryKey: (string) $validated['categoryKey'],
            type: ForumJournalType::from((string) $validated['type']),
            visibility: ForumVisibility::from((string) $validated['visibility']),
            startedOn: CarbonImmutable::parse((string) $validated['startedOn'], $timezone),
            timezone: $timezone,
            locale: (string) $validated['locale'],
            idempotencyKey: (string) $validated['idempotencyKey'],
        );
    }
}

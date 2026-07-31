<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumExpertSessionData;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumExpertSessionForm extends Form
{
    public ?int $expertProfileId = null;

    public string $professionalScope = '';

    public string $jurisdiction = '';

    public string $title = '';

    public string $summary = '';

    public string $locale = 'en';

    public string $questionOpensAt = '';

    public string $questionClosesAt = '';

    public string $startsAt = '';

    public string $endsAt = '';

    public string $timezone = 'UTC';

    public string $idempotencyKey = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'expertProfileId' => ['required', 'integer', 'min:1'],
            'professionalScope' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'jurisdiction' => [
                'required',
                'string',
                'max:120',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/',
            ],
            'title' => ['required', 'string', 'min:8', 'max:180'],
            'summary' => ['required', 'string', 'min:20', 'max:10000'],
            'locale' => ['required', Rule::in(config('platform.supported_locales', ['en']))],
            'questionOpensAt' => ['required', 'date'],
            'questionClosesAt' => ['required', 'date', 'after:questionOpensAt'],
            'startsAt' => ['required', 'date', 'after_or_equal:questionOpensAt'],
            'endsAt' => ['required', 'date', 'after:startsAt', 'after_or_equal:questionClosesAt'],
            'timezone' => ['required', 'timezone:all'],
            'idempotencyKey' => ['required', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'expertProfileId' => __('forum_expert_sessions.fields.expert_profile'),
            'professionalScope' => __('forum_expert_sessions.fields.professional_scope'),
            'jurisdiction' => __('forum_expert_sessions.fields.jurisdiction'),
            'title' => __('forum_expert_sessions.fields.title'),
            'summary' => __('forum_expert_sessions.fields.summary'),
            'questionOpensAt' => __('forum_expert_sessions.fields.question_opens_at'),
            'questionClosesAt' => __('forum_expert_sessions.fields.question_closes_at'),
            'startsAt' => __('forum_expert_sessions.fields.starts_at'),
            'endsAt' => __('forum_expert_sessions.fields.ends_at'),
        ];
    }

    public function data(): CreateForumExpertSessionData
    {
        $validated = $this->validate();
        $timezone = (string) $validated['timezone'];

        return new CreateForumExpertSessionData(
            expertProfileId: (int) $validated['expertProfileId'],
            professionalScope: trim((string) $validated['professionalScope']),
            jurisdiction: strtoupper(trim((string) $validated['jurisdiction'])),
            title: trim((string) $validated['title']),
            summary: trim((string) $validated['summary']),
            locale: (string) $validated['locale'],
            timezone: $timezone,
            questionOpensAt: CarbonImmutable::parse(
                (string) $validated['questionOpensAt'],
                $timezone,
            ),
            questionClosesAt: CarbonImmutable::parse(
                (string) $validated['questionClosesAt'],
                $timezone,
            ),
            startsAt: CarbonImmutable::parse((string) $validated['startsAt'], $timezone),
            endsAt: CarbonImmutable::parse((string) $validated['endsAt'], $timezone),
            idempotencyKey: (string) $validated['idempotencyKey'],
        );
    }
}

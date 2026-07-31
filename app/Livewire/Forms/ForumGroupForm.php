<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumGroupData;
use App\Enums\ForumGroupVisibility;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumGroupForm extends Form
{
    public string $name = '';

    public string $description = '';

    public string $rulesText = '';

    public string $visibility = 'public';

    public string $defaultLocale = 'en';

    public string $locationScope = '';

    public string $questionsText = '';

    /** @var list<int> */
    public array $taxonIds = [];

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:160'],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
            'rulesText' => ['required', 'string', 'min:3', 'max:5000'],
            'visibility' => ['required', Rule::enum(ForumGroupVisibility::class)],
            'defaultLocale' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'locationScope' => [
                'nullable',
                'string',
                'max:160',
                'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
            ],
            'questionsText' => ['nullable', 'string', 'max:3000'],
            'taxonIds' => ['array', 'max:10'],
            'taxonIds.*' => [
                'integer',
                'distinct',
                Rule::exists('taxa', 'id')->where('is_active', true),
            ],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('forum_groups.fields.name'),
            'description' => __('forum_groups.fields.description'),
            'rulesText' => __('forum_groups.fields.rules'),
            'visibility' => __('forum_groups.fields.visibility'),
            'defaultLocale' => __('forum_groups.fields.language'),
            'locationScope' => __('forum_groups.fields.location_scope'),
            'questionsText' => __('forum_groups.fields.membership_questions'),
            'taxonIds' => __('forum_groups.fields.species_focus'),
        ];
    }

    public function data(): CreateForumGroupData
    {
        $validated = $this->validate();

        return new CreateForumGroupData(
            name: trim((string) $validated['name']),
            description: trim((string) $validated['description']),
            rules: $this->lines((string) $validated['rulesText'], 20),
            visibility: ForumGroupVisibility::from((string) $validated['visibility']),
            defaultLocale: (string) $validated['defaultLocale'],
            locationScope: filled($validated['locationScope'] ?? null)
                ? trim((string) $validated['locationScope'])
                : null,
            membershipQuestions: $this->lines(
                (string) ($validated['questionsText'] ?? ''),
                10,
            ),
            taxonIds: array_values(array_map('intval', $validated['taxonIds'])),
            idempotencyKey: 'group:create:'.(string) str()->uuid(),
        );
    }

    /** @return list<string> */
    private function lines(string $value, int $limit): array
    {
        return collect(preg_split('/\R/u', trim($value)) ?: [])
            ->map(static fn (string $line): string => trim($line))
            ->filter()
            ->unique()
            ->take($limit)
            ->values()
            ->all();
    }
}

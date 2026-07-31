<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\KnowledgeGuideData;
use App\Models\KnowledgeArticle;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class KnowledgeGuideForm extends Form
{
    public string $title = '';

    public string $summary = '';

    public string $body = '';

    public string $category = '';

    public string $type = 'guide';

    public string $difficulty = 'beginner';

    public string $audience = '';

    public string $language = 'en';

    public string $jurisdiction = '';

    /** @var list<int> */
    public array $taxonIds = [];

    public ?int $discussionTopicId = null;

    public string $sourcesText = '';

    public string $protectedSectionsText = '';

    public string $changeSummary = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:180'],
            'summary' => ['required', 'string', 'min:20', 'max:1500'],
            'body' => ['required', 'string', 'min:50', 'max:100000'],
            'category' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in([
                'guide',
                'checklist',
                'faq',
                'comparison',
                'local-guide',
            ])],
            'difficulty' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'audience' => ['nullable', 'string', 'max:120'],
            'language' => ['required', Rule::in(config('platform.supported_locales', ['en']))],
            'jurisdiction' => ['nullable', 'string', 'max:120'],
            'taxonIds' => ['array', 'max:1'],
            'taxonIds.*' => [
                'integer',
                Rule::exists('taxa', 'id')->where('is_active', true),
            ],
            'discussionTopicId' => ['nullable', 'integer', 'exists:forum_topics,id'],
            'sourcesText' => [
                'nullable',
                'string',
                'max:5000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    foreach ($this->lines((string) $value) as $url) {
                        if (validator(['url' => $url], ['url' => 'url:http,https'])->fails()) {
                            $fail(__('knowledge.validation.invalid_source_url'));

                            return;
                        }
                    }
                },
            ],
            'protectedSectionsText' => ['nullable', 'string', 'max:3000'],
            'changeSummary' => ['required', 'string', 'min:10', 'max:240'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'title' => __('knowledge.fields.title'),
            'summary' => __('knowledge.fields.summary'),
            'body' => __('knowledge.fields.body'),
            'category' => __('knowledge.fields.category'),
            'type' => __('knowledge.fields.type'),
            'difficulty' => __('knowledge.fields.difficulty'),
            'audience' => __('knowledge.fields.audience'),
            'language' => __('knowledge.fields.language'),
            'jurisdiction' => __('knowledge.fields.jurisdiction'),
            'taxonIds' => __('knowledge.fields.taxon'),
            'discussionTopicId' => __('knowledge.fields.discussion'),
            'sourcesText' => __('knowledge.fields.sources'),
            'protectedSectionsText' => __('knowledge.fields.protected_sections'),
            'changeSummary' => __('knowledge.fields.change_summary'),
        ];
    }

    public function fillFromArticle(KnowledgeArticle $article): void
    {
        $this->title = $article->title;
        $this->summary = $article->summary;
        $this->body = $article->body;
        $this->category = $article->category;
        $this->type = $article->type;
        $this->difficulty = $article->difficulty;
        $this->audience = $article->audience ?? '';
        $this->language = $article->language;
        $this->jurisdiction = $article->jurisdiction ?? '';
        $this->taxonIds = $article->taxon_id === null ? [] : [$article->taxon_id];
        $this->discussionTopicId = $article->discussion_topic_id;
        $this->sourcesText = implode("\n", $article->sources ?? []);
        $this->protectedSectionsText = implode("\n", $article->protected_sections ?? []);
        $this->changeSummary = '';
    }

    public function fillTranslationFromArticle(
        KnowledgeArticle $source,
        string $targetLocale,
    ): void {
        $this->title = '';
        $this->summary = '';
        $this->body = '';
        $this->category = $source->category;
        $this->type = $source->type;
        $this->difficulty = $source->difficulty;
        $this->audience = '';
        $this->language = $targetLocale;
        $this->jurisdiction = $source->jurisdiction ?? '';
        $this->taxonIds = $source->taxon_id === null ? [] : [$source->taxon_id];
        $this->discussionTopicId = $source->discussion_topic_id;
        $this->sourcesText = implode("\n", $source->sources ?? []);
        $this->protectedSectionsText = '';
        $this->changeSummary = '';
    }

    public function data(int $expectedLockVersion): KnowledgeGuideData
    {
        $validated = $this->validate();

        return new KnowledgeGuideData(
            title: trim((string) $validated['title']),
            summary: trim((string) $validated['summary']),
            body: trim((string) $validated['body']),
            category: (string) $validated['category'],
            type: (string) $validated['type'],
            difficulty: (string) $validated['difficulty'],
            audience: filled($validated['audience'] ?? null)
                ? trim((string) $validated['audience'])
                : null,
            language: (string) $validated['language'],
            jurisdiction: filled($validated['jurisdiction'] ?? null)
                ? trim((string) $validated['jurisdiction'])
                : null,
            taxonId: isset($validated['taxonIds'][0])
                ? (int) $validated['taxonIds'][0]
                : null,
            discussionTopicId: isset($validated['discussionTopicId'])
                ? (int) $validated['discussionTopicId']
                : null,
            sources: $this->lines((string) ($validated['sourcesText'] ?? '')),
            protectedSections: $this->lines(
                (string) ($validated['protectedSectionsText'] ?? ''),
            ),
            changeSummary: trim((string) $validated['changeSummary']),
            expectedLockVersion: $expectedLockVersion,
        );
    }

    /** @return list<string> */
    private function lines(string $value): array
    {
        return collect(preg_split('/\R/u', $value) ?: [])
            ->map(static fn (string $line): string => trim($line))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}

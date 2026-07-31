<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Form;

final class ForumExpertCorrectionForm extends Form
{
    public string $body = '';

    public string $sourceUrls = '';

    public string $reason = '';

    public int $expectedVersion = 1;

    /**
     * @return array{
     *   body: string,
     *   source_links: array<int, array{label: string, url: string}>,
     *   reason: string,
     *   expected_version: int
     * }
     */
    public function data(): array
    {
        /** @var array{body: string, sourceUrls: string, reason: string, expectedVersion: int} $validated */
        $validated = $this->validate([
            'body' => ['required', 'string', 'min:20', 'max:20000'],
            'sourceUrls' => ['nullable', 'string', 'max:10000'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'expectedVersion' => ['required', 'integer', 'min:1'],
        ]);
        $links = collect(preg_split('/\R/u', trim($validated['sourceUrls'])) ?: [])
            ->map(static fn (string $url): string => trim($url))
            ->filter()
            ->values()
            ->map(static fn (string $url, int $index): array => [
                'label' => __('forum_expert_sessions.labels.source_number', [
                    'number' => $index + 1,
                ]),
                'url' => $url,
            ])
            ->all();

        validator(['source_links' => $links], [
            'source_links' => ['array', 'max:10'],
            'source_links.*' => ['array:label,url'],
            'source_links.*.label' => ['required', 'string', 'max:180'],
            'source_links.*.url' => ['required', 'url:http,https', 'max:2000', 'distinct'],
        ])->validate();

        return [
            'body' => trim($validated['body']),
            'source_links' => $links,
            'reason' => trim($validated['reason']),
            'expected_version' => $validated['expectedVersion'],
        ];
    }
}

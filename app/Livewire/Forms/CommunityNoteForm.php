<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CommunityNoteData;
use App\Enums\ForumCommunityNoteType;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class CommunityNoteForm extends Form
{
    public string $noteType = 'missing-context';

    public string $body = '';

    public string $evidenceUrl = '';

    public string $evidenceLabel = '';

    public string $jurisdiction = '';

    public string $speciesContext = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'noteType' => ['required', Rule::enum(ForumCommunityNoteType::class)],
            'body' => ['required', 'string', 'min:40', 'max:2000'],
            'evidenceUrl' => ['required', 'url:http,https', 'max:500'],
            'evidenceLabel' => ['required', 'string', 'min:2', 'max:120'],
            'jurisdiction' => ['nullable', 'string', 'max:120'],
            'speciesContext' => ['nullable', 'string', 'max:180'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'noteType' => __('forum_review.fields.note_type'),
            'body' => __('forum_review.fields.body'),
            'evidenceUrl' => __('forum_review.fields.evidence_url'),
            'evidenceLabel' => __('forum_review.fields.evidence_label'),
            'jurisdiction' => __('forum_review.fields.jurisdiction'),
            'speciesContext' => __('forum_review.fields.species_context'),
        ];
    }

    public function data(int $topicId): CommunityNoteData
    {
        $validated = $this->validate();

        return new CommunityNoteData(
            subjectType: 'forum-topic',
            subjectId: $topicId,
            type: ForumCommunityNoteType::from((string) $validated['noteType']),
            body: trim((string) $validated['body']),
            evidence: [[
                'url' => (string) $validated['evidenceUrl'],
                'label' => trim((string) $validated['evidenceLabel']),
            ]],
            jurisdiction: filled($validated['jurisdiction'] ?? null)
                ? trim((string) $validated['jurisdiction'])
                : null,
            speciesContext: filled($validated['speciesContext'] ?? null)
                ? trim((string) $validated['speciesContext'])
                : null,
        );
    }
}

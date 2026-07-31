<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Form;

final class ForumGroupAssociationForm extends Form
{
    public string $topicSlug = '';

    public string $guideSlug = '';

    /** @return array<string, list<string>> */
    public function topicRules(): array
    {
        return [
            'topicSlug' => ['required', 'string', 'max:180', 'exists:forum_topics,slug'],
        ];
    }

    /** @return array<string, list<string>> */
    public function guideRules(): array
    {
        return [
            'guideSlug' => [
                'required',
                'string',
                'max:180',
                'exists:knowledge_articles,slug',
            ],
        ];
    }

    public function validatedTopicSlug(): string
    {
        $validated = $this->validate($this->topicRules());

        return trim((string) $validated['topicSlug']);
    }

    public function validatedGuideSlug(): string
    {
        $validated = $this->validate($this->guideRules());

        return trim((string) $validated['guideSlug']);
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\MentorScopeData;
use App\Enums\ForumMentorshipType;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class MentorScopeForm extends Form
{
    public string $type = 'first-time-owner';

    public string $experienceSummary = '';

    public ?int $forumCategoryId = null;

    /** @var list<int> */
    public array $taxonIds = [];

    public bool $requiresVerifiedExpertise = false;

    public bool $isActive = true;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ForumMentorshipType::class)],
            'experienceSummary' => ['required', 'string', 'min:20', 'max:2000'],
            'forumCategoryId' => [
                'nullable',
                'integer',
                Rule::exists('forum_categories', 'id')->where('is_active', true),
            ],
            'taxonIds' => ['array', 'max:1'],
            'taxonIds.*' => [
                'integer',
                Rule::exists('taxa', 'id')->where('is_active', true),
            ],
            'requiresVerifiedExpertise' => ['boolean'],
            'isActive' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'type' => __('forum_mentorship.fields.mentorship_type'),
            'experienceSummary' => __('forum_mentorship.fields.experience'),
            'forumCategoryId' => __('forum_mentorship.fields.category'),
            'taxonIds' => __('forum_mentorship.fields.taxon'),
            'requiresVerifiedExpertise' => __('forum_mentorship.fields.professional_scope'),
            'isActive' => __('forum_mentorship.fields.scope_active'),
        ];
    }

    public function data(): MentorScopeData
    {
        $validated = $this->validate();

        return new MentorScopeData(
            type: ForumMentorshipType::from((string) $validated['type']),
            experienceSummary: trim((string) $validated['experienceSummary']),
            forumCategoryId: isset($validated['forumCategoryId'])
                ? (int) $validated['forumCategoryId']
                : null,
            taxonId: isset($validated['taxonIds'][0])
                ? (int) $validated['taxonIds'][0]
                : null,
            requiresVerifiedExpertise: (bool) $validated['requiresVerifiedExpertise'],
            isActive: (bool) $validated['isActive'],
        );
    }
}

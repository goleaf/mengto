<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumPollData;
use App\Enums\ForumPollEligibility;
use App\Enums\ForumPollResultVisibility;
use App\Enums\ForumPollType;
use App\Enums\ForumPollVoterVisibility;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumPollForm extends Form
{
    public string $question = '';

    public string $description = '';

    public string $optionsText = '';

    public string $type = 'single-choice';

    public string $voterVisibility = 'anonymous';

    public string $resultVisibility = 'after-vote';

    public bool $isVoteEditable = true;

    public string $eligibility = 'group-members';

    public string $closesAt = '';

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:5', 'max:240'],
            'description' => ['nullable', 'string', 'max:3000'],
            'optionsText' => ['required', 'string', 'max:3800'],
            'type' => ['required', Rule::enum(ForumPollType::class)],
            'voterVisibility' => [
                'required',
                Rule::enum(ForumPollVoterVisibility::class),
            ],
            'resultVisibility' => [
                'required',
                Rule::enum(ForumPollResultVisibility::class),
            ],
            'isVoteEditable' => ['boolean'],
            'eligibility' => ['required', Rule::enum(ForumPollEligibility::class)],
            'closesAt' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function toData(string $idempotencyKey): CreateForumPollData
    {
        $validated = $this->validate();

        return new CreateForumPollData(
            question: trim((string) $validated['question']),
            description: filled($validated['description'] ?? null)
                ? trim((string) $validated['description'])
                : null,
            options: collect(preg_split('/\R/u', (string) $validated['optionsText']))
                ->filter(is_string(...))
                ->map(static fn (string $option): string => trim($option))
                ->filter()
                ->values()
                ->all(),
            type: ForumPollType::from((string) $validated['type']),
            voterVisibility: ForumPollVoterVisibility::from(
                (string) $validated['voterVisibility'],
            ),
            resultVisibility: ForumPollResultVisibility::from(
                (string) $validated['resultVisibility'],
            ),
            isVoteEditable: (bool) $validated['isVoteEditable'],
            eligibility: ForumPollEligibility::from((string) $validated['eligibility']),
            closesAt: filled($validated['closesAt'] ?? null)
                ? CarbonImmutable::parse((string) $validated['closesAt'])
                : null,
            idempotencyKey: $idempotencyKey,
        );
    }
}

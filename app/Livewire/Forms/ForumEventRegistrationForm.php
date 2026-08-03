<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\RegisterForForumEventData;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumEventRegistrationForm extends Form
{
    public string $attendanceFormat = 'physical';

    public int $guestCount = 0;

    public ?int $occurrenceId = null;

    /** @var list<int> */
    public array $petProfileIds = [];

    public string $requirementsNote = '';

    public string $photoConsent = 'ask_first';

    public bool $requirementsAccepted = false;

    public string $idempotencyKey = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'attendanceFormat' => ['required', Rule::enum(ForumEventFormat::class)],
            'guestCount' => ['required', 'integer', 'min:0', 'max:10'],
            'occurrenceId' => ['nullable', 'integer'],
            'petProfileIds' => ['array', 'max:5'],
            'petProfileIds.*' => ['integer', 'distinct'],
            'requirementsNote' => ['nullable', 'string', 'max:3000'],
            'photoConsent' => ['required', Rule::enum(ForumEventPhotoConsent::class)],
            'requirementsAccepted' => ['accepted'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ];
    }

    public function data(): RegisterForForumEventData
    {
        $validated = $this->validate();

        return new RegisterForForumEventData(
            attendanceFormat: ForumEventFormat::from((string) $validated['attendanceFormat']),
            guestCount: (int) $validated['guestCount'],
            petProfileId: null,
            requirementsNote: filled($validated['requirementsNote'] ?? null)
                ? trim((string) $validated['requirementsNote'])
                : null,
            photoConsent: ForumEventPhotoConsent::from((string) $validated['photoConsent']),
            requirementsAccepted: (bool) $validated['requirementsAccepted'],
            idempotencyKey: (string) $validated['idempotencyKey'],
            petProfileIds: array_values(array_map(
                static fn (mixed $id): int => (int) $id,
                $validated['petProfileIds'] ?? [],
            )),
            occurrenceId: isset($validated['occurrenceId'])
                ? (int) $validated['occurrenceId']
                : null,
        );
    }
}

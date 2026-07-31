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

    public ?int $petProfileId = null;

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
            'petProfileId' => ['nullable', 'integer'],
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
            petProfileId: isset($validated['petProfileId'])
                ? (int) $validated['petProfileId']
                : null,
            requirementsNote: filled($validated['requirementsNote'] ?? null)
                ? trim((string) $validated['requirementsNote'])
                : null,
            photoConsent: ForumEventPhotoConsent::from((string) $validated['photoConsent']),
            requirementsAccepted: (bool) $validated['requirementsAccepted'],
            idempotencyKey: (string) $validated['idempotencyKey'],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\MentorshipRequestData;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class MentorshipRequestForm extends Form
{
    public string $message = '';

    public string $language = 'en';

    public string $locationScope = '';

    public string $communicationPreference = 'platform';

    public bool $safetyAcknowledged = false;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:20', 'max:3000'],
            'language' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'locationScope' => ['nullable', 'string', 'max:160', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'],
            'communicationPreference' => ['required', Rule::in(['platform'])],
            'safetyAcknowledged' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'message' => __('forum_mentorship.fields.request_message'),
            'language' => __('forum_mentorship.fields.language'),
            'locationScope' => __('forum_mentorship.fields.location_scope'),
            'communicationPreference' => __('forum_mentorship.fields.communication'),
            'safetyAcknowledged' => __('forum_mentorship.fields.safety_acknowledgement'),
        ];
    }

    public function data(string $idempotencyKey): MentorshipRequestData
    {
        $validated = $this->validate();

        return new MentorshipRequestData(
            message: trim((string) $validated['message']),
            language: (string) $validated['language'],
            locationScope: filled($validated['locationScope'] ?? null)
                ? trim((string) $validated['locationScope'])
                : null,
            communicationPreference: (string) $validated['communicationPreference'],
            safetyAcknowledged: (bool) $validated['safetyAcknowledged'],
            idempotencyKey: $idempotencyKey,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\MentorProfileData;
use App\Enums\ForumMentorProfileState;
use App\Models\ForumMentorProfile;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class MentorProfileForm extends Form
{
    public string $state = 'paused';

    public string $headline = '';

    public string $summary = '';

    /** @var list<string> */
    public array $languages = ['en'];

    public string $locationScope = '';

    public string $timezone = 'UTC';

    /** @var list<string> */
    public array $communicationPreferences = ['platform'];

    public string $availability = '';

    public int $capacity = 2;

    public bool $isPublic = true;

    public bool $safetyAcknowledged = false;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'state' => ['required', Rule::enum(ForumMentorProfileState::class)],
            'headline' => ['required', 'string', 'min:5', 'max:160'],
            'summary' => ['required', 'string', 'min:20', 'max:3000'],
            'languages' => ['required', 'array', 'min:1', 'max:3'],
            'languages.*' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'locationScope' => ['nullable', 'string', 'max:160', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'],
            'timezone' => ['required', Rule::in(timezone_identifiers_list())],
            'communicationPreferences' => ['required', 'array', 'size:1'],
            'communicationPreferences.*' => ['required', Rule::in(['platform'])],
            'availability' => ['nullable', 'string', 'max:500'],
            'capacity' => ['required', 'integer', 'min:1', 'max:10'],
            'isPublic' => ['boolean'],
            'safetyAcknowledged' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'state' => __('forum_mentorship.fields.profile_state'),
            'headline' => __('forum_mentorship.fields.headline'),
            'summary' => __('forum_mentorship.fields.summary'),
            'languages' => __('forum_mentorship.fields.languages'),
            'locationScope' => __('forum_mentorship.fields.location_scope'),
            'timezone' => __('forum_mentorship.fields.timezone'),
            'communicationPreferences' => __('forum_mentorship.fields.communication'),
            'availability' => __('forum_mentorship.fields.availability'),
            'capacity' => __('forum_mentorship.fields.capacity'),
            'isPublic' => __('forum_mentorship.fields.public_profile'),
            'safetyAcknowledged' => __('forum_mentorship.fields.safety_acknowledgement'),
        ];
    }

    public function fillFromProfile(ForumMentorProfile $profile): void
    {
        $this->state = $profile->state->value;
        $this->headline = $profile->headline;
        $this->summary = $profile->summary;
        $this->languages = $profile->languages;
        $this->locationScope = $profile->location_scope ?? '';
        $this->timezone = $profile->timezone;
        $this->communicationPreferences = $profile->communication_preferences;
        $this->availability = (string) data_get($profile->availability, 'note', '');
        $this->capacity = $profile->capacity;
        $this->isPublic = $profile->is_public;
        $this->safetyAcknowledged = $profile->safety_acknowledged_at !== null;
    }

    public function data(int $lockVersion): MentorProfileData
    {
        $validated = $this->validate();

        return new MentorProfileData(
            state: ForumMentorProfileState::from((string) $validated['state']),
            headline: trim((string) $validated['headline']),
            summary: trim((string) $validated['summary']),
            languages: array_values($validated['languages']),
            locationScope: filled($validated['locationScope'] ?? null)
                ? trim((string) $validated['locationScope'])
                : null,
            timezone: (string) $validated['timezone'],
            communicationPreferences: array_values(
                $validated['communicationPreferences'],
            ),
            availability: filled($validated['availability'] ?? null)
                ? ['note' => trim((string) $validated['availability'])]
                : [],
            capacity: (int) $validated['capacity'],
            isPublic: (bool) $validated['isPublic'],
            safetyAcknowledged: (bool) $validated['safetyAcknowledged'],
            expectedLockVersion: $lockVersion,
        );
    }
}

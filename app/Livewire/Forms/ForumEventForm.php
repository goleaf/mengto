<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\CreateForumEventData;
use App\Enums\ForumEventAccessibilityStatus;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumEventForm extends Form
{
    public string $title = '';

    public string $summary = '';

    public string $type = 'other';

    public string $visibility = 'public';

    public string $format = 'physical';

    public string $petParticipationMode = 'optional';

    public string $startsAt = '';

    public string $endsAt = '';

    public string $timezone = 'UTC';

    public ?int $capacity = null;

    public string $registrationPolicy = 'open';

    public bool $waitlistEnabled = true;

    public string $locationScope = '';

    public string $exactLocation = '';

    public string $onlineUrl = '';

    public string $attendanceRequirements = '';

    public string $vaccinationRequirements = '';

    public string $vaccinationJurisdiction = '';

    public ?int $minimumAnimalAgeMonths = null;

    public ?int $maximumAnimalAgeMonths = null;

    public string $accessibilityInformation = '';

    public string $accessibilityStatus = 'not_assessed';

    public int $costMinor = 0;

    public string $currency = 'EUR';

    public string $refundPolicy = '';

    public string $photoConsentMode = 'ask_first';

    public string $animalWelfareRules = '';

    public string $emergencyContactPlan = '';

    /** @var list<int> */
    public array $taxonIds = [];

    public string $locale = 'en';

    public string $idempotencyKey = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'summary' => ['required', 'string', 'min:10', 'max:10000'],
            'type' => ['required', Rule::enum(ForumEventType::class)],
            'visibility' => [
                'required',
                Rule::in([
                    ForumEventVisibility::Public->value,
                    ForumEventVisibility::Unlisted->value,
                    ForumEventVisibility::Members->value,
                    ForumEventVisibility::Invitation->value,
                    ForumEventVisibility::Private->value,
                ]),
            ],
            'format' => ['required', Rule::enum(ForumEventFormat::class)],
            'petParticipationMode' => [
                'required',
                Rule::enum(ForumEventPetParticipation::class),
            ],
            'startsAt' => ['required', 'date', 'after:now'],
            'endsAt' => ['required', 'date', 'after:startsAt'],
            'timezone' => ['required', 'timezone:all'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'registrationPolicy' => [
                'required',
                Rule::enum(ForumEventRegistrationPolicy::class),
            ],
            'waitlistEnabled' => ['boolean'],
            'locationScope' => [
                Rule::requiredIf($this->format !== ForumEventFormat::Online->value),
                'nullable',
                'string',
                'max:190',
            ],
            'exactLocation' => ['nullable', 'string', 'max:2000'],
            'onlineUrl' => [
                Rule::requiredIf($this->format !== ForumEventFormat::Physical->value),
                'nullable',
                'url:http,https',
                'max:2000',
            ],
            'attendanceRequirements' => ['nullable', 'string', 'max:5000'],
            'vaccinationRequirements' => ['nullable', 'string', 'max:5000'],
            'vaccinationJurisdiction' => [
                Rule::requiredIf(filled($this->vaccinationRequirements)),
                'nullable',
                'string',
                'max:120',
            ],
            'minimumAnimalAgeMonths' => ['nullable', 'integer', 'min:0', 'max:1200'],
            'maximumAnimalAgeMonths' => [
                'nullable',
                'integer',
                'min:0',
                'max:1200',
                'gte:minimumAnimalAgeMonths',
            ],
            'accessibilityInformation' => ['nullable', 'string', 'max:5000'],
            'accessibilityStatus' => [
                'required',
                Rule::enum(ForumEventAccessibilityStatus::class),
            ],
            'costMinor' => ['required', 'integer', 'min:0', 'max:100000000'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'refundPolicy' => [
                Rule::requiredIf($this->costMinor > 0),
                'nullable',
                'string',
                'max:5000',
            ],
            'photoConsentMode' => [
                'required',
                Rule::enum(ForumEventPhotoConsent::class),
            ],
            'animalWelfareRules' => ['required', 'string', 'min:10', 'max:10000'],
            'emergencyContactPlan' => ['required', 'string', 'min:10', 'max:10000'],
            'taxonIds' => ['array', 'max:5'],
            'taxonIds.*' => [
                'integer',
                'distinct',
                Rule::exists('taxa', 'id')->where('is_active', true)->whereNull('archived_at'),
            ],
            'locale' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'title' => __('forum_events.fields.title'),
            'summary' => __('forum_events.fields.summary'),
            'type' => __('forum_events.fields.type'),
            'visibility' => __('forum_events.fields.visibility'),
            'format' => __('forum_events.fields.format'),
            'petParticipationMode' => __('forum_events.fields.pet_participation_mode'),
            'startsAt' => __('forum_events.fields.starts_at'),
            'endsAt' => __('forum_events.fields.ends_at'),
            'timezone' => __('forum_events.fields.timezone'),
            'capacity' => __('forum_events.fields.capacity'),
            'registrationPolicy' => __('forum_events.fields.registration_policy'),
            'locationScope' => __('forum_events.fields.location_scope'),
            'exactLocation' => __('forum_events.fields.exact_location'),
            'onlineUrl' => __('forum_events.fields.online_url'),
            'animalWelfareRules' => __('forum_events.fields.animal_welfare_rules'),
            'emergencyContactPlan' => __('forum_events.fields.emergency_contact_plan'),
        ];
    }

    public function data(): CreateForumEventData
    {
        $validated = $this->validate();
        $timezone = (string) $validated['timezone'];

        return new CreateForumEventData(
            title: trim((string) $validated['title']),
            summary: trim((string) $validated['summary']),
            type: ForumEventType::from((string) $validated['type']),
            visibility: ForumEventVisibility::from((string) $validated['visibility']),
            format: ForumEventFormat::from((string) $validated['format']),
            startsAt: CarbonImmutable::parse((string) $validated['startsAt'], $timezone),
            endsAt: CarbonImmutable::parse((string) $validated['endsAt'], $timezone),
            timezone: $timezone,
            capacity: isset($validated['capacity']) ? (int) $validated['capacity'] : null,
            registrationPolicy: ForumEventRegistrationPolicy::from(
                (string) $validated['registrationPolicy'],
            ),
            waitlistEnabled: (bool) $validated['waitlistEnabled'],
            locationScope: $this->optionalString($validated, 'locationScope'),
            exactLocation: $this->optionalString($validated, 'exactLocation'),
            onlineUrl: $this->optionalString($validated, 'onlineUrl'),
            attendanceRequirements: $this->optionalString($validated, 'attendanceRequirements'),
            vaccinationRequirements: $this->optionalString($validated, 'vaccinationRequirements'),
            vaccinationJurisdiction: $this->optionalString($validated, 'vaccinationJurisdiction'),
            minimumAnimalAgeMonths: isset($validated['minimumAnimalAgeMonths'])
                ? (int) $validated['minimumAnimalAgeMonths']
                : null,
            maximumAnimalAgeMonths: isset($validated['maximumAnimalAgeMonths'])
                ? (int) $validated['maximumAnimalAgeMonths']
                : null,
            accessibilityInformation: $this->optionalString($validated, 'accessibilityInformation'),
            costMinor: (int) $validated['costMinor'],
            currency: mb_strtoupper((string) $validated['currency']),
            refundPolicy: $this->optionalString($validated, 'refundPolicy'),
            photoConsentMode: ForumEventPhotoConsent::from(
                (string) $validated['photoConsentMode'],
            ),
            animalWelfareRules: trim((string) $validated['animalWelfareRules']),
            emergencyContactPlan: trim((string) $validated['emergencyContactPlan']),
            groupId: null,
            taxonIds: array_values(array_map('intval', $validated['taxonIds'])),
            locale: (string) $validated['locale'],
            idempotencyKey: (string) $validated['idempotencyKey'],
            petParticipationMode: ForumEventPetParticipation::from(
                (string) $validated['petParticipationMode'],
            ),
            accessibilityStatus: ForumEventAccessibilityStatus::from(
                (string) $validated['accessibilityStatus'],
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function optionalString(array $validated, string $key): ?string
    {
        return filled($validated[$key] ?? null)
            ? trim((string) $validated[$key])
            : null;
    }
}

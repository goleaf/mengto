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
use App\Models\User;
use App\Rules\EventOrganizableOrganization;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Livewire\Form;

final class ForumEventForm extends Form
{
    private const LOCAL_DATE_TIME_FORMAT = 'Y-m-d\TH:i';

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

    public ?int $placeId = null;

    public ?int $venueId = null;

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

    public ?int $responsibleOrganizationId = null;

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
                    ForumEventVisibility::Organization->value,
                    ForumEventVisibility::Invitation->value,
                    ForumEventVisibility::Private->value,
                ]),
            ],
            'format' => ['required', Rule::enum(ForumEventFormat::class)],
            'petParticipationMode' => [
                'required',
                Rule::enum(ForumEventPetParticipation::class),
            ],
            'startsAt' => ['required', 'date_format:'.self::LOCAL_DATE_TIME_FORMAT],
            'endsAt' => ['required', 'date_format:'.self::LOCAL_DATE_TIME_FORMAT],
            'timezone' => ['required', 'timezone:all'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'registrationPolicy' => [
                'required',
                Rule::enum(ForumEventRegistrationPolicy::class),
            ],
            'waitlistEnabled' => ['boolean'],
            'locationScope' => [
                Rule::requiredIf(
                    $this->format !== ForumEventFormat::Online->value && $this->placeId === null,
                ),
                'nullable',
                'string',
                'max:190',
            ],
            'exactLocation' => [
                Rule::prohibitedIf($this->placeId !== null),
                'nullable',
                'string',
                'max:2000',
            ],
            'placeId' => [
                Rule::prohibitedIf($this->format === ForumEventFormat::Online->value),
                'nullable',
                'integer',
                'exists:places,id',
            ],
            'venueId' => [
                Rule::prohibitedIf($this->format === ForumEventFormat::Online->value),
                'nullable',
                'integer',
                'exists:venues,id',
            ],
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
            'responsibleOrganizationId' => [
                Rule::requiredIf($this->visibility === ForumEventVisibility::Organization->value),
                'nullable',
                'integer',
                new EventOrganizableOrganization(
                    Auth::user() instanceof User ? Auth::user() : null,
                ),
            ],
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
            'placeId' => __('places.fields.place'),
            'venueId' => __('places.fields.venue'),
            'onlineUrl' => __('forum_events.fields.online_url'),
            'attendanceRequirements' => __('forum_events.fields.attendance_requirements'),
            'vaccinationRequirements' => __('forum_events.fields.vaccination_requirements'),
            'vaccinationJurisdiction' => __('forum_events.fields.vaccination_jurisdiction'),
            'minimumAnimalAgeMonths' => __('forum_events.fields.minimum_animal_age_months'),
            'maximumAnimalAgeMonths' => __('forum_events.fields.maximum_animal_age_months'),
            'accessibilityInformation' => __('forum_events.fields.accessibility_information'),
            'accessibilityStatus' => __('forum_events.fields.accessibility_status'),
            'costMinor' => __('forum_events.fields.cost_minor'),
            'currency' => __('forum_events.fields.currency'),
            'refundPolicy' => __('forum_events.fields.refund_policy'),
            'photoConsentMode' => __('forum_events.fields.photo_consent_mode'),
            'animalWelfareRules' => __('forum_events.fields.animal_welfare_rules'),
            'emergencyContactPlan' => __('forum_events.fields.emergency_contact_plan'),
            'taxonIds' => __('forum_events.fields.taxon_ids'),
            'locale' => __('forum_events.fields.locale'),
            'responsibleOrganizationId' => __('forum_events.fields.responsible_organization'),
        ];
    }

    public function data(): CreateForumEventData
    {
        return $this->makeData($this->validatedInput(false));
    }

    public function draftData(): CreateForumEventData
    {
        return $this->makeData($this->validatedInput(true));
    }

    /** @return array<string, mixed> */
    private function validatedInput(bool $draft): array
    {
        $rules = $this->rules();

        if ($draft) {
            $rules['summary'] = ['nullable', 'string', 'max:10000'];
            $rules['locationScope'] = ['nullable', 'string', 'max:190'];
            $rules['onlineUrl'] = ['nullable', 'url:http,https', 'max:2000'];
            $rules['vaccinationJurisdiction'] = ['nullable', 'string', 'max:120'];
            $rules['refundPolicy'] = ['nullable', 'string', 'max:5000'];
            $rules['animalWelfareRules'] = ['nullable', 'string', 'max:10000'];
            $rules['emergencyContactPlan'] = ['nullable', 'string', 'max:10000'];
            $rules['responsibleOrganizationId'] = [
                'nullable',
                'integer',
                new EventOrganizableOrganization(
                    Auth::user() instanceof User ? Auth::user() : null,
                ),
            ];
        }

        $validated = $this->withValidator(function (Validator $validator) use ($draft): void {
            $validator->after(function (Validator $validator) use ($draft): void {
                if ($validator->errors()->hasAny(['startsAt', 'endsAt', 'timezone'])) {
                    return;
                }

                $startsAt = $this->parseLocalDateTime($this->startsAt, $this->timezone);
                $endsAt = $this->parseLocalDateTime($this->endsAt, $this->timezone);

                if ($startsAt === null || $endsAt === null) {
                    $this->addLocalDateTimeShapeErrors($validator, $startsAt, $endsAt);

                    return;
                }

                if (! $draft
                    && $startsAt->lessThanOrEqualTo(CarbonImmutable::now($this->timezone))
                ) {
                    $validator->errors()->add('startsAt', __('validation.after', [
                        'attribute' => __('forum_events.fields.starts_at'),
                        'date' => 'now',
                    ]));
                }

                if ($endsAt->lessThanOrEqualTo($startsAt)) {
                    $validator->errors()->add('endsAt', __('validation.after', [
                        'attribute' => __('forum_events.fields.ends_at'),
                        'date' => __('forum_events.fields.starts_at'),
                    ]));
                }

                if (! $draft
                    && $this->visibility === ForumEventVisibility::Invitation->value
                    && $this->registrationPolicy !== ForumEventRegistrationPolicy::Invitation->value
                ) {
                    $validator->errors()->add(
                        'registrationPolicy',
                        __('forum_events.validation.invitation_visibility_policy'),
                    );
                }
            });
        })->validate($rules);

        return $validated;
    }

    /** @param array<string, mixed> $validated */
    private function makeData(array $validated): CreateForumEventData
    {
        $timezone = (string) $validated['timezone'];
        $startsAt = $this->parseLocalDateTime((string) $validated['startsAt'], $timezone);
        $endsAt = $this->parseLocalDateTime((string) $validated['endsAt'], $timezone);

        if ($startsAt === null || $endsAt === null) {
            throw new \LogicException(__('messages.validated_event_date_times_could_not_be_parsed'));
        }

        return new CreateForumEventData(
            title: trim((string) $validated['title']),
            summary: trim((string) $validated['summary']),
            type: ForumEventType::from((string) $validated['type']),
            visibility: ForumEventVisibility::from((string) $validated['visibility']),
            format: ForumEventFormat::from((string) $validated['format']),
            startsAt: $startsAt->utc(),
            endsAt: $endsAt->utc(),
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
            responsibleOrganizationId: isset($validated['responsibleOrganizationId'])
                ? (int) $validated['responsibleOrganizationId']
                : null,
            placeId: isset($validated['placeId']) ? (int) $validated['placeId'] : null,
            venueId: isset($validated['venueId']) ? (int) $validated['venueId'] : null,
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

    private function parseLocalDateTime(string $value, string $timezone): ?CarbonImmutable
    {
        try {
            $dateTime = CarbonImmutable::createFromFormat(
                '!'.self::LOCAL_DATE_TIME_FORMAT,
                $value,
                $timezone,
            );
        } catch (InvalidFormatException) {
            return null;
        }

        return $dateTime instanceof CarbonImmutable
            && $dateTime->format(self::LOCAL_DATE_TIME_FORMAT) === $value
            ? $dateTime
            : null;
    }

    private function addLocalDateTimeShapeErrors(
        Validator $validator,
        ?CarbonImmutable $startsAt,
        ?CarbonImmutable $endsAt,
    ): void {
        if ($startsAt === null) {
            $validator->errors()->add('startsAt', __('validation.date_format', [
                'attribute' => __('forum_events.fields.starts_at'),
                'format' => self::LOCAL_DATE_TIME_FORMAT,
            ]));
        }

        if ($endsAt === null) {
            $validator->errors()->add('endsAt', __('validation.date_format', [
                'attribute' => __('forum_events.fields.ends_at'),
                'format' => self::LOCAL_DATE_TIME_FORMAT,
            ]));
        }
    }
}

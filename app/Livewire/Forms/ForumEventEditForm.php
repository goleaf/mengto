<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\UpdateForumEventData;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Models\ForumEvent;
use App\Rules\ApproximateMeetupLocation;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumEventEditForm extends Form
{
    public string $title = '';

    public string $summary = '';

    public string $type = 'other';

    public string $visibility = 'public';

    public string $registrationPolicy = 'open';

    public string $registrationOpensAt = '';

    public string $registrationClosesAt = '';

    public string $timezone = 'UTC';

    public string $petParticipationMode = 'optional';

    public ?int $capacity = null;

    public bool $waitlistEnabled = true;

    public string $locationScope = '';

    public string $exactLocation = '';

    public string $attendanceRequirements = '';

    public string $accessibilityInformation = '';

    public string $animalWelfareRules = '';

    public string $emergencyContactPlan = '';

    public string $idempotencyKey = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'summary' => ['required', 'string', 'min:10', 'max:10000'],
            'type' => ['required', Rule::enum(ForumEventType::class)],
            'visibility' => ['required', Rule::enum(ForumEventVisibility::class)],
            'registrationPolicy' => ['required', Rule::enum(ForumEventRegistrationPolicy::class)],
            'registrationOpensAt' => ['nullable', 'date'],
            'registrationClosesAt' => ['nullable', 'date'],
            'petParticipationMode' => ['required', Rule::enum(ForumEventPetParticipation::class)],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'waitlistEnabled' => ['boolean'],
            'locationScope' => ['nullable', 'string', 'max:190', new ApproximateMeetupLocation],
            'exactLocation' => ['nullable', 'string', 'max:2000'],
            'attendanceRequirements' => ['nullable', 'string', 'max:5000'],
            'accessibilityInformation' => ['nullable', 'string', 'max:5000'],
            'animalWelfareRules' => ['required', 'string', 'min:10', 'max:10000'],
            'emergencyContactPlan' => ['required', 'string', 'min:10', 'max:10000'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ];
    }

    public function fillFromEvent(ForumEvent $event): void
    {
        $this->title = $event->title;
        $this->summary = $event->summary;
        $this->type = $event->type->value;
        $this->visibility = $event->visibility->value;
        $this->registrationPolicy = $event->registration_policy->value;
        $this->registrationOpensAt = $event->registration_opens_at?->setTimezone($event->timezone)->format('Y-m-d\TH:i') ?? '';
        $this->registrationClosesAt = $event->registration_closes_at?->setTimezone($event->timezone)->format('Y-m-d\TH:i') ?? '';
        $this->timezone = $event->timezone;
        $this->petParticipationMode = $event->pet_participation_mode->value;
        $this->capacity = $event->capacity;
        $this->waitlistEnabled = $event->waitlist_enabled;
        $this->locationScope = $event->location_scope ?? '';
        $this->exactLocation = $event->exact_location ?? '';
        $this->attendanceRequirements = $event->attendance_requirements ?? '';
        $this->accessibilityInformation = $event->accessibility_information ?? '';
        $this->animalWelfareRules = $event->animal_welfare_rules;
        $this->emergencyContactPlan = $event->emergency_contact_plan;
        $this->idempotencyKey = (string) str()->uuid();
    }

    public function data(): UpdateForumEventData
    {
        $validated = $this->validate();

        return new UpdateForumEventData(
            title: trim((string) $validated['title']),
            summary: trim((string) $validated['summary']),
            type: ForumEventType::from((string) $validated['type']),
            visibility: ForumEventVisibility::from((string) $validated['visibility']),
            registrationPolicy: ForumEventRegistrationPolicy::from((string) $validated['registrationPolicy']),
            petParticipationMode: ForumEventPetParticipation::from((string) $validated['petParticipationMode']),
            capacity: isset($validated['capacity']) ? (int) $validated['capacity'] : null,
            waitlistEnabled: (bool) $validated['waitlistEnabled'],
            locationScope: $this->optionalString($validated, 'locationScope'),
            exactLocation: $this->optionalString($validated, 'exactLocation'),
            attendanceRequirements: $this->optionalString($validated, 'attendanceRequirements'),
            accessibilityInformation: $this->optionalString($validated, 'accessibilityInformation'),
            animalWelfareRules: trim((string) $validated['animalWelfareRules']),
            emergencyContactPlan: trim((string) $validated['emergencyContactPlan']),
            idempotencyKey: (string) $validated['idempotencyKey'],
            registrationOpensAt: filled($validated['registrationOpensAt'] ?? null)
                ? CarbonImmutable::parse((string) $validated['registrationOpensAt'], $this->timezone)->utc()
                : null,
            registrationClosesAt: filled($validated['registrationClosesAt'] ?? null)
                ? CarbonImmutable::parse((string) $validated['registrationClosesAt'], $this->timezone)->utc()
                : null,
        );
    }

    /** @param array<string, mixed> $data */
    private function optionalString(array $data, string $key): ?string
    {
        $value = trim((string) ($data[$key] ?? ''));

        return $value === '' ? null : $value;
    }
}

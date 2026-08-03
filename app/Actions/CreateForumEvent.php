<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateForumEventData;
use App\Enums\ForumEventAccessibilityStatus;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Models\ForumEvent;
use App\Models\ForumGroup;
use App\Models\Taxon;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class CreateForumEvent
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
        private InitializeForumEventLifecycle $initializeLifecycle,
    ) {}

    public function handle(User $actor, CreateForumEventData $data): ForumEvent
    {
        $this->gate->forUser($actor)->authorize('create', ForumEvent::class);
        $this->validate($data);

        $existing = ForumEvent::query()
            ->where('creation_idempotency_key', $data->idempotencyKey)
            ->first();

        if ($existing !== null) {
            if (! $existing->isOrganizer($actor)) {
                throw ValidationException::withMessages([
                    'eventForm.title' => __('forum_events.validation.idempotency_conflict'),
                ]);
            }

            return $existing;
        }

        $group = $data->groupId === null
            ? null
            : ForumGroup::query()->findOrFail($data->groupId);

        if ($group !== null) {
            $this->gate->forUser($actor)->authorize('createContent', $group);
        }

        $taxa = Taxon::query()
            ->select(['id'])
            ->active()
            ->whereIn('id', $data->taxonIds)
            ->get();

        if ($taxa->count() !== count(array_unique($data->taxonIds))) {
            throw ValidationException::withMessages([
                'eventForm.taxonIds' => __('forum_events.validation.taxa'),
            ]);
        }

        return DB::transaction(function () use ($actor, $data, $group, $taxa): ForumEvent {
            $event = ForumEvent::query()->create([
                'organizer_user_id' => $actor->id,
                'owner_user_id' => $actor->id,
                'organizer_key' => $actor->actor_key,
                'organizer_name' => $actor->name,
                'forum_group_id' => $group?->id,
                'stable_key' => Str::slug($data->title).'-'.Str::lower((string) Str::ulid()),
                'creation_idempotency_key' => $data->idempotencyKey,
                'is_system_managed' => false,
                'title' => trim($data->title),
                'summary' => trim($data->summary),
                'type' => $data->type,
                'visibility' => $data->visibility,
                'format' => $data->format,
                'pet_participation_mode' => $data->petParticipationMode,
                'status' => ForumEventStatus::Scheduled,
                'locale' => $data->locale,
                'starts_at' => $data->startsAt,
                'ends_at' => $data->endsAt,
                'timezone' => $data->timezone,
                'capacity' => $data->capacity,
                'registration_policy' => $data->registrationPolicy,
                'waitlist_enabled' => $data->waitlistEnabled,
                'location_scope' => $data->locationScope,
                'exact_location' => $data->exactLocation,
                'online_url' => $data->onlineUrl,
                'attendance_requirements' => $data->attendanceRequirements,
                'vaccination_requirements' => $data->vaccinationRequirements,
                'vaccination_jurisdiction' => $data->vaccinationJurisdiction,
                'minimum_animal_age_months' => $data->minimumAnimalAgeMonths,
                'maximum_animal_age_months' => $data->maximumAnimalAgeMonths,
                'accessibility_information' => $data->accessibilityInformation,
                'accessibility_status' => $data->accessibilityStatus,
                'cost_minor' => $data->costMinor,
                'currency' => Str::upper($data->currency),
                'refund_policy' => $data->refundPolicy,
                'photo_consent_mode' => $data->photoConsentMode,
                'animal_welfare_rules' => trim($data->animalWelfareRules),
                'emergency_contact_plan' => trim($data->emergencyContactPlan),
            ]);

            if ($taxa->isNotEmpty()) {
                $event->taxa()->attach($taxa->pluck('id')->mapWithKeys(
                    static fn (int $id, int $index): array => [
                        $id => ['is_primary' => $index === 0],
                    ],
                )->all());
            }

            $this->initializeLifecycle->handle($event, $actor, 'event-created');

            $this->audit->record(
                event: $event,
                actor: $actor,
                eventType: 'created',
                reasonCode: 'event-created',
                summaryTranslationKey: 'forum_events.history.created',
                toStatus: ForumEventStatus::Scheduled->value,
                metadata: [
                    'group_id' => $group?->id,
                    'taxon_ids' => $taxa->pluck('id')->all(),
                ],
                idempotencyKey: 'event:create:'.$data->idempotencyKey,
            );

            return $event;
        }, 3);
    }

    private function validate(CreateForumEventData $data): void
    {
        Validator::make([
            'title' => $data->title,
            'summary' => $data->summary,
            'type' => $data->type->value,
            'visibility' => $data->visibility->value,
            'format' => $data->format->value,
            'pet_participation_mode' => $data->petParticipationMode->value,
            'starts_at' => $data->startsAt->toAtomString(),
            'ends_at' => $data->endsAt->toAtomString(),
            'timezone' => $data->timezone,
            'capacity' => $data->capacity,
            'registration_policy' => $data->registrationPolicy->value,
            'location_scope' => $data->locationScope,
            'exact_location' => $data->exactLocation,
            'online_url' => $data->onlineUrl,
            'attendance_requirements' => $data->attendanceRequirements,
            'vaccination_requirements' => $data->vaccinationRequirements,
            'vaccination_jurisdiction' => $data->vaccinationJurisdiction,
            'minimum_animal_age_months' => $data->minimumAnimalAgeMonths,
            'maximum_animal_age_months' => $data->maximumAnimalAgeMonths,
            'accessibility_information' => $data->accessibilityInformation,
            'accessibility_status' => $data->accessibilityStatus->value,
            'cost_minor' => $data->costMinor,
            'currency' => Str::upper($data->currency),
            'refund_policy' => $data->refundPolicy,
            'photo_consent_mode' => $data->photoConsentMode->value,
            'animal_welfare_rules' => $data->animalWelfareRules,
            'emergency_contact_plan' => $data->emergencyContactPlan,
            'group_id' => $data->groupId,
            'taxon_ids' => $data->taxonIds,
            'locale' => $data->locale,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'summary' => ['required', 'string', 'min:10', 'max:10000'],
            'type' => ['required', Rule::enum(ForumEventType::class)],
            'visibility' => ['required', Rule::enum(ForumEventVisibility::class)],
            'format' => ['required', Rule::enum(ForumEventFormat::class)],
            'pet_participation_mode' => [
                'required',
                Rule::enum(ForumEventPetParticipation::class),
            ],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'timezone' => ['required', 'timezone:all'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'registration_policy' => [
                'required',
                Rule::enum(ForumEventRegistrationPolicy::class),
            ],
            'location_scope' => [
                Rule::requiredIf($data->format !== ForumEventFormat::Online),
                'nullable',
                'string',
                'max:190',
            ],
            'exact_location' => ['nullable', 'string', 'max:2000'],
            'online_url' => [
                Rule::requiredIf($data->format !== ForumEventFormat::Physical),
                'nullable',
                'url:http,https',
                'max:2000',
            ],
            'attendance_requirements' => ['nullable', 'string', 'max:5000'],
            'vaccination_requirements' => ['nullable', 'string', 'max:5000'],
            'vaccination_jurisdiction' => [
                Rule::requiredIf(filled($data->vaccinationRequirements)),
                'nullable',
                'string',
                'max:120',
            ],
            'minimum_animal_age_months' => ['nullable', 'integer', 'min:0', 'max:1200'],
            'maximum_animal_age_months' => [
                'nullable',
                'integer',
                'min:0',
                'max:1200',
                'gte:minimum_animal_age_months',
            ],
            'accessibility_information' => ['nullable', 'string', 'max:5000'],
            'accessibility_status' => [
                'required',
                Rule::enum(ForumEventAccessibilityStatus::class),
            ],
            'cost_minor' => ['required', 'integer', 'min:0', 'max:100000000'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'refund_policy' => [
                Rule::requiredIf($data->costMinor > 0),
                'nullable',
                'string',
                'max:5000',
            ],
            'photo_consent_mode' => [
                'required',
                Rule::enum(ForumEventPhotoConsent::class),
            ],
            'animal_welfare_rules' => ['required', 'string', 'min:10', 'max:10000'],
            'emergency_contact_plan' => ['required', 'string', 'min:10', 'max:10000'],
            'group_id' => ['nullable', 'integer', 'exists:forum_groups,id'],
            'taxon_ids' => ['array', 'max:20'],
            'taxon_ids.*' => ['integer', 'distinct'],
            'locale' => [
                'required',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();

        if ($data->visibility === ForumEventVisibility::Group && $data->groupId === null) {
            throw ValidationException::withMessages([
                'eventForm.groupId' => __('forum_events.validation.group_visibility'),
            ]);
        }
    }
}

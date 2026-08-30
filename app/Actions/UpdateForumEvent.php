<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UpdateForumEventData;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Models\ForumEvent;
use App\Models\User;
use App\Services\ForumEventAudit;
use App\Services\ForumEventNotifier;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class UpdateForumEvent
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
        private ForumEventNotifier $notifier,
    ) {}

    public function handle(User $actor, ForumEvent $event, UpdateForumEventData $data): ForumEvent
    {
        $this->gate->forUser($actor)->authorize('update', $event);

        [$updated, $materialChanges] = DB::transaction(function () use ($actor, $data, $event): array {
            $locked = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $this->gate->forUser($actor)->authorize('update', $locked);
            $this->validate($locked, $data);

            if (in_array($locked->status, [
                ForumEventStatus::Cancelled,
                ForumEventStatus::Archived,
                ForumEventStatus::RetentionDeletionPending,
            ], true)) {
                throw ValidationException::withMessages([
                    'event' => __('forum_events.validation.edit_status'),
                ]);
            }

            if ($data->visibility === ForumEventVisibility::Group && $locked->forum_group_id === null) {
                throw ValidationException::withMessages([
                    'editForm.visibility' => __('forum_events.validation.group_visibility'),
                ]);
            }

            if ($data->visibility === ForumEventVisibility::Organization
                && $locked->responsible_organization_id === null
            ) {
                throw ValidationException::withMessages([
                    'editForm.visibility' => __('forum_events.validation.organization_visibility'),
                ]);
            }

            if ($data->visibility === ForumEventVisibility::Invitation
                && $data->registrationPolicy !== ForumEventRegistrationPolicy::Invitation
            ) {
                throw ValidationException::withMessages([
                    'editForm.registrationPolicy' => __('forum_events.validation.invitation_visibility_policy'),
                ]);
            }

            if ($locked->place_id !== null && $data->exactLocation !== null) {
                throw ValidationException::withMessages([
                    'editForm.exactLocation' => __('forum_events.validation.place_exact_location'),
                ]);
            }

            $capacityRegistrations = $locked->registrations()
                ->whereIn('status', collect(ForumEventRegistrationStatus::cases())
                    ->filter(static fn (ForumEventRegistrationStatus $status): bool => $status->consumesSeat())
                    ->map(static fn (ForumEventRegistrationStatus $status): string => $status->value)
                    ->all());
            $usedCapacity = (clone $capacityRegistrations)->count()
                + (int) (clone $capacityRegistrations)->sum('guest_count');

            if ($data->capacity !== null && $data->capacity < $usedCapacity) {
                throw ValidationException::withMessages([
                    'editForm.capacity' => __('forum_events.validation.capacity_below_confirmed'),
                ]);
            }

            $attributes = [
                'title' => $data->title,
                'summary' => $data->summary,
                'type' => $data->type,
                'visibility' => $data->visibility,
                'registration_policy' => $data->registrationPolicy,
                'pet_participation_mode' => $data->petParticipationMode,
                'capacity' => $data->capacity,
                'waitlist_enabled' => $data->waitlistEnabled,
                'location_scope' => $data->locationScope,
                'exact_location' => $data->exactLocation,
                'attendance_requirements' => $data->attendanceRequirements,
                'accessibility_information' => $data->accessibilityInformation,
                'animal_welfare_rules' => $data->animalWelfareRules,
                'emergency_contact_plan' => $data->emergencyContactPlan,
            ];
            $materialFields = [
                'visibility',
                'registration_policy',
                'capacity',
                'location_scope',
                'exact_location',
            ];
            $materialChanges = array_values(array_filter(
                $materialFields,
                static fn (string $field): bool => $locked->getAttribute($field) !== $attributes[$field],
            ));

            if ($locked->starts_at->isPast() && $materialChanges !== []) {
                throw ValidationException::withMessages([
                    'event' => __('forum_events.validation.started_material_change'),
                ]);
            }

            $locked->forceFill($attributes)->save();

            $occurrence = $locked->occurrences()
                ->where('is_override', false)
                ->lockForUpdate()
                ->first();
            if ($occurrence !== null) {
                $occurrence->forceFill([
                    'capacity' => $data->capacity,
                    'location_scope' => $data->locationScope,
                    'exact_location' => $data->exactLocation,
                ])->save();
            }

            $this->audit->record(
                event: $locked,
                actor: $actor,
                eventType: 'updated',
                reasonCode: $materialChanges === [] ? 'event-minor-update' : 'event-material-update',
                summaryTranslationKey: 'forum_events.history.updated',
                metadata: ['changed_fields' => array_keys($locked->getChanges())],
                idempotencyKey: 'event:update:'.$data->idempotencyKey,
            );

            return [$locked, $materialChanges];
        }, 3);

        if ($materialChanges !== []) {
            $updated->registrations()
                ->whereIn('status', ForumEvent::participantAccessStatusValues())
                ->with('user:id,actor_key,locale')
                ->orderBy('id')
                ->chunkById(100, function ($registrations) use ($updated): void {
                    foreach ($registrations as $registration) {
                        $this->notifier->send(
                            $registration->user,
                            $updated,
                            'event-material-update',
                            'forum_events.notifications.material_update_title',
                            'forum_events.notifications.material_update_body',
                            'event-material-update:'.$updated->id.':'.$updated->updated_at?->timestamp.':'.$registration->user_id,
                        );
                    }
                });
        }

        return $updated;
    }

    private function validate(ForumEvent $event, UpdateForumEventData $data): void
    {
        Validator::make([
            'title' => $data->title,
            'summary' => $data->summary,
            'type' => $data->type->value,
            'visibility' => $data->visibility->value,
            'registration_policy' => $data->registrationPolicy->value,
            'pet_participation_mode' => $data->petParticipationMode->value,
            'capacity' => $data->capacity,
            'location_scope' => $data->locationScope,
            'exact_location' => $data->exactLocation,
            'attendance_requirements' => $data->attendanceRequirements,
            'accessibility_information' => $data->accessibilityInformation,
            'animal_welfare_rules' => $data->animalWelfareRules,
            'emergency_contact_plan' => $data->emergencyContactPlan,
            'idempotency_key' => $data->idempotencyKey,
        ], [
            'title' => ['required', 'string', 'min:4', 'max:180'],
            'summary' => ['required', 'string', 'min:10', 'max:10000'],
            'type' => ['required', Rule::enum(ForumEventType::class)],
            'visibility' => ['required', Rule::enum(ForumEventVisibility::class)],
            'registration_policy' => [
                'required',
                Rule::enum(ForumEventRegistrationPolicy::class),
            ],
            'pet_participation_mode' => [
                'required',
                Rule::enum(ForumEventPetParticipation::class),
            ],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'location_scope' => [
                Rule::requiredIf($event->format->value !== 'online' && $event->place_id === null),
                'nullable',
                'string',
                'max:190',
            ],
            'exact_location' => [
                Rule::prohibitedIf($event->place_id !== null),
                'nullable',
                'string',
                'max:2000',
            ],
            'attendance_requirements' => ['nullable', 'string', 'max:5000'],
            'accessibility_information' => ['nullable', 'string', 'max:5000'],
            'animal_welfare_rules' => ['required', 'string', 'min:10', 'max:10000'],
            'emergency_contact_plan' => ['required', 'string', 'min:10', 'max:10000'],
            'idempotency_key' => ['required', 'string', 'min:16', 'max:190'],
        ])->validate();
    }
}

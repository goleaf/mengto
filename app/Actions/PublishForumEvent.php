<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumEventFormat;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventVisibility;
use App\Models\ForumEvent;
use App\Models\User;
use App\Services\ForumEventAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final readonly class PublishForumEvent
{
    public function __construct(
        private Gate $gate,
        private ForumEventAudit $audit,
    ) {}

    public function handle(User $actor, ForumEvent $event): ForumEvent
    {
        $this->gate->forUser($actor)->authorize('publish', $event);

        return DB::transaction(function () use ($actor, $event): ForumEvent {
            $locked = ForumEvent::query()->lockForUpdate()->findOrFail($event->id);
            $this->gate->forUser($actor)->authorize('publish', $locked);

            if ($locked->status === ForumEventStatus::Scheduled) {
                return $locked;
            }

            if (! in_array($locked->status, [
                ForumEventStatus::Draft,
                ForumEventStatus::Incomplete,
            ], true)) {
                throw ValidationException::withMessages([
                    'event' => __('forum_events.validation.publish_status'),
                ]);
            }

            Validator::make([
                'title' => $locked->title,
                'summary' => $locked->summary,
                'starts_at' => $locked->starts_at->toAtomString(),
                'ends_at' => $locked->ends_at->toAtomString(),
                'timezone' => $locked->timezone,
                'location_scope' => $locked->location_scope,
                'online_url' => $locked->online_url,
                'registration_policy' => $locked->registration_policy->value,
                'registration_opens_at' => $locked->registration_opens_at?->toAtomString(),
                'registration_closes_at' => $locked->registration_closes_at?->toAtomString(),
                'visibility' => $locked->visibility->value,
                'group_id' => $locked->forum_group_id,
                'responsible_organization_id' => $locked->responsible_organization_id,
                'animal_welfare_rules' => $locked->animal_welfare_rules,
                'emergency_contact_plan' => $locked->emergency_contact_plan,
            ], [
                'title' => ['required', 'string', 'min:4', 'max:180'],
                'summary' => ['required', 'string', 'min:10', 'max:10000'],
                'starts_at' => ['required', 'date', 'after:now'],
                'ends_at' => ['required', 'date', 'after:starts_at'],
                'timezone' => ['required', 'timezone:all'],
                'location_scope' => [
                    Rule::requiredIf($locked->format !== ForumEventFormat::Online),
                    'nullable',
                    'string',
                    'max:190',
                ],
                'online_url' => [
                    Rule::requiredIf($locked->format !== ForumEventFormat::Physical),
                    'nullable',
                    'url:http,https',
                    'max:2000',
                ],
                'registration_policy' => [
                    'required',
                    Rule::enum(ForumEventRegistrationPolicy::class),
                ],
                'registration_opens_at' => ['nullable', 'date'],
                'registration_closes_at' => ['nullable', 'date'],
                'visibility' => ['required', Rule::enum(ForumEventVisibility::class)],
                'group_id' => [
                    Rule::requiredIf($locked->visibility === ForumEventVisibility::Group),
                    'nullable',
                    'integer',
                ],
                'responsible_organization_id' => [
                    Rule::requiredIf($locked->visibility === ForumEventVisibility::Organization),
                    'nullable',
                    'integer',
                ],
                'animal_welfare_rules' => ['required', 'string', 'min:10', 'max:10000'],
                'emergency_contact_plan' => ['required', 'string', 'min:10', 'max:10000'],
            ])->validate();

            if ($locked->registration_opens_at !== null
                && ! $locked->registration_opens_at->isBefore($locked->starts_at)
            ) {
                throw ValidationException::withMessages([
                    'registration_opens_at' => __('forum_events.validation.registration_window_before_start'),
                ]);
            }

            if ($locked->registration_closes_at !== null
                && $locked->registration_closes_at->isAfter($locked->starts_at)
            ) {
                throw ValidationException::withMessages([
                    'registration_closes_at' => __('forum_events.validation.registration_window_before_start'),
                ]);
            }

            if ($locked->registration_opens_at !== null
                && $locked->registration_closes_at !== null
                && ! $locked->registration_closes_at->isAfter($locked->registration_opens_at)
            ) {
                throw ValidationException::withMessages([
                    'registration_closes_at' => __('forum_events.validation.registration_window_order'),
                ]);
            }

            if ($locked->visibility === ForumEventVisibility::Invitation
                && $locked->registration_policy !== ForumEventRegistrationPolicy::Invitation
            ) {
                throw ValidationException::withMessages([
                    'registration_policy' => __('forum_events.validation.invitation_visibility_policy'),
                ]);
            }

            $fromStatus = $locked->status;
            $locked->forceFill([
                'status' => ForumEventStatus::Scheduled,
                'published_at' => now(),
            ])->save();
            $locked->occurrences()
                ->whereIn('status', [
                    ForumEventStatus::Draft->value,
                    ForumEventStatus::Incomplete->value,
                ])
                ->update([
                    'status' => ForumEventStatus::Scheduled->value,
                    'updated_at' => now(),
                ]);
            $this->audit->record(
                event: $locked,
                actor: $actor,
                eventType: 'published',
                reasonCode: 'event-published',
                summaryTranslationKey: 'forum_events.history.published',
                fromStatus: $fromStatus->value,
                toStatus: ForumEventStatus::Scheduled->value,
                idempotencyKey: 'event:publish:'.$locked->id,
            );

            return $locked;
        }, 3);
    }
}

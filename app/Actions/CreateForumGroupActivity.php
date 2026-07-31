<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateForumEventData;
use App\Data\CreateForumGroupActivityData;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Enums\ForumGroupActivityStatus;
use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateForumGroupActivity
{
    public function __construct(
        private readonly Gate $gate,
        private readonly CreateForumEvent $createEvent,
    ) {}

    public function handle(
        User $actor,
        ForumGroup $group,
        CreateForumGroupActivityData $data,
    ): ForumGroupActivity {
        $this->gate->forUser($actor)->authorize('create', [ForumGroupActivity::class, $group]);
        $this->validate($group, $data);

        return DB::transaction(function () use ($actor, $data, $group): ForumGroupActivity {
            $event = $this->createEvent->handle(
                $actor,
                $this->eventData($group, $data),
            );
            $existing = ForumGroupActivity::query()
                ->where('creation_idempotency_key', $data->idempotencyKey)
                ->first();

            if ($existing !== null) {
                if ($existing->forum_group_id !== $group->id
                    || $existing->created_by_user_id !== $actor->id
                ) {
                    throw ValidationException::withMessages([
                        'activity' => __('forum_polls.validation.idempotency_conflict'),
                    ]);
                }

                if ($existing->forum_event_id === null) {
                    $existing->forceFill(['forum_event_id' => $event->id])->save();
                }

                return $existing;
            }

            return ForumGroupActivity::query()->create([
                'forum_group_id' => $group->id,
                'forum_event_id' => $event->id,
                'created_by_user_id' => $actor->id,
                'stable_key' => 'group-activity-'.Str::lower((string) Str::ulid()),
                'creation_idempotency_key' => $data->idempotencyKey,
                'title' => trim($data->title),
                'summary' => trim($data->summary),
                'format' => $data->format,
                'status' => ForumGroupActivityStatus::Scheduled,
                'starts_at' => $data->startsAt,
                'ends_at' => $data->endsAt,
                'timezone' => $data->timezone,
                'location_scope' => $data->format->value === 'online'
                    ? null
                    : $data->locationScope,
                'capacity' => $data->capacity,
                'participation_notes' => $data->participationNotes,
            ]);
        }, 3);
    }

    private function eventData(
        ForumGroup $group,
        CreateForumGroupActivityData $data,
    ): CreateForumEventData {
        $participationNotes = filled($data->participationNotes)
            ? trim((string) $data->participationNotes)
            : null;
        $welfareRules = $participationNotes !== null
            && mb_strlen($participationNotes) >= 10
            ? $participationNotes
            : __('forum_events.defaults.group_welfare_rules');

        return new CreateForumEventData(
            title: $data->title,
            summary: $data->summary,
            type: ForumEventType::ClubMeetup,
            visibility: ForumEventVisibility::Group,
            format: ForumEventFormat::from($data->format->value),
            startsAt: $data->startsAt,
            endsAt: $data->endsAt,
            timezone: $data->timezone,
            capacity: $data->capacity,
            registrationPolicy: ForumEventRegistrationPolicy::Approval,
            waitlistEnabled: true,
            locationScope: $data->locationScope,
            exactLocation: null,
            onlineUrl: $data->onlineUrl,
            attendanceRequirements: $participationNotes,
            vaccinationRequirements: null,
            vaccinationJurisdiction: null,
            minimumAnimalAgeMonths: null,
            maximumAnimalAgeMonths: null,
            accessibilityInformation: null,
            costMinor: 0,
            currency: 'EUR',
            refundPolicy: null,
            photoConsentMode: ForumEventPhotoConsent::AskFirst,
            animalWelfareRules: $welfareRules,
            emergencyContactPlan: __('forum_events.defaults.group_emergency_plan'),
            groupId: $group->id,
            taxonIds: $group->taxa()
                ->orderByPivot('is_primary', 'desc')
                ->pluck('taxa.id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all(),
            locale: $group->default_locale,
            idempotencyKey: 'group-activity-event:'.$data->idempotencyKey,
        );
    }

    private function validate(
        ForumGroup $group,
        CreateForumGroupActivityData $data,
    ): void {
        $errors = [];

        if (mb_strlen(trim($data->title)) < 4 || mb_strlen(trim($data->title)) > 180) {
            $errors['activityTitle'] = __('forum_polls.validation.activity_title');
        }

        if (mb_strlen(trim($data->summary)) < 10 || mb_strlen(trim($data->summary)) > 3000) {
            $errors['activitySummary'] = __('forum_polls.validation.activity_summary');
        }

        if (! $data->startsAt->isFuture() || ! $data->endsAt->isAfter($data->startsAt)) {
            $errors['activityEndsAt'] = __('forum_polls.validation.activity_dates');
        }

        if (! in_array($data->timezone, timezone_identifiers_list(), true)) {
            $errors['activityTimezone'] = __('forum_polls.validation.timezone');
        }

        if ($data->format->value !== 'online'
            && ($data->locationScope === null || trim($data->locationScope) === '')
        ) {
            $errors['activityLocation'] = __('forum_polls.validation.activity_location');
        }

        if ($data->format->value !== 'physical' && blank($data->onlineUrl)) {
            $errors['activityOnlineUrl'] = __('forum_events.validation.online_url');
        }

        if ($data->capacity !== null && ($data->capacity < 1 || $data->capacity > 100000)) {
            $errors['activityCapacity'] = __('forum_polls.validation.activity_capacity');
        }

        if ($group->status->value !== 'active') {
            $errors['activity'] = __('forum_polls.validation.group_not_active');
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}

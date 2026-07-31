<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ForumEventBackfillResult;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Enums\ForumGroupActivityStatus;
use App\Models\ForumEvent;
use App\Models\ForumGroupActivity;
use App\Models\User;
use App\Services\EventCatalog;
use App\Services\EventContentCatalog;
use App\Services\ForumEventAudit;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class BackfillForumEvents
{
    public function __construct(
        private EventCatalog $catalog,
        private EventContentCatalog $content,
        private ForumEventAudit $audit,
    ) {}

    public function handle(): ForumEventBackfillResult
    {
        $catalogCreated = 0;
        $catalogUpdated = 0;
        $groupActivitiesCreated = 0;
        $groupActivitiesLinked = 0;
        $reviewRequired = 0;
        $previousLocale = app()->getLocale();
        app()->setLocale('en');

        try {
            foreach ($this->catalog->all() as $record) {
                $existing = ForumEvent::query()
                    ->where('stable_key', (string) $record['key'])
                    ->first();
                $event = $this->backfillCatalogRecord($record, $existing);
                $existing === null ? $catalogCreated++ : $catalogUpdated++;
                $reviewRequired += (bool) data_get($event->metadata, 'requires_review') ? 1 : 0;
            }

            ForumGroupActivity::query()
                ->with(['creator:id,actor_key,name', 'group.taxa:id'])
                ->whereNull('forum_event_id')
                ->orderBy('id')
                ->lazyById(100)
                ->each(function (ForumGroupActivity $activity) use (
                    &$groupActivitiesCreated,
                    &$groupActivitiesLinked,
                    &$reviewRequired,
                ): void {
                    [$created, $requiresReview] = $this->backfillGroupActivity($activity);
                    $created ? $groupActivitiesCreated++ : $groupActivitiesLinked++;
                    $reviewRequired += $requiresReview ? 1 : 0;
                });
        } finally {
            app()->setLocale($previousLocale);
        }

        return new ForumEventBackfillResult(
            catalogCreated: $catalogCreated,
            catalogUpdated: $catalogUpdated,
            groupActivitiesCreated: $groupActivitiesCreated,
            groupActivitiesLinked: $groupActivitiesLinked,
            reviewRequired: $reviewRequired,
        );
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function backfillCatalogRecord(
        array $record,
        ?ForumEvent $existing,
    ): ForumEvent {
        $content = $this->content->content($record);
        $organizerKey = Str::slug((string) $record['organizer']);
        $organizer = User::query()
            ->select(['id', 'actor_key', 'name'])
            ->where('actor_key', $organizerKey)
            ->first();

        if (($record['managed_by_current_user'] ?? false) === true) {
            $currentUser = User::query()
                ->select(['id', 'actor_key', 'name'])
                ->where('actor_key', 'mia-carter')
                ->first();
            $organizer = $currentUser ?? $organizer;
            $organizerKey = $organizer instanceof User
                ? $organizer->actor_key
                : 'mia-carter';
        }

        $startsAt = CarbonImmutable::parse((string) $record['starts_at']);
        $endsAt = CarbonImmutable::parse((string) $record['ends_at']);
        $rules = collect($content['rules'])
            ->map(static fn (array $rule): string => trim($rule['title'].': '.$rule['description']))
            ->implode("\n");
        $safety = collect($content['safety'])
            ->map(static fn (array $item): string => trim($item['title'].': '.$item['description']))
            ->implode("\n");
        $accessibility = collect($content['location']['details'])
            ->firstWhere('label', __('messages.accessibility_d3368cbffe'))['value'] ?? null;
        $requiresReview = $organizer === null
            || ($record['format'] === 'online' && blank($record['online_link'] ?? null));
        $attributes = [
            'organizer_user_id' => $organizer?->id,
            'organizer_key' => $organizerKey,
            'organizer_name' => (string) $record['organizer'],
            'forum_group_id' => null,
            'creation_idempotency_key' => 'legacy-event:'.(string) $record['key'],
            'is_system_managed' => true,
            'legacy_source_key' => (string) $record['key'],
            'title' => (string) $record['title'],
            'summary' => (string) ($record['description'] ?? $record['short_description']),
            'type' => $this->eventType((string) $record['event_type']),
            'visibility' => $this->visibility((string) $record['privacy']),
            'format' => $this->format((string) $record['format']),
            'status' => $endsAt->isPast()
                ? ForumEventStatus::Completed
                : ForumEventStatus::Scheduled,
            'locale' => 'en',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'timezone' => (string) $record['timezone'],
            'capacity' => (int) $record['capacity'],
            'registration_policy' => $this->registrationPolicy(
                (string) $record['registration_policy'],
            ),
            'waitlist_enabled' => true,
            'location_scope' => (string) $record['general_location'],
            'exact_location' => filled($record['exact_location'] ?? null)
                ? (string) $record['exact_location']
                : null,
            'online_url' => filled($record['online_link'] ?? null)
                ? (string) $record['online_link']
                : null,
            'attendance_requirements' => (string) $record['pet_requirements'],
            'vaccination_requirements' => $this->vaccinationRequirement(
                (string) $record['key'],
                (string) $record['pet_requirements'],
            ),
            'vaccination_jurisdiction' => $this->vaccinationRequirement(
                (string) $record['key'],
                (string) $record['pet_requirements'],
            ) === null ? null : 'US-OR',
            'minimum_animal_age_months' => $record['key'] === 'puppy-social-lab' ? 3 : null,
            'maximum_animal_age_months' => $record['key'] === 'puppy-social-lab' ? 6 : null,
            'accessibility_information' => is_string($accessibility) ? $accessibility : null,
            'cost_minor' => (int) $record['price_minor'],
            'currency' => Str::upper((string) $record['currency']),
            'refund_policy' => $record['price_minor'] > 0
                ? __('forum_events.defaults.legacy_refund_policy')
                : null,
            'photo_consent_mode' => ForumEventPhotoConsent::AskFirst,
            'animal_welfare_rules' => $rules,
            'emergency_contact_plan' => $safety,
            'metadata' => [
                'legacy_base_attendees' => (int) $record['base_attendees'],
                'legacy_waitlist_count' => (int) $record['waitlist_count'],
                'legacy_image' => (string) $record['image'],
                'legacy_image_alt' => (string) $record['image_alt'],
                'requires_review' => $requiresReview,
            ],
        ];
        $event = $existing ?? new ForumEvent(['stable_key' => (string) $record['key']]);
        $event->forceFill($attributes)->save();
        $this->audit->record(
            event: $event,
            actor: null,
            eventType: 'legacy-catalog-synchronized',
            reasonCode: 'legacy-event-backfill',
            summaryTranslationKey: 'forum_events.history.legacy_synchronized',
            toStatus: $event->status->value,
            metadata: ['requires_review' => $requiresReview],
            idempotencyKey: 'event-backfill:catalog:'.$event->stable_key,
        );

        return $event;
    }

    /** @return array{bool, bool} */
    private function backfillGroupActivity(
        ForumGroupActivity $activity,
    ): array {
        return DB::transaction(function () use ($activity): array {
            $stableKey = 'group-event-'.$activity->stable_key;
            $event = ForumEvent::query()
                ->where('stable_key', $stableKey)
                ->first();
            $created = $event === null;
            $requiresReview = in_array(
                $activity->format->value,
                [ForumEventFormat::Online->value, ForumEventFormat::Hybrid->value],
                true,
            );
            $status = match ($activity->status) {
                ForumGroupActivityStatus::Cancelled => ForumEventStatus::Cancelled,
                ForumGroupActivityStatus::Completed => ForumEventStatus::Completed,
                default => $activity->ends_at->isPast()
                    ? ForumEventStatus::Completed
                    : ForumEventStatus::Scheduled,
            };
            $attributes = [
                'organizer_user_id' => $activity->creator->id,
                'organizer_key' => $activity->creator->actor_key,
                'organizer_name' => $activity->creator->name,
                'forum_group_id' => $activity->forum_group_id,
                'creation_idempotency_key' => 'legacy-group-activity:'.$activity->id,
                'is_system_managed' => false,
                'legacy_source_key' => 'forum-group-activity:'.$activity->id,
                'title' => $activity->title,
                'summary' => $activity->summary,
                'type' => ForumEventType::ClubMeetup,
                'visibility' => ForumEventVisibility::Group,
                'format' => ForumEventFormat::from($activity->format->value),
                'status' => $status,
                'locale' => $activity->group->default_locale,
                'starts_at' => $activity->starts_at,
                'ends_at' => $activity->ends_at,
                'timezone' => $activity->timezone,
                'capacity' => $activity->capacity,
                'registration_policy' => ForumEventRegistrationPolicy::Approval,
                'waitlist_enabled' => true,
                'location_scope' => $activity->location_scope,
                'attendance_requirements' => $activity->participation_notes,
                'cost_minor' => 0,
                'currency' => 'EUR',
                'photo_consent_mode' => ForumEventPhotoConsent::AskFirst,
                'animal_welfare_rules' => filled($activity->participation_notes)
                    && mb_strlen((string) $activity->participation_notes) >= 10
                    ? (string) $activity->participation_notes
                    : __('forum_events.defaults.group_welfare_rules'),
                'emergency_contact_plan' => __('forum_events.defaults.group_emergency_plan'),
                'cancelled_at' => $status === ForumEventStatus::Cancelled
                    ? $activity->updated_at
                    : null,
                'archived_at' => null,
                'metadata' => [
                    'requires_review' => $requiresReview,
                    'legacy_group_activity_id' => $activity->id,
                ],
            ];
            $event ??= new ForumEvent(['stable_key' => $stableKey]);
            $event->forceFill($attributes)->save();
            $taxonIds = $activity->group->taxa->pluck('id')->all();
            $event->taxa()->sync(collect($taxonIds)->mapWithKeys(
                static fn (int $id, int $index): array => [
                    $id => ['is_primary' => $index === 0],
                ],
            )->all());
            $activity->forceFill(['forum_event_id' => $event->id])->save();
            $this->audit->record(
                event: $event,
                actor: $activity->creator,
                eventType: 'group-activity-synchronized',
                reasonCode: 'group-activity-backfill',
                summaryTranslationKey: 'forum_events.history.group_activity_synchronized',
                toStatus: $event->status->value,
                metadata: [
                    'group_activity_id' => $activity->id,
                    'requires_review' => $requiresReview,
                ],
                idempotencyKey: 'event-backfill:group-activity:'.$activity->id,
            );

            return [$created, $requiresReview];
        }, 3);
    }

    private function eventType(string $value): ForumEventType
    {
        return match ($value) {
            'group-walk' => ForumEventType::Walk,
            'puppy-training', 'training-course' => ForumEventType::Training,
            'pet-show' => ForumEventType::Show,
            'adoption-day' => ForumEventType::Adoption,
            'search-action' => ForumEventType::Volunteer,
            'private-celebration' => ForumEventType::Celebration,
            'expert-webinar' => ForumEventType::OnlineSession,
            default => ForumEventType::Other,
        };
    }

    private function format(string $value): ForumEventFormat
    {
        return match ($value) {
            'online' => ForumEventFormat::Online,
            'hybrid' => ForumEventFormat::Hybrid,
            default => ForumEventFormat::Physical,
        };
    }

    private function visibility(string $value): ForumEventVisibility
    {
        return match ($value) {
            'closed' => ForumEventVisibility::Members,
            'hidden' => ForumEventVisibility::Private,
            default => ForumEventVisibility::Public,
        };
    }

    private function registrationPolicy(string $value): ForumEventRegistrationPolicy
    {
        return match ($value) {
            'approval' => ForumEventRegistrationPolicy::Approval,
            'invitation' => ForumEventRegistrationPolicy::Invitation,
            default => ForumEventRegistrationPolicy::Open,
        };
    }

    private function vaccinationRequirement(
        string $eventKey,
        string $requirements,
    ): ?string {
        return in_array($eventKey, ['puppy-social-lab', 'rose-city-pet-show'], true)
            ? $requirements
            : null;
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\InitializeForumEventLifecycle;
use App\Enums\ForumEventAccessibilityStatus;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRecurrenceFrequency;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventReviewStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventTeamMembershipStatus;
use App\Enums\ForumEventTeamRole;
use App\Enums\ForumEventType;
use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Enums\ForumEventVisibility;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventReview;
use App\Models\ForumEventSeries;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumEventUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;
use LogicException;

final class ForumEventDemoSeeder extends Seeder
{
    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Event demo data may only be created in an explicitly allowed environment.');
        }

        $attendee = User::query()->where('actor_key', 'demo-lithuanian')->first();
        $invitee = User::query()->where('actor_key', 'demo-unverified')->first();
        $organizer = User::query()->where('actor_key', 'demo-administrator')->first();
        $walk = ForumEvent::query()->where('stable_key', 'small-dog-social')->first();
        $private = ForumEvent::query()->where('stable_key', 'baxter-birthday')->first();
        $completed = ForumEvent::query()->where('stable_key', 'missing-scout-search')->first();

        if ($organizer !== null) {
            $this->seedCanonicalLifecycleScenarios($organizer, $attendee);
        }

        if ($attendee !== null && $walk !== null) {
            ForumEventRegistration::query()->updateOrCreate(
                [
                    'forum_event_id' => $walk->id,
                    'user_id' => $attendee->id,
                ],
                [
                    'stable_key' => 'demo-event-registration-lithuanian-walk',
                    'idempotency_key' => 'demo:event:registration:lithuanian-walk',
                    'status' => ForumEventRegistrationStatus::Confirmed,
                    'attendance_format' => 'physical',
                    'guest_count' => 0,
                    'photo_consent' => 'ask_first',
                    'requirements_accepted' => true,
                    'lock_version' => 0,
                ],
            );
            ForumEventUpdate::query()->updateOrCreate(
                ['idempotency_key' => 'demo:event:update:walk-arrival'],
                [
                    'forum_event_id' => $walk->id,
                    'author_user_id' => $walk->organizer_user_id,
                    'stable_key' => 'demo-event-update-walk-arrival',
                    'type' => ForumEventUpdateType::General,
                    'audience' => ForumEventUpdateAudience::Public,
                    'title' => 'Quiet arrival reminder',
                    'body' => 'Please leave extra space between animals while the host checks everyone in.',
                    'published_at' => now(),
                ],
            );
        }

        if ($invitee !== null && $private !== null) {
            ForumEventInvitation::query()->updateOrCreate(
                [
                    'forum_event_id' => $private->id,
                    'invited_user_id' => $invitee->id,
                ],
                [
                    'invited_by_user_id' => $private->organizer_user_id,
                    'stable_key' => 'demo-event-invitation-private',
                    'idempotency_key' => 'demo:event:invitation:private',
                    'status' => ForumEventInvitationStatus::Pending,
                    'expires_at' => now()->addWeeks(2),
                    'responded_at' => null,
                ],
            );
        }

        if ($attendee !== null && $completed !== null) {
            ForumEventRegistration::query()->updateOrCreate(
                [
                    'forum_event_id' => $completed->id,
                    'user_id' => $attendee->id,
                ],
                [
                    'stable_key' => 'demo-event-registration-completed',
                    'idempotency_key' => 'demo:event:registration:completed',
                    'status' => ForumEventRegistrationStatus::CheckedIn,
                    'attendance_format' => 'physical',
                    'guest_count' => 0,
                    'photo_consent' => 'declined',
                    'requirements_accepted' => true,
                    'check_in_method' => 'manual',
                    'checked_in_at' => $completed->starts_at,
                    'lock_version' => 1,
                ],
            );
            ForumEventReview::query()->updateOrCreate(
                [
                    'forum_event_id' => $completed->id,
                    'reviewer_user_id' => $attendee->id,
                ],
                [
                    'stable_key' => 'demo-event-review-completed',
                    'idempotency_key' => 'demo:event:review:completed',
                    'rating' => 5,
                    'title' => 'Clear volunteer coordination',
                    'body' => 'The public search zones and private sighting channel were explained clearly.',
                    'status' => ForumEventReviewStatus::Published,
                ],
            );
        }
    }

    private function seedCanonicalLifecycleScenarios(
        User $organizer,
        ?User $teamMember,
    ): void {
        $base = now('Europe/Vilnius')->addWeeks(2)->startOfDay()->addHours(10);
        $scenarios = [
            ['community-meetup', 'Calm community meetup', ForumEventType::SocialMeetup, ForumEventFormat::Physical, ForumEventVisibility::Public, ForumEventStatus::Scheduled, ForumEventPetParticipation::Optional],
            ['weekly-group-walk', 'Weekly riverside group walk', ForumEventType::GroupWalk, ForumEventFormat::Physical, ForumEventVisibility::Public, ForumEventStatus::RegistrationOpen, ForumEventPetParticipation::Required],
            ['beginner-training', 'Beginner cooperative training', ForumEventType::TrainingSession, ForumEventFormat::Physical, ForumEventVisibility::Members, ForumEventStatus::RegistrationOpen, ForumEventPetParticipation::ParticipatingAnimals],
            ['welfare-workshop', 'Animal welfare workshop', ForumEventType::Workshop, ForumEventFormat::Hybrid, ForumEventVisibility::Public, ForumEventStatus::Published, ForumEventPetParticipation::Optional],
            ['care-conference', 'Companion animal care conference', ForumEventType::Conference, ForumEventFormat::Hybrid, ForumEventVisibility::Public, ForumEventStatus::RegistrationScheduled, ForumEventPetParticipation::HumansOnly],
            ['online-webinar', 'Accessible online care webinar', ForumEventType::Webinar, ForumEventFormat::Online, ForumEventVisibility::Public, ForumEventStatus::RegistrationOpen, ForumEventPetParticipation::HumansOnly],
            ['community-exhibition', 'Responsible community exhibition', ForumEventType::Exhibition, ForumEventFormat::Physical, ForumEventVisibility::Public, ForumEventStatus::AwaitingSafetyReview, ForumEventPetParticipation::ParticipatingAnimals],
            ['skills-competition', 'Welfare-first skills competition', ForumEventType::Competition, ForumEventFormat::Physical, ForumEventVisibility::Unlisted, ForumEventStatus::ResultsPending, ForumEventPetParticipation::ParticipatingAnimals],
            ['verified-adoption-day', 'Verified shelter adoption day', ForumEventType::AdoptionDay, ForumEventFormat::Physical, ForumEventVisibility::Public, ForumEventStatus::Scheduled, ForumEventPetParticipation::HumansOnly],
            ['shelter-open-day', 'Shelter open day', ForumEventType::ShelterOpenDay, ForumEventFormat::Physical, ForumEventVisibility::Public, ForumEventStatus::Full, ForumEventPetParticipation::HumansOnly],
            ['community-fundraiser', 'Community veterinary support fundraiser', ForumEventType::Fundraiser, ForumEventFormat::Hybrid, ForumEventVisibility::Public, ForumEventStatus::Published, ForumEventPetParticipation::Optional],
            ['shelter-volunteer-shift', 'Shelter volunteer shift', ForumEventType::VolunteerShift, ForumEventFormat::Physical, ForumEventVisibility::Members, ForumEventStatus::RegistrationOpen, ForumEventPetParticipation::HumansOnly],
            ['organization-briefing', 'Organization safety briefing', ForumEventType::OrganizationMeeting, ForumEventFormat::Hybrid, ForumEventVisibility::Organization, ForumEventStatus::Scheduled, ForumEventPetParticipation::HumansOnly],
            ['verified-marketplace-fair', 'Verified marketplace fair', ForumEventType::MarketplaceFair, ForumEventFormat::Physical, ForumEventVisibility::Public, ForumEventStatus::Scheduled, ForumEventPetParticipation::VisitorAnimals],
            ['controlled-introduction', 'Controlled animal introduction', ForumEventType::ControlledAnimalIntroduction, ForumEventFormat::Physical, ForumEventVisibility::Invitation, ForumEventStatus::AwaitingSafetyReview, ForumEventPetParticipation::ParticipatingAnimals],
            ['custom-coordination', 'Unlisted emergency coordination meeting', ForumEventType::Custom, ForumEventFormat::Online, ForumEventVisibility::Unlisted, ForumEventStatus::Draft, ForumEventPetParticipation::HumansOnly],
        ];
        $roles = [
            ForumEventTeamRole::CoOrganizer,
            ForumEventTeamRole::RouteLeader,
            ForumEventTeamRole::Trainer,
            ForumEventTeamRole::Speaker,
            ForumEventTeamRole::ScheduleManager,
            ForumEventTeamRole::SessionModerator,
            ForumEventTeamRole::WelfareOfficer,
            ForumEventTeamRole::Judge,
            ForumEventTeamRole::SafetyLead,
            ForumEventTeamRole::VolunteerCoordinator,
            ForumEventTeamRole::PaymentReviewer,
            ForumEventTeamRole::CheckInOperator,
            ForumEventTeamRole::Auditor,
            ForumEventTeamRole::VendorCoordinator,
            ForumEventTeamRole::MedicalContact,
            ForumEventTeamRole::Administrator,
        ];

        foreach ($scenarios as $index => $scenario) {
            [$key, $title, $type, $format, $visibility, $status, $petParticipation] = $scenario;
            $startsAt = $base->copy()->addDays($index);
            $endsAt = $type === ForumEventType::Conference
                ? $startsAt->copy()->addDays(2)->addHours(6)
                : $startsAt->copy()->addHours(3);
            $event = ForumEvent::query()->firstOrCreate(
                ['stable_key' => 'demo-point13-'.$key],
                [
                    'owner_user_id' => $organizer->id,
                    'organizer_user_id' => $organizer->id,
                    'organizer_key' => $organizer->actor_key,
                    'organizer_name' => $organizer->name,
                    'creation_idempotency_key' => 'demo:event:point13:'.$key,
                    'is_system_managed' => false,
                    'title' => $title,
                    'summary' => 'A canonical event lifecycle scenario with explicit safety, privacy, accessibility, and animal-participation information.',
                    'type' => $type,
                    'visibility' => $visibility,
                    'format' => $format,
                    'pet_participation_mode' => $petParticipation,
                    'status' => $status,
                    'locale' => $index % 3 === 0 ? 'lt' : ($index % 3 === 1 ? 'en' : 'ru'),
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'timezone' => 'Europe/Vilnius',
                    'capacity' => in_array($type, [ForumEventType::Conference, ForumEventType::Webinar], true) ? 120 : 24,
                    'registration_policy' => in_array($visibility, [ForumEventVisibility::Private, ForumEventVisibility::Invitation], true)
                        ? ForumEventRegistrationPolicy::Invitation
                        : ($type->requiresSafetyReview() ? ForumEventRegistrationPolicy::Approval : ForumEventRegistrationPolicy::Open),
                    'waitlist_enabled' => true,
                    'location_scope' => $format === ForumEventFormat::Online ? null : 'Vilnius region',
                    'exact_location' => $format === ForumEventFormat::Online ? null : 'Approved participant meeting point',
                    'online_url' => $format === ForumEventFormat::Physical
                        ? null
                        : 'https://events.example.test/demo/'.$key,
                    'attendance_requirements' => 'Follow the organizer instructions and leave when continued participation would be unsafe.',
                    'vaccination_requirements' => $petParticipation->acceptsGeneralPets()
                        ? 'Current status must be reviewed when the event safety plan requires it.'
                        : null,
                    'vaccination_jurisdiction' => $petParticipation->acceptsGeneralPets() ? 'Lithuania' : null,
                    'accessibility_status' => $index % 2 === 0
                        ? ForumEventAccessibilityStatus::VenueSupplied
                        : ForumEventAccessibilityStatus::AccommodationOnRequest,
                    'accessibility_information' => 'Step-free access information and a quiet-space request channel are available.',
                    'cost_minor' => 0,
                    'currency' => 'EUR',
                    'photo_consent_mode' => ForumEventPhotoConsent::AskFirst,
                    'animal_welfare_rules' => 'Animal welfare takes priority over attendance, schedule, scoring, and revenue.',
                    'emergency_contact_plan' => 'Contact the event safety lead and the appropriate local emergency or veterinary service.',
                    'current_version_number' => 1,
                    'published_at' => in_array($status, [ForumEventStatus::Published, ForumEventStatus::RegistrationOpen], true)
                        ? now()
                        : null,
                    'lock_version' => 0,
                    'metadata' => ['demo_scenario' => $key],
                ],
            );
            app(InitializeForumEventLifecycle::class)->handle(
                $event,
                $organizer,
                'demo-point13-lifecycle',
            );

            if ($teamMember !== null) {
                ForumEventTeamMembership::query()->firstOrCreate(
                    [
                        'forum_event_id' => $event->id,
                        'user_id' => $teamMember->id,
                        'role' => $roles[$index]->value,
                    ],
                    [
                        'invited_by_user_id' => $organizer->id,
                        'status' => ForumEventTeamMembershipStatus::Active,
                        'starts_at' => now(),
                    ],
                );
            }
        }

        $walk = ForumEvent::query()->where('stable_key', 'demo-point13-weekly-group-walk')->firstOrFail();
        $series = ForumEventSeries::query()->firstOrCreate(
            ['stable_key' => 'demo-point13-weekly-walk-series'],
            [
                'owner_user_id' => $organizer->id,
                'name' => 'Weekly riverside group walk',
                'frequency' => ForumEventRecurrenceFrequency::Weekly,
                'interval' => 1,
                'weekdays' => [(int) $walk->starts_at->dayOfWeekIso],
                'timezone' => $walk->timezone,
                'starts_on' => $walk->starts_at->toDateString(),
                'ends_on' => $walk->starts_at->copy()->addWeeks(8)->toDateString(),
                'maximum_occurrences' => 8,
                'is_active' => true,
            ],
        );
        $walk->occurrences()->whereNull('forum_event_series_id')->update([
            'forum_event_series_id' => $series->id,
        ]);

        foreach ([2, 3] as $number) {
            $startsAt = $walk->starts_at->copy()->addWeeks($number - 1);
            ForumEventOccurrence::query()->firstOrCreate(
                ['stable_key' => 'demo-point13-weekly-group-walk-occurrence-'.$number],
                [
                    'forum_event_id' => $walk->id,
                    'forum_event_series_id' => $series->id,
                    'status' => ForumEventStatus::RegistrationOpen,
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addHours(3),
                    'timezone' => $walk->timezone,
                    'format' => ForumEventFormat::Physical,
                    'capacity' => $walk->capacity,
                    'location_scope' => $walk->location_scope,
                    'exact_location' => $walk->exact_location,
                    'is_override' => false,
                ],
            );
        }
    }
}

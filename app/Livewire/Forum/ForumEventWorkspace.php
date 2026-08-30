<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\CancelForumEvent;
use App\Actions\EnsureForumEventPlaceAccess;
use App\Actions\InviteToForumEvent;
use App\Actions\PublishForumEvent;
use App\Actions\PublishForumEventUpdate;
use App\Actions\RescheduleForumEvent;
use App\Actions\RespondToForumEventInvitation;
use App\Actions\RevealPlaceExactLocation;
use App\Actions\RevokeForumEventInvitation;
use App\Actions\SaveForumEventSession;
use App\Actions\SendForumEventMessage;
use App\Actions\SubmitForumEventReport;
use App\Actions\SubmitForumEventReview;
use App\Actions\UpdateForumEvent;
use App\Data\PlaceExactLocationRevealContext;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventMessageAudience;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventReviewStatus;
use App\Enums\ForumEventSessionReservationPolicy;
use App\Enums\ForumEventSessionRole;
use App\Enums\ForumEventSessionStatus;
use App\Enums\ForumEventSessionType;
use App\Enums\ForumEventType;
use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Enums\ForumEventVisibility;
use App\Enums\PetProfileStatus;
use App\Enums\PlaceAccessPurpose;
use App\Livewire\Forms\ForumEventEditForm;
use App\Livewire\Forms\ForumEventInvitationForm;
use App\Livewire\Forms\ForumEventMessageForm;
use App\Livewire\Forms\ForumEventRegistrationForm;
use App\Livewire\Forms\ForumEventReportForm;
use App\Livewire\Forms\ForumEventRescheduleForm;
use App\Livewire\Forms\ForumEventReviewForm;
use App\Livewire\Forms\ForumEventSessionForm;
use App\Livewire\Forms\ForumEventUpdateForm;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventMessage;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventRegistrationPet;
use App\Models\ForumEventReview;
use App\Models\ForumEventRoom;
use App\Models\ForumEventSession;
use App\Models\ForumEventSessionStaff;
use App\Models\ForumEventTeamMembership;
use App\Models\ForumEventTrack;
use App\Models\ForumEventUpdate;
use App\Models\ForumReportReason;
use App\Models\PetProfile;
use App\Models\Place;
use App\Models\Taxon;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Services\ForumEventOrganizerVerification;
use App\Services\ForumEventRegistrationService;
use App\Services\ForumReportReasonCatalog;
use App\Services\LocaleFormatter;
use App\Services\PetProfileAccess;
use App\Services\PetSpeciesLabel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Lang;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

final class ForumEventWorkspace extends Component
{
    use WithPagination;

    #[Locked]
    public int $eventId;

    #[Locked]
    public string $workspaceMode = 'detail';

    public ForumEventRegistrationForm $registrationForm;

    public ForumEventInvitationForm $invitationForm;

    public ForumEventEditForm $editForm;

    public ForumEventUpdateForm $updateForm;

    public ForumEventMessageForm $messageForm;

    public ForumEventReviewForm $reviewForm;

    public ForumEventRescheduleForm $rescheduleForm;

    public ForumEventReportForm $reportForm;

    public ForumEventSessionForm $sessionForm;

    #[Locked]
    public ?int $editingSessionId = null;

    public string $cancellationReasonCode = 'organizer-cancelled';

    public string $cancellationExplanation = '';

    public string $cancellationIdempotencyKey = '';

    public string $feedback = '';

    /** @var array{address: string|null, latitude: string|null, longitude: string|null, instructions: string|null}|null */
    #[Locked]
    public ?array $revealedPlaceLocation = null;

    private ?ForumEvent $resolvedEvent = null;

    private ForumEventOrganizerVerification $organizerVerification;

    private LocaleFormatter $formatter;

    private PetSpeciesLabel $petSpeciesLabel;

    private PetProfileAccess $petProfileAccess;

    public function boot(
        ForumEventOrganizerVerification $organizerVerification,
        LocaleFormatter $formatter,
        PetProfileAccess $petProfileAccess,
        PetSpeciesLabel $petSpeciesLabel,
    ): void {
        $this->organizerVerification = $organizerVerification;
        $this->formatter = $formatter;
        $this->petProfileAccess = $petProfileAccess;
        $this->petSpeciesLabel = $petSpeciesLabel;
    }

    public function mount(int $eventId, string $workspaceMode = 'detail'): void
    {
        $this->eventId = $eventId;
        $this->workspaceMode = in_array($workspaceMode, ['detail', 'edit', 'manage'], true)
            ? $workspaceMode
            : 'detail';
        Gate::authorize('view', $this->eventModel());
        $this->initializeForms();
    }

    public function hydrate(): void
    {
        $this->revealedPlaceLocation = null;
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function event(): array
    {
        $event = $this->eventModel([
            'organizer:id,name,status',
            'group:id,stable_key,name,name_translation_key',
            'place',
            'taxa:id,stable_key',
            'taxa.activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
        ]);
        Gate::authorize('view', $event);
        $user = Auth::user();
        $authorizedUser = $user instanceof User ? $user : null;
        $canViewAccess = Gate::forUser($authorizedUser)->allows('viewAccessDetails', $event);
        $canRevealPlaceExact = $canViewAccess
            && $event->place instanceof Place
            && Gate::forUser($authorizedUser)->allows('viewExactLocation', $event->place);
        $confirmedCount = (int) $event->registrations()
            ->whereIn('status', $this->seatConsumingStatuses())
            ->count();
        $confirmedGuests = (int) $event->registrations()
            ->whereIn('status', $this->seatConsumingStatuses())
            ->sum('guest_count');
        $legacyAttendees = (int) data_get($event->metadata, 'legacy_base_attendees', 0);
        $waitlistCount = (int) $event->registrations()
            ->where('status', ForumEventRegistrationStatus::Waitlisted->value)
            ->count()
            + (int) data_get($event->metadata, 'legacy_waitlist_count', 0);

        return [
            'id' => $event->id,
            'stable_key' => $event->stable_key,
            'title' => $event->title,
            'summary' => $event->summary,
            'type' => $event->type->label(),
            'format' => $event->format->label(),
            'format_key' => $event->format->value,
            'status' => $event->status->label(),
            'status_key' => $event->status->value,
            'visibility' => $event->visibility->label(),
            'pet_participation' => $event->pet_participation_mode->label(),
            'pet_participation_key' => $event->pet_participation_mode->value,
            'accepts_general_pets' => $event->pet_participation_mode->acceptsGeneralPets(),
            'requires_pet' => $event->pet_participation_mode->value === 'required',
            'accessibility_status' => $event->accessibility_status->label(),
            'current_version_number' => $event->current_version_number,
            'starts_at' => $this->formatter->dateTime($event->starts_at, $event->timezone),
            'ends_at' => $this->formatter->dateTime($event->ends_at, $event->timezone),
            'starts_at_iso' => $event->starts_at->toAtomString(),
            'ends_at_iso' => $event->ends_at->toAtomString(),
            'starts_at_accessible' => $this->formatter->accessibleDateTime(
                $event->starts_at,
                $event->timezone,
            ),
            'ends_at_accessible' => $this->formatter->accessibleDateTime(
                $event->ends_at,
                $event->timezone,
            ),
            'timezone' => $event->timezone,
            'location_scope' => $event->location_scope,
            'has_place' => $event->place_id !== null,
            'can_reveal_place_exact' => $canRevealPlaceExact,
            'exact_location' => $canViewAccess ? $event->exact_location : null,
            'online_url' => $canViewAccess ? $event->online_url : null,
            'emergency_contact_plan' => $canViewAccess
                ? $event->emergency_contact_plan
                : null,
            'can_view_access' => $canViewAccess,
            'attendance_requirements' => $event->attendance_requirements,
            'vaccination_requirements' => $event->vaccination_requirements,
            'vaccination_jurisdiction' => $event->vaccination_jurisdiction,
            'minimum_animal_age_months' => $event->minimum_animal_age_months,
            'maximum_animal_age_months' => $event->maximum_animal_age_months,
            'accessibility_information' => $event->accessibility_information,
            'photo_consent' => $event->photo_consent_mode->label(),
            'animal_welfare_rules' => $event->animal_welfare_rules,
            'registration_policy' => $event->registration_policy->label(),
            'waitlist_enabled' => $event->waitlist_enabled,
            'capacity' => $event->capacity,
            'confirmed_count' => $legacyAttendees + $confirmedCount + $confirmedGuests,
            'legacy_attendee_count' => $legacyAttendees,
            'waitlist_count' => $waitlistCount,
            'cost' => $event->cost_minor === 0
                ? __('forum_events.labels.cost_free')
                : $this->formatter->currency($event->cost_minor / 100, $event->currency),
            'refund_policy' => $event->refund_policy,
            'organizer_name' => $event->organizer_name,
            'organizer_verified' => $this->organizerVerification->allows($event->organizer),
            'group_name' => $event->group?->displayName(),
            'group_url' => $event->group === null
                ? null
                : route('groups.show', $event->group),
            'taxa' => $event->taxa->map($this->presentTaxon(...))->all(),
            'image' => data_get($event->metadata, 'legacy_image'),
            'image_alt' => data_get(
                $event->metadata,
                'legacy_image_alt',
                $event->title,
            ),
            'can_register' => Gate::forUser($authorizedUser)->allows('register', $event),
            'can_invite' => Gate::forUser($authorizedUser)->allows('invite', $event),
            'can_update' => Gate::forUser($authorizedUser)->allows('update', $event),
            'can_publish' => Gate::forUser($authorizedUser)->allows('publish', $event),
            'can_send_message' => Gate::forUser($authorizedUser)->allows('sendMessage', $event),
            'can_review' => Gate::forUser($authorizedUser)->allows('review', $event),
            'can_report' => Gate::forUser($authorizedUser)->allows('report', $event),
            'can_manage_registrations' => Gate::forUser($authorizedUser)
                ->allows('manageRegistrations', $event),
            'can_manage_schedule' => Gate::forUser($authorizedUser)
                ->allows('manageSchedule', $event),
            'can_override_schedule_conflict' => Gate::forUser($authorizedUser)
                ->allows('overrideScheduleConflict', $event),
        ];
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function occurrences(): array
    {
        Gate::authorize('view', $this->eventModel());

        return ForumEventOccurrence::query()
            ->select([
                'id',
                'forum_event_id',
                'stable_key',
                'status',
                'starts_at',
                'ends_at',
                'timezone',
                'format',
                'capacity',
                'location_scope',
                'is_override',
            ])
            ->where('forum_event_id', $this->eventId)
            ->orderBy('starts_at')
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(fn (ForumEventOccurrence $occurrence): array => [
                'id' => $occurrence->id,
                'status' => $occurrence->status->label(),
                'status_key' => $occurrence->status->value,
                'starts_at' => $this->formatter->dateTime(
                    $occurrence->starts_at,
                    $occurrence->timezone,
                ),
                'ends_at' => $this->formatter->dateTime(
                    $occurrence->ends_at,
                    $occurrence->timezone,
                ),
                'timezone' => $occurrence->timezone,
                'format' => $occurrence->format->label(),
                'capacity' => $occurrence->capacity,
                'location' => $occurrence->location_scope
                    ?? __('forum_events.defaults.online_location'),
                'is_override' => $occurrence->is_override,
            ])
            ->all();
    }

    /** @return list<array{key: string, date_iso: string, date: string, sessions: list<array<string, mixed>>}> */
    #[Computed]
    public function schedule(): array
    {
        $event = $this->eventModel();
        Gate::authorize('view', $event);
        $canManage = Gate::allows('manageSchedule', $event);

        return ForumEventSession::query()
            ->select([
                'id',
                'forum_event_id',
                'forum_event_occurrence_id',
                'forum_event_track_id',
                'forum_event_room_id',
                'title',
                'summary',
                'type',
                'status',
                'starts_at',
                'ends_at',
                'timezone',
                'capacity',
                'reservation_policy',
                'is_required',
                'position',
            ])
            ->with([
                'track:id,forum_event_id,name',
                'room:id,forum_event_id,name,public_directions,capacity,is_private',
                'staffAssignments' => function (Relation $relation) use ($canManage): void {
                    $assignments = $relation->getQuery()->select([
                        'id',
                        'forum_event_session_id',
                        'user_id',
                        'role',
                        'is_public',
                    ]);

                    if (! $canManage) {
                        $assignments->where('is_public', true);
                    }
                },
                'staffAssignments.user:id,name',
            ])
            ->where('forum_event_id', $event->id)
            ->when(
                ! $canManage,
                fn (Builder $sessions): Builder => $sessions->where(
                    'status',
                    '!=',
                    ForumEventSessionStatus::Draft->value,
                ),
            )
            ->orderBy('starts_at')
            ->orderBy('position')
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->groupBy(fn (ForumEventSession $session): string => $session->starts_at
                ->setTimezone($session->timezone)
                ->toDateString())
            ->map(function ($sessions, string $date): array {
                /** @var ForumEventSession $first */
                $first = $sessions->first();

                return [
                    'key' => str_replace('-', '', $date),
                    'date_iso' => $date,
                    'date' => (string) $this->formatter->weekdayMonthDay(
                        $first->starts_at,
                        $first->timezone,
                    ),
                    'sessions' => $sessions
                        ->map(fn (ForumEventSession $session): array => [
                            'id' => $session->id,
                            'title' => $session->title,
                            'summary' => $session->summary,
                            'type' => $session->type->label(),
                            'status' => $session->status->label(),
                            'status_key' => $session->status->value,
                            'starts_at' => $this->formatter->time(
                                $session->starts_at,
                                $session->timezone,
                            ),
                            'ends_at' => $this->formatter->time(
                                $session->ends_at,
                                $session->timezone,
                            ),
                            'starts_at_iso' => $session->starts_at->toAtomString(),
                            'ends_at_iso' => $session->ends_at->toAtomString(),
                            'timezone' => $session->timezone,
                            'track' => $session->track?->name,
                            'room' => $session->room?->name,
                            'room_directions' => $session->room?->public_directions,
                            'capacity' => $session->capacity,
                            'reservation_policy' => $session->reservation_policy->label(),
                            'is_required' => $session->is_required,
                            'staff' => $session->staffAssignments
                                ->map(static fn (ForumEventSessionStaff $staff): array => [
                                    'name' => $staff->user->name,
                                    'role' => $staff->role->label(),
                                ])
                                ->all(),
                        ])
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function scheduleOccurrenceOptions(): array
    {
        if (! Gate::allows('manageSchedule', $this->eventModel())) {
            return [];
        }

        return ForumEventOccurrence::query()
            ->select(['id', 'forum_event_id', 'starts_at', 'timezone'])
            ->where('forum_event_id', $this->eventId)
            ->orderBy('starts_at')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (ForumEventOccurrence $occurrence): array => [
                $occurrence->id => (string) $this->formatter->dateTime(
                    $occurrence->starts_at,
                    $occurrence->timezone,
                ),
            ])
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function scheduleTrackOptions(): array
    {
        if (! Gate::allows('manageSchedule', $this->eventModel())) {
            return [];
        }

        return ForumEventTrack::query()
            ->select(['id', 'forum_event_id', 'name', 'position'])
            ->where('forum_event_id', $this->eventId)
            ->orderBy('position')
            ->orderBy('id')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function scheduleRoomOptions(): array
    {
        if (! Gate::allows('manageSchedule', $this->eventModel())) {
            return [];
        }

        return ForumEventRoom::query()
            ->select(['id', 'forum_event_id', 'name', 'position'])
            ->where('forum_event_id', $this->eventId)
            ->orderBy('position')
            ->orderBy('id')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function scheduleStaffOptions(): array
    {
        if (! Gate::allows('manageSchedule', $this->eventModel())) {
            return [];
        }

        return ForumEventTeamMembership::query()
            ->select(['id', 'forum_event_id', 'user_id', 'status', 'starts_at', 'ends_at'])
            ->active()
            ->with('user:id,name')
            ->where('forum_event_id', $this->eventId)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->mapWithKeys(static fn (ForumEventTeamMembership $membership): array => [
                $membership->user_id => $membership->user->name,
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function sessionTypeOptions(): array
    {
        return collect(ForumEventSessionType::cases())
            ->mapWithKeys(static fn (ForumEventSessionType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function sessionStatusOptions(): array
    {
        return collect(ForumEventSessionStatus::cases())
            ->mapWithKeys(static fn (ForumEventSessionStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function sessionReservationPolicyOptions(): array
    {
        return collect(ForumEventSessionReservationPolicy::cases())
            ->mapWithKeys(static fn (ForumEventSessionReservationPolicy $policy): array => [
                $policy->value => $policy->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function sessionRoleOptions(): array
    {
        return collect(ForumEventSessionRole::cases())
            ->mapWithKeys(static fn (ForumEventSessionRole $role): array => [
                $role->value => $role->label(),
            ])
            ->all();
    }

    /** @return array<string, mixed>|null */
    #[Computed]
    public function currentRegistration(): ?array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        $registration = ForumEventRegistration::query()
            ->select([
                'id',
                'forum_event_id',
                'forum_event_occurrence_id',
                'forum_event_version_id',
                'user_id',
                'pet_profile_id',
                'status',
                'attendance_format',
                'guest_count',
                'photo_consent',
                'requirements_accepted',
                'waitlist_position',
                'checked_in_at',
                'checked_out_at',
                'cancelled_at',
                'lock_version',
            ])
            ->with([
                'occurrence:id,forum_event_id,starts_at,timezone',
                'version:id,forum_event_id,version_number',
                'registrationPets:id,forum_event_registration_id,pet_profile_id,eligibility_status',
                'registrationPets.petProfile:id,user_id,name,species,status,visibility,is_discoverable',
                'registrationPets.petProfile.managers' => static function (Relation $managers) use ($user): void {
                    $managers->where('user_id', $user->id);
                },
            ])
            ->where('forum_event_id', $this->eventId)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($registration === null) {
            return null;
        }

        return [
            'id' => $registration->id,
            'status' => $registration->status->label(),
            'status_key' => $registration->status->value,
            'attendance_format' => $registration->attendance_format->label(),
            'guest_count' => $registration->guest_count,
            'photo_consent' => $registration->photo_consent->label(),
            'waitlist_position' => $registration->waitlist_position,
            'occurrence' => $registration->occurrence === null
                ? null
                : $this->formatter->dateTime(
                    $registration->occurrence->starts_at,
                    $registration->occurrence->timezone,
                ),
            'event_version' => $registration->version?->version_number,
            'pets' => $registration->registrationPets
                ->map(fn (ForumEventRegistrationPet $registrationPet): array => $this->presentRegistrationPet(
                    $registrationPet,
                    $user,
                ))
                ->all(),
            'can_cancel' => Gate::forUser($user)->allows('cancelRegistration', $registration),
        ];
    }

    /** @return array<string, mixed>|null */
    #[Computed]
    public function currentInvitation(): ?array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        $invitation = ForumEventInvitation::query()
            ->select([
                'id',
                'forum_event_id',
                'invited_by_user_id',
                'invited_user_id',
                'status',
                'expires_at',
            ])
            ->with('inviter:id,name')
            ->where('forum_event_id', $this->eventId)
            ->where('invited_user_id', $user->id)
            ->where('status', ForumEventInvitationStatus::Pending->value)
            ->where('expires_at', '>', now())
            ->first();

        $inviter = $invitation?->getRelation('inviter');

        return $invitation === null ? null : [
            'id' => $invitation->id,
            'status' => $invitation->status->label(),
            'expires_at' => $this->formatter->dateTime($invitation->expires_at),
            'inviter_name' => $inviter instanceof User
                ? $inviter->name
                : __('forum_events.detail.community_organizer'),
        ];
    }

    /** @return list<array{id: int, recipient: string, status: string, expires_at: string}> */
    #[Computed]
    public function invitations(): array
    {
        $event = $this->eventModel();

        if (! Gate::allows('invite', $event)) {
            return [];
        }

        return ForumEventInvitation::query()
            ->select(['id', 'forum_event_id', 'invited_user_id', 'status', 'expires_at'])
            ->with('recipient:id,name,email')
            ->where('forum_event_id', $event->id)
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn (ForumEventInvitation $invitation): array => [
                'id' => $invitation->id,
                'recipient' => $invitation->recipient->name.' · '.$invitation->recipient->email,
                'status' => $invitation->status->label(),
                'status_key' => $invitation->status->value,
                'expires_at' => $this->formatter->dateTime($invitation->expires_at),
            ])->all();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function updates(): array
    {
        $event = $this->eventModel();
        Gate::authorize('view', $event);
        $user = Auth::user();
        $canViewAttendeeUpdates = $user instanceof User
            && $event->canDiscloseAccessTo($user);

        return ForumEventUpdate::query()
            ->select([
                'id',
                'forum_event_id',
                'author_user_id',
                'type',
                'audience',
                'title',
                'body',
                'published_at',
            ])
            ->with('author:id,name')
            ->where('forum_event_id', $event->id)
            ->when(
                ! $canViewAttendeeUpdates,
                static fn (Builder $updates) => $updates
                    ->where('audience', ForumEventUpdateAudience::Public->value),
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->map(function (ForumEventUpdate $update): array {
                $author = $update->getRelation('author');

                return [
                    'id' => $update->id,
                    'title' => Lang::has($update->title) ? __($update->title) : $update->title,
                    'body' => $update->body,
                    'type' => $update->type->label(),
                    'audience' => $update->audience->label(),
                    'author_name' => $author instanceof User
                        ? $author->name
                        : __('forum_events.detail.community_organizer'),
                    'published_at' => $this->formatter->dateTime($update->published_at),
                ];
            })
            ->all();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function messages(): array
    {
        $user = Auth::user();

        if (! $user instanceof User
            || ! Gate::forUser($user)->allows('sendMessage', $this->eventModel())
        ) {
            return [];
        }

        $event = $this->eventModel();
        $isOrganizer = $event->isOrganizer($user) || $user->isAdministrator();

        return ForumEventMessage::query()
            ->select([
                'id',
                'forum_event_id',
                'sender_user_id',
                'audience',
                'body',
                'created_at',
            ])
            ->with('sender:id,name')
            ->where('forum_event_id', $event->id)
            ->when(! $isOrganizer, function (Builder $messages) use ($user): void {
                $messages->where(function (Builder $visible) use ($user): void {
                    $visible
                        ->where('audience', ForumEventMessageAudience::Attendees->value)
                        ->orWhere('sender_user_id', $user->id);
                });
            })
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(function (ForumEventMessage $message): array {
                $sender = $message->getRelation('sender');

                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'audience' => $message->audience->label(),
                    'sender_name' => $sender instanceof User
                        ? $sender->name
                        : __('forum_events.detail.community_organizer'),
                    'sent_at' => $this->formatter->dateTime($message->created_at),
                ];
            })
            ->all();
    }

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function reviews(): array
    {
        return ForumEventReview::query()
            ->select([
                'id',
                'forum_event_id',
                'reviewer_user_id',
                'rating',
                'title',
                'body',
                'status',
                'created_at',
            ])
            ->with('reviewer:id,name')
            ->where('forum_event_id', $this->eventId)
            ->where('status', ForumEventReviewStatus::Published->value)
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(fn (ForumEventReview $review): array => [
                'id' => $review->id,
                'rating' => $review->rating,
                'title' => $review->title,
                'body' => $review->body,
                'reviewer_name' => $review->reviewer->name,
                'created_at' => $this->formatter->date($review->created_at),
            ])
            ->all();
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    #[Computed]
    public function registrations(): LengthAwarePaginator
    {
        $event = $this->eventModel();
        Gate::authorize('manageRegistrations', $event);
        $viewer = $this->requireUser();

        return ForumEventRegistration::query()
            ->select([
                'id',
                'forum_event_id',
                'forum_event_occurrence_id',
                'forum_event_version_id',
                'user_id',
                'pet_profile_id',
                'status',
                'attendance_format',
                'guest_count',
                'photo_consent',
                'waitlist_position',
                'checked_in_at',
                'checked_out_at',
                'lock_version',
            ])
            ->with([
                'user:id,name,email',
                'petProfile:id,user_id,name,species,status,visibility,is_discoverable',
                'occurrence:id,forum_event_id,starts_at,timezone',
                'version:id,forum_event_id,version_number',
                'registrationPets:id,forum_event_registration_id,pet_profile_id,eligibility_status',
                'registrationPets.petProfile:id,user_id,name,species,status,visibility,is_discoverable',
                'registrationPets.petProfile.managers' => static function (Relation $managers) use ($viewer): void {
                    $managers->where('user_id', $viewer->id);
                },
            ])
            ->where('forum_event_id', $event->id)
            ->orderBy('status')
            ->orderBy('waitlist_position')
            ->orderBy('id')
            ->paginate(25, pageName: 'attendees')
            ->through(fn (ForumEventRegistration $registration): array => [
                'id' => $registration->id,
                'user_name' => $registration->user->name,
                'user_email' => $registration->user->email,
                'pet_name' => null,
                'pets' => $registration->registrationPets
                    ->map(fn (ForumEventRegistrationPet $registrationPet): array => $this->presentRegistrationPet(
                        $registrationPet,
                        $viewer,
                    ))
                    ->all(),
                'status' => $registration->status->label(),
                'status_key' => $registration->status->value,
                'attendance_format' => $registration->attendance_format->label(),
                'guest_count' => $registration->guest_count,
                'photo_consent' => $registration->photo_consent->label(),
                'waitlist_position' => $registration->waitlist_position,
                'occurrence' => $registration->occurrence === null
                    ? null
                    : $this->formatter->dateTime(
                        $registration->occurrence->starts_at,
                        $registration->occurrence->timezone,
                    ),
                'event_version' => $registration->version?->version_number,
            ]);
    }

    /** @return array{name: string, species: string|null, eligibility: string} */
    private function presentRegistrationPet(
        ForumEventRegistrationPet $registrationPet,
        User $viewer,
    ): array {
        $pet = $registrationPet->petProfile;

        if (! $pet instanceof PetProfile) {
            return [
                'name' => __('forum_events.labels.unavailable_pet'),
                'species' => null,
                'eligibility' => $registrationPet->eligibility_status->label(),
            ];
        }

        return [
            'name' => $this->petProfileAccess->canView($pet, $viewer)
                ? $pet->name
                : __('forum_events.labels.private_pet'),
            'species' => $pet->species,
            'eligibility' => $registrationPet->eligibility_status->label(),
        ];
    }

    /** @return array<int, string> */
    #[Computed]
    public function petOptions(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        return PetProfile::query()
            ->select(['id', 'user_id', 'name', 'species', 'species_confidence'])
            ->representableBy($user)
            ->where('status', PetProfileStatus::Active)
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (PetProfile $pet): array => [
                $pet->id => $pet->name.' · '.$this->petSpeciesLabel->for(
                    $pet->species,
                    $pet->species_confidence,
                ),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function attendanceFormatOptions(): array
    {
        return match ($this->eventModel()->format) {
            ForumEventFormat::Physical => [
                ForumEventFormat::Physical->value => ForumEventFormat::Physical->label(),
            ],
            ForumEventFormat::Online => [
                ForumEventFormat::Online->value => ForumEventFormat::Online->label(),
            ],
            ForumEventFormat::Hybrid => [
                ForumEventFormat::Physical->value => ForumEventFormat::Physical->label(),
                ForumEventFormat::Online->value => ForumEventFormat::Online->label(),
            ],
        };
    }

    /** @return array<string, string> */
    #[Computed]
    public function photoConsentOptions(): array
    {
        return collect(ForumEventPhotoConsent::cases())
            ->mapWithKeys(static fn (ForumEventPhotoConsent $consent): array => [
                $consent->value => $consent->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function editableTypeOptions(): array
    {
        return collect(ForumEventType::cases())
            ->mapWithKeys(static fn (ForumEventType $type): array => [
                $type->value => $type->label(),
            ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function editableVisibilityOptions(): array
    {
        return collect(ForumEventVisibility::cases())
            ->reject(fn (ForumEventVisibility $visibility): bool => match ($visibility) {
                ForumEventVisibility::Group => $this->eventModel()->forum_group_id === null,
                ForumEventVisibility::Organization => $this->eventModel()->responsible_organization_id === null,
                default => false,
            })
            ->mapWithKeys(static fn (ForumEventVisibility $visibility): array => [
                $visibility->value => $visibility->label(),
            ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function editableRegistrationPolicyOptions(): array
    {
        return collect(ForumEventRegistrationPolicy::cases())
            ->mapWithKeys(static fn (ForumEventRegistrationPolicy $policy): array => [
                $policy->value => $policy->label(),
            ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function editablePetParticipationOptions(): array
    {
        return collect(ForumEventPetParticipation::cases())
            ->mapWithKeys(static fn (ForumEventPetParticipation $mode): array => [
                $mode->value => $mode->label(),
            ])->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function updateTypeOptions(): array
    {
        return collect(ForumEventUpdateType::cases())
            ->mapWithKeys(static fn (ForumEventUpdateType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function updateAudienceOptions(): array
    {
        return collect(ForumEventUpdateAudience::cases())
            ->mapWithKeys(static fn (ForumEventUpdateAudience $audience): array => [
                $audience->value => $audience->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function messageAudienceOptions(): array
    {
        $event = $this->eventModel();
        $user = Auth::user();

        if ($user instanceof User && ($event->isOrganizer($user) || $user->isAdministrator())) {
            return [
                ForumEventMessageAudience::Attendees->value => ForumEventMessageAudience::Attendees->label(),
            ];
        }

        return [
            ForumEventMessageAudience::Organizers->value => ForumEventMessageAudience::Organizers->label(),
        ];
    }

    /** @return array<string, string> */
    #[Computed]
    public function reportReasonOptions(): array
    {
        return ForumReportReason::query()
            ->select(['stable_key', 'translation_key'])
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('stable_key')
            ->get()
            ->mapWithKeys(static fn (ForumReportReason $reason): array => [
                $reason->stable_key => __($reason->translation_key),
            ])
            ->all();
    }

    public function register(ForumEventRegistrationService $registrations): void
    {
        $registrations->register(
            $this->requireUser(),
            $this->eventModel(),
            $this->registrationForm->data(),
        );
        $this->feedback = __('forum_events.feedback.registered');
        $this->registrationForm->reset();
        $this->initializeRegistrationForm();
        $this->refreshComputed();
    }

    public function revealPlaceExactLocation(
        EnsureForumEventPlaceAccess $ensureAccess,
        RevealPlaceExactLocation $reveal,
    ): void
    {
        $user = $this->requireUser();
        $event = $this->eventModel(['place']);
        Gate::forUser($user)->authorize('viewAccessDetails', $event);

        if (! $event->place instanceof Place) {
            abort(404);
        }

        if (! $event->place->isManagedBy($user)) {
            $ensureAccess->handle($user, $event);
        }

        $this->revealedPlaceLocation = $reveal->handle(
            $user,
            $event->place,
            new PlaceExactLocationRevealContext(
                purpose: PlaceAccessPurpose::EventAttendance,
                eventId: $event->id,
                channel: 'meetup-detail',
            ),
        );
    }

    public function cancelRegistration(ForumEventRegistrationService $registrations): void
    {
        $user = $this->requireUser();
        $registration = ForumEventRegistration::query()
            ->where('forum_event_id', $this->eventId)
            ->where('user_id', $user->id)
            ->latest('id')
            ->firstOrFail();
        $registrations->cancel($user, $registration);
        $this->feedback = __('forum_events.feedback.registration_cancelled');
        $this->refreshComputed();
    }

    public function respondToInvitation(
        int $invitationId,
        bool $accept,
        RespondToForumEventInvitation $respond,
    ): void {
        $user = $this->requireUser();
        $invitation = ForumEventInvitation::query()
            ->where('forum_event_id', $this->eventId)
            ->where('invited_user_id', $user->id)
            ->findOrFail($invitationId);
        $respond->handle($user, $invitation, $accept);
        $this->feedback = $accept
            ? __('forum_events.feedback.invitation_accepted')
            : __('forum_events.feedback.invitation_declined');
        $this->refreshComputed();
    }

    public function invite(InviteToForumEvent $invite): void
    {
        $user = $this->requireUser();
        $event = $this->eventModel();
        Gate::forUser($user)->authorize('invite', $event);
        $data = $this->invitationForm->data($event->timezone);
        $recipient = User::query()
            ->select(['id', 'name', 'email', 'status'])
            ->where('email', $data['recipient_email'])
            ->firstOrFail();
        $invite->handle(
            $user,
            $event,
            $recipient,
            $data['expires_at'],
            $data['idempotency_key'],
        );
        $this->invitationForm->reset();
        $this->initializeInvitationForm();
        $this->feedback = __('forum_events.feedback.invited');
        $this->refreshComputed();
    }

    public function revokeInvitation(
        int $invitationId,
        RevokeForumEventInvitation $revoke,
    ): void {
        $user = $this->requireUser();
        $event = $this->eventModel();
        Gate::forUser($user)->authorize('invite', $event);
        $invitation = ForumEventInvitation::query()
            ->where('forum_event_id', $this->eventId)
            ->findOrFail($invitationId);
        $revoke->handle($user, $invitation);
        $this->feedback = __('forum_events.feedback.invitation_revoked');
        $this->refreshComputed();
    }

    public function reviewRegistration(
        int $registrationId,
        bool $approve,
        ForumEventRegistrationService $registrations,
    ): void {
        $user = $this->requireUser();
        Gate::forUser($user)->authorize('manageRegistrations', $this->eventModel());
        $registration = ForumEventRegistration::query()
            ->where('forum_event_id', $this->eventId)
            ->findOrFail($registrationId);
        $registrations->review($user, $registration, $approve);
        $this->feedback = $approve
            ? __('forum_events.feedback.registration_approved')
            : __('forum_events.feedback.registration_declined');
        $this->refreshComputed();
    }

    public function removeRegistration(
        int $registrationId,
        ForumEventRegistrationService $registrations,
    ): void {
        $user = $this->requireUser();
        Gate::forUser($user)->authorize('manageRegistrations', $this->eventModel());
        $registration = ForumEventRegistration::query()
            ->where('forum_event_id', $this->eventId)
            ->findOrFail($registrationId);
        $registrations->remove($user, $registration);
        $this->feedback = __('forum_events.feedback.participant_removed');
        $this->refreshComputed();
    }

    public function checkIn(
        int $registrationId,
        ForumEventRegistrationService $registrations,
    ): void {
        $user = $this->requireUser();
        Gate::forUser($user)->authorize('manageRegistrations', $this->eventModel());
        $registration = ForumEventRegistration::query()
            ->where('forum_event_id', $this->eventId)
            ->findOrFail($registrationId);
        $registrations->checkIn($user, $registration, 'manual');
        $this->feedback = __('forum_events.feedback.checked_in');
        $this->refreshComputed();
    }

    public function checkOut(
        int $registrationId,
        ForumEventRegistrationService $registrations,
    ): void {
        $user = $this->requireUser();
        Gate::forUser($user)->authorize('manageRegistrations', $this->eventModel());
        $registration = ForumEventRegistration::query()
            ->where('forum_event_id', $this->eventId)
            ->findOrFail($registrationId);
        $registrations->checkOut($user, $registration);
        $this->feedback = __('forum_events.feedback.checked_out');
        $this->refreshComputed();
    }

    public function publishUpdate(PublishForumEventUpdate $publish): void
    {
        $data = $this->updateForm->data();
        $publish->handle(
            $this->requireUser(),
            $this->eventModel(),
            $data['type'],
            $data['audience'],
            $data['title'],
            $data['body'],
            $data['idempotency_key'],
        );
        $this->updateForm->reset();
        $this->initializeUpdateForm();
        $this->feedback = __('forum_events.feedback.update_published');
        $this->refreshComputed();
    }

    public function publish(PublishForumEvent $publish): void
    {
        $publish->handle($this->requireUser(), $this->eventModel());
        $this->feedback = __('forum_events.feedback.published');
        $this->refreshComputed();
    }

    public function saveEdit(UpdateForumEvent $update): void
    {
        $update->handle(
            $this->requireUser(),
            $this->eventModel(),
            $this->editForm->data(),
        );
        $this->feedback = __('forum_events.feedback.updated');
        $this->refreshComputed();
        $this->initializeEditForm();
    }

    public function sendMessage(SendForumEventMessage $send): void
    {
        $data = $this->messageForm->data();
        $send->handle(
            $this->requireUser(),
            $this->eventModel(),
            $data['audience'],
            $data['body'],
            $data['idempotency_key'],
        );
        $this->messageForm->reset();
        $this->initializeMessageForm();
        $this->feedback = __('forum_events.feedback.message_sent');
        $this->refreshComputed();
    }

    public function submitReview(SubmitForumEventReview $submit): void
    {
        $data = $this->reviewForm->data();
        $submit->handle(
            $this->requireUser(),
            $this->eventModel(),
            $data['rating'],
            $data['title'],
            $data['body'],
            $data['idempotency_key'],
        );
        $this->reviewForm->reset();
        $this->initializeReviewForm();
        $this->feedback = __('forum_events.feedback.review_submitted');
        $this->refreshComputed();
    }

    public function report(
        ForumReportReasonCatalog $catalog,
        SubmitForumEventReport $submit,
    ): void {
        $data = $this->reportForm->data($catalog);
        $submit->handle(
            $this->requireUser(),
            $this->eventModel(),
            $data['reason'],
            $data['description'],
            $data['truthfulness_confirmed'],
            $data['immediate_safety'],
        );
        $this->reportForm->reset();
        $this->feedback = __('forum_events.feedback.reported');
    }

    public function cancelEvent(CancelForumEvent $cancel): void
    {
        $validated = $this->validate([
            'cancellationReasonCode' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
            ],
            'cancellationExplanation' => ['required', 'string', 'min:10', 'max:5000'],
            'cancellationIdempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ]);
        $cancel->handle(
            $this->requireUser(),
            $this->eventModel(),
            (string) $validated['cancellationReasonCode'],
            trim((string) $validated['cancellationExplanation']),
            (string) $validated['cancellationIdempotencyKey'],
        );
        $this->feedback = __('forum_events.feedback.cancelled');
        $this->refreshComputed();
    }

    public function reschedule(RescheduleForumEvent $reschedule): void
    {
        $data = $this->rescheduleForm->data();
        $reschedule->handle(
            $this->requireUser(),
            $this->eventModel(),
            $data['starts_at'],
            $data['ends_at'],
            $data['timezone'],
            $data['explanation'],
            $data['idempotency_key'],
        );
        $this->rescheduleForm->reset();
        $this->initializeRescheduleForm();
        $this->feedback = __('forum_events.feedback.rescheduled');
        $this->refreshComputed();
    }

    public function saveSession(SaveForumEventSession $save): void
    {
        $event = $this->eventModel();
        Gate::authorize('manageSchedule', $event);
        $session = $this->editingSessionId === null
            ? null
            : ForumEventSession::query()
                ->where('forum_event_id', $event->id)
                ->findOrFail($this->editingSessionId);
        $wasEditing = $session !== null;

        $save->handle(
            $this->requireUser(),
            $event,
            $this->sessionForm->data(),
            $session,
        );
        $this->feedback = $wasEditing
            ? __('forum_events.feedback.session_updated')
            : __('forum_events.feedback.session_created');
        $this->resetSessionEditorState();
        $this->refreshComputed();
    }

    public function editSession(int $sessionId): void
    {
        $event = $this->eventModel();
        Gate::authorize('manageSchedule', $event);
        $session = ForumEventSession::query()
            ->with('staffAssignments:id,forum_event_session_id,user_id,role,is_public')
            ->where('forum_event_id', $event->id)
            ->findOrFail($sessionId);
        $staff = $session->staffAssignments->first();

        $this->editingSessionId = $session->id;
        $this->sessionForm->occurrenceId = $session->forum_event_occurrence_id;
        $this->sessionForm->trackId = $session->forum_event_track_id;
        $this->sessionForm->roomId = $session->forum_event_room_id;
        $this->sessionForm->title = $session->title;
        $this->sessionForm->summary = $session->summary ?? '';
        $this->sessionForm->type = $session->type->value;
        $this->sessionForm->status = $session->status->value;
        $this->sessionForm->startsAt = $session->starts_at
            ->setTimezone($session->timezone)
            ->format('Y-m-d\TH:i');
        $this->sessionForm->endsAt = $session->ends_at
            ->setTimezone($session->timezone)
            ->format('Y-m-d\TH:i');
        $this->sessionForm->timezone = $session->timezone;
        $this->sessionForm->capacity = $session->capacity;
        $this->sessionForm->reservationPolicy = $session->reservation_policy->value;
        $this->sessionForm->isRequired = $session->is_required;
        $this->sessionForm->position = $session->position;
        if ($staff === null) {
            $this->sessionForm->staffUserId = null;
            $this->sessionForm->staffRole = ForumEventSessionRole::Speaker->value;
            $this->sessionForm->staffIsPublic = true;
        } else {
            $this->sessionForm->staffUserId = $staff->user_id;
            $this->sessionForm->staffRole = $staff->role->value;
            $this->sessionForm->staffIsPublic = $staff->is_public;
        }
        $this->sessionForm->conflictOverrideReason = '';
        $this->sessionForm->idempotencyKey = (string) str()->uuid();
    }

    public function resetSessionEditor(): void
    {
        Gate::authorize('manageSchedule', $this->eventModel());
        $this->resetSessionEditorState();
    }

    public function render()
    {
        return view('livewire.forum.forum-event-workspace');
    }

    /**
     * @param  list<string>  $relations
     */
    private function eventModel(array $relations = []): ForumEvent
    {
        $this->resolvedEvent ??= ForumEvent::query()
            ->select([
                'id',
                'owner_user_id',
                'organizer_user_id',
                'responsible_organization_id',
                'place_id',
                'venue_id',
                'organizer_key',
                'organizer_name',
                'forum_group_id',
                'stable_key',
                'is_system_managed',
                'title',
                'summary',
                'type',
                'visibility',
                'format',
                'status',
                'locale',
                'starts_at',
                'ends_at',
                'timezone',
                'capacity',
                'registration_policy',
                'waitlist_enabled',
                'pet_participation_mode',
                'accessibility_status',
                'current_version_number',
                'registration_opens_at',
                'registration_closes_at',
                'published_at',
                'safety_suspended_at',
                'location_scope',
                'exact_location',
                'online_url',
                'attendance_requirements',
                'vaccination_requirements',
                'vaccination_jurisdiction',
                'minimum_animal_age_months',
                'maximum_animal_age_months',
                'accessibility_information',
                'cost_minor',
                'currency',
                'refund_policy',
                'photo_consent_mode',
                'animal_welfare_rules',
                'emergency_contact_plan',
                'lock_version',
                'cancelled_at',
                'archived_at',
                'metadata',
            ])
            ->findOrFail($this->eventId);

        if ($relations !== []) {
            $this->resolvedEvent->loadMissing($relations);
        }

        return $this->resolvedEvent;
    }

    /** @return array{id: int, name: string, scientific_name: string|null, rank: string} */
    private function presentTaxon(Taxon $taxon): array
    {
        $version = $taxon->activeVersion;

        return [
            'id' => $taxon->id,
            'name' => $version instanceof TaxonVersion
                ? $version->scientific_name
                : __('taxonomy.unidentified'),
            'scientific_name' => $version instanceof TaxonVersion
                ? $version->scientific_name
                : null,
            'rank' => $version instanceof TaxonVersion
                ? $version->rank
                : __('taxonomy.unknown_rank'),
        ];
    }

    private function initializeForms(): void
    {
        $this->initializeEditForm();
        $this->initializeRegistrationForm();
        $this->initializeInvitationForm();
        $this->initializeUpdateForm();
        $this->initializeMessageForm();
        $this->initializeReviewForm();
        $this->initializeRescheduleForm();
        $this->initializeSessionForm();
        $this->cancellationIdempotencyKey = (string) str()->uuid();
    }

    private function initializeEditForm(): void
    {
        $event = $this->eventModel();

        if (Gate::allows('update', $event)) {
            $this->editForm->fillFromEvent($event);
        }
    }

    private function initializeRegistrationForm(): void
    {
        $event = $this->eventModel();
        $this->registrationForm->attendanceFormat = $event->format === ForumEventFormat::Online
            ? ForumEventFormat::Online->value
            : ForumEventFormat::Physical->value;
        $this->registrationForm->occurrenceId = ForumEventOccurrence::query()
            ->where('forum_event_id', $event->id)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->value('id');
        $this->registrationForm->idempotencyKey = (string) str()->uuid();
    }

    private function initializeInvitationForm(): void
    {
        $event = $this->eventModel();
        $this->invitationForm->expiresAt = $event->starts_at
            ->subMinute()
            ->setTimezone($event->timezone)
            ->format('Y-m-d\TH:i');
        $this->invitationForm->idempotencyKey = (string) str()->uuid();
    }

    private function initializeUpdateForm(): void
    {
        $this->updateForm->idempotencyKey = (string) str()->uuid();
    }

    private function initializeMessageForm(): void
    {
        $event = $this->eventModel();
        $user = Auth::user();
        $this->messageForm->audience = $user instanceof User
            && ($event->isOrganizer($user) || $user->isAdministrator())
            ? ForumEventMessageAudience::Attendees->value
            : ForumEventMessageAudience::Organizers->value;
        $this->messageForm->idempotencyKey = (string) str()->uuid();
    }

    private function initializeReviewForm(): void
    {
        $this->reviewForm->idempotencyKey = (string) str()->uuid();
    }

    private function initializeRescheduleForm(): void
    {
        $event = $this->eventModel();
        $this->rescheduleForm->startsAt = $event->starts_at
            ->setTimezone($event->timezone)
            ->format('Y-m-d\TH:i');
        $this->rescheduleForm->endsAt = $event->ends_at
            ->setTimezone($event->timezone)
            ->format('Y-m-d\TH:i');
        $this->rescheduleForm->timezone = $event->timezone;
        $this->rescheduleForm->idempotencyKey = (string) str()->uuid();
    }

    private function initializeSessionForm(): void
    {
        $event = $this->eventModel();
        $occurrence = ForumEventOccurrence::query()
            ->select([
                'id',
                'forum_event_id',
                'starts_at',
                'ends_at',
                'timezone',
                'capacity',
            ])
            ->where('forum_event_id', $event->id)
            ->orderBy('starts_at')
            ->first();

        if ($occurrence === null) {
            $this->sessionForm->occurrenceId = null;
            $this->sessionForm->timezone = $event->timezone;
            $this->sessionForm->capacity = null;
        } else {
            $this->sessionForm->occurrenceId = $occurrence->id;
            $this->sessionForm->timezone = $occurrence->timezone;
            $this->sessionForm->capacity = $occurrence->capacity;
            $endsAt = $occurrence->starts_at->addHour();

            if ($endsAt->gt($occurrence->ends_at)) {
                $endsAt = $occurrence->ends_at;
            }

            $this->sessionForm->startsAt = $occurrence->starts_at
                ->setTimezone($occurrence->timezone)
                ->format('Y-m-d\TH:i');
            $this->sessionForm->endsAt = $endsAt
                ->setTimezone($occurrence->timezone)
                ->format('Y-m-d\TH:i');
        }

        $this->sessionForm->idempotencyKey = (string) str()->uuid();
    }

    private function resetSessionEditorState(): void
    {
        $this->editingSessionId = null;
        $this->sessionForm->reset();
        $this->initializeSessionForm();
    }

    private function refreshComputed(): void
    {
        $this->resolvedEvent = null;
        unset(
            $this->event,
            $this->occurrences,
            $this->schedule,
            $this->currentRegistration,
            $this->currentInvitation,
            $this->invitations,
            $this->updates,
            $this->messages,
            $this->reviews,
            $this->registrations,
        );
    }

    private function requireUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    /** @return list<string> */
    private function seatConsumingStatuses(): array
    {
        return collect(ForumEventRegistrationStatus::cases())
            ->filter(static fn (ForumEventRegistrationStatus $status): bool => $status->consumesSeat())
            ->map(static fn (ForumEventRegistrationStatus $status): string => $status->value)
            ->all();
    }
}

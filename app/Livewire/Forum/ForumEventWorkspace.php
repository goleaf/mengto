<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\CancelForumEvent;
use App\Actions\InviteToForumEvent;
use App\Actions\PublishForumEventUpdate;
use App\Actions\RescheduleForumEvent;
use App\Actions\RespondToForumEventInvitation;
use App\Actions\SendForumEventMessage;
use App\Actions\SubmitForumEventReport;
use App\Actions\SubmitForumEventReview;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventInvitationStatus;
use App\Enums\ForumEventMessageAudience;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventReviewStatus;
use App\Enums\ForumEventUpdateAudience;
use App\Enums\ForumEventUpdateType;
use App\Livewire\Forms\ForumEventInvitationForm;
use App\Livewire\Forms\ForumEventMessageForm;
use App\Livewire\Forms\ForumEventRegistrationForm;
use App\Livewire\Forms\ForumEventReportForm;
use App\Livewire\Forms\ForumEventRescheduleForm;
use App\Livewire\Forms\ForumEventReviewForm;
use App\Livewire\Forms\ForumEventUpdateForm;
use App\Models\ForumEvent;
use App\Models\ForumEventInvitation;
use App\Models\ForumEventMessage;
use App\Models\ForumEventRegistration;
use App\Models\ForumEventReview;
use App\Models\ForumEventUpdate;
use App\Models\ForumReportReason;
use App\Models\PetProfile;
use App\Models\Taxon;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Services\ForumEventOrganizerVerification;
use App\Services\ForumEventRegistrationService;
use App\Services\ForumReportReasonCatalog;
use App\Services\LocaleFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ForumEventWorkspace extends Component
{
    #[Locked]
    public int $eventId;

    public ForumEventRegistrationForm $registrationForm;

    public ForumEventInvitationForm $invitationForm;

    public ForumEventUpdateForm $updateForm;

    public ForumEventMessageForm $messageForm;

    public ForumEventReviewForm $reviewForm;

    public ForumEventRescheduleForm $rescheduleForm;

    public ForumEventReportForm $reportForm;

    public string $cancellationReasonCode = 'organizer-cancelled';

    public string $cancellationExplanation = '';

    public string $cancellationIdempotencyKey = '';

    public string $feedback = '';

    private ?ForumEvent $resolvedEvent = null;

    private ForumEventOrganizerVerification $organizerVerification;

    private LocaleFormatter $formatter;

    public function boot(
        ForumEventOrganizerVerification $organizerVerification,
        LocaleFormatter $formatter,
    ): void {
        $this->organizerVerification = $organizerVerification;
        $this->formatter = $formatter;
    }

    public function mount(int $eventId): void
    {
        $this->eventId = $eventId;
        Gate::authorize('view', $this->eventModel());
        $this->initializeForms();
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function event(): array
    {
        $event = $this->eventModel([
            'organizer:id,name,status',
            'group:id,stable_key,name,name_translation_key',
            'taxa:id,stable_key',
            'taxa.activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
        ]);
        Gate::authorize('view', $event);
        $user = Auth::user();
        $authorizedUser = $user instanceof User ? $user : null;
        $canViewAccess = Gate::forUser($authorizedUser)->allows('viewAccessDetails', $event);
        $confirmedCount = (int) $event->registrations()
            ->whereIn('status', [
                ForumEventRegistrationStatus::Confirmed->value,
                ForumEventRegistrationStatus::CheckedIn->value,
            ])
            ->count();
        $confirmedGuests = (int) $event->registrations()
            ->whereIn('status', [
                ForumEventRegistrationStatus::Confirmed->value,
                ForumEventRegistrationStatus::CheckedIn->value,
            ])
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
            'can_send_message' => Gate::forUser($authorizedUser)->allows('sendMessage', $event),
            'can_review' => Gate::forUser($authorizedUser)->allows('review', $event),
            'can_report' => Gate::forUser($authorizedUser)->allows('report', $event),
            'can_manage_registrations' => Gate::forUser($authorizedUser)
                ->allows('manageRegistrations', $event),
        ];
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
                'user_id',
                'pet_profile_id',
                'status',
                'attendance_format',
                'guest_count',
                'photo_consent',
                'requirements_accepted',
                'waitlist_position',
                'checked_in_at',
                'cancelled_at',
                'lock_version',
            ])
            ->where('forum_event_id', $this->eventId)
            ->where('user_id', $user->id)
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
                    'title' => $update->title,
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

    /** @return list<array<string, mixed>> */
    #[Computed]
    public function registrations(): array
    {
        $event = $this->eventModel();

        if (! Gate::allows('manageRegistrations', $event)) {
            return [];
        }

        return ForumEventRegistration::query()
            ->select([
                'id',
                'forum_event_id',
                'user_id',
                'pet_profile_id',
                'status',
                'attendance_format',
                'guest_count',
                'photo_consent',
                'waitlist_position',
                'checked_in_at',
                'lock_version',
            ])
            ->with([
                'user:id,name,email',
                'petProfile:id,user_id,name,species',
            ])
            ->where('forum_event_id', $event->id)
            ->orderBy('status')
            ->orderBy('waitlist_position')
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(static fn (ForumEventRegistration $registration): array => [
                'id' => $registration->id,
                'user_name' => $registration->user->name,
                'user_email' => $registration->user->email,
                'pet_name' => $registration->petProfile?->name,
                'status' => $registration->status->label(),
                'status_key' => $registration->status->value,
                'attendance_format' => $registration->attendance_format->label(),
                'guest_count' => $registration->guest_count,
                'photo_consent' => $registration->photo_consent->label(),
                'waitlist_position' => $registration->waitlist_position,
            ])
            ->all();
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
            ->select(['id', 'user_id', 'name', 'species'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->mapWithKeys(static fn (PetProfile $pet): array => [
                $pet->id => $pet->name.' · '.$pet->species,
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

    public function cancelRegistration(ForumEventRegistrationService $registrations): void
    {
        $user = $this->requireUser();
        $registration = ForumEventRegistration::query()
            ->where('forum_event_id', $this->eventId)
            ->where('user_id', $user->id)
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
        $data = $this->invitationForm->data($this->eventModel()->timezone);
        $recipient = User::query()
            ->select(['id', 'name', 'email', 'status'])
            ->where('email', $data['recipient_email'])
            ->firstOrFail();
        $invite->handle(
            $this->requireUser(),
            $this->eventModel(),
            $recipient,
            $data['expires_at'],
            $data['idempotency_key'],
        );
        $this->invitationForm->reset();
        $this->initializeInvitationForm();
        $this->feedback = __('forum_events.feedback.invited');
        $this->refreshComputed();
    }

    public function reviewRegistration(
        int $registrationId,
        bool $approve,
        ForumEventRegistrationService $registrations,
    ): void {
        $registration = ForumEventRegistration::query()
            ->where('forum_event_id', $this->eventId)
            ->findOrFail($registrationId);
        $registrations->review($this->requireUser(), $registration, $approve);
        $this->feedback = $approve
            ? __('forum_events.feedback.registration_approved')
            : __('forum_events.feedback.registration_declined');
        $this->refreshComputed();
    }

    public function checkIn(
        int $registrationId,
        ForumEventRegistrationService $registrations,
    ): void {
        $registration = ForumEventRegistration::query()
            ->where('forum_event_id', $this->eventId)
            ->findOrFail($registrationId);
        $registrations->checkIn($this->requireUser(), $registration, 'manual');
        $this->feedback = __('forum_events.feedback.checked_in');
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
                'organizer_user_id',
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
        $this->initializeRegistrationForm();
        $this->initializeInvitationForm();
        $this->initializeUpdateForm();
        $this->initializeMessageForm();
        $this->initializeReviewForm();
        $this->initializeRescheduleForm();
        $this->cancellationIdempotencyKey = (string) str()->uuid();
    }

    private function initializeRegistrationForm(): void
    {
        $event = $this->eventModel();
        $this->registrationForm->attendanceFormat = $event->format === ForumEventFormat::Online
            ? ForumEventFormat::Online->value
            : ForumEventFormat::Physical->value;
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

    private function refreshComputed(): void
    {
        $this->resolvedEvent = null;
        unset(
            $this->event,
            $this->currentRegistration,
            $this->currentInvitation,
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
}

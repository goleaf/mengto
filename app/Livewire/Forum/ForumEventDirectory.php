<?php

declare(strict_types=1);

namespace App\Livewire\Forum;

use App\Actions\CreateForumEvent;
use App\Enums\ForumEventAccessibilityStatus;
use App\Enums\ForumEventFormat;
use App\Enums\ForumEventPetParticipation;
use App\Enums\ForumEventPhotoConsent;
use App\Enums\ForumEventRegistrationPolicy;
use App\Enums\ForumEventRegistrationStatus;
use App\Enums\ForumEventReviewStatus;
use App\Enums\ForumEventStatus;
use App\Enums\ForumEventType;
use App\Enums\ForumEventVisibility;
use App\Enums\OrganizationRestrictionCapability;
use App\Enums\VenueStatus;
use App\Livewire\Forms\ForumEventForm;
use App\Models\ForumEvent;
use App\Models\Organization;
use App\Models\Place;
use App\Models\Taxon;
use App\Models\TaxonVersion;
use App\Models\User;
use App\Models\Venue;
use App\Services\ForumEventOrganizerVerification;
use App\Services\LocaleFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class ForumEventDirectory extends Component
{
    use WithPagination;

    protected ForumEventOrganizerVerification $organizerVerification;

    protected LocaleFormatter $formatter;

    public ForumEventForm $form;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $type = 'all';

    #[Url(except: 'all')]
    public string $format = 'all';

    #[Url(except: 'upcoming')]
    public string $period = 'upcoming';

    public string $feedback = '';

    public function boot(
        ForumEventOrganizerVerification $organizerVerification,
        LocaleFormatter $formatter,
    ): void {
        $this->organizerVerification = $organizerVerification;
        $this->formatter = $formatter;
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', ForumEvent::class);
        $this->initializeForm();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedFormat(): void
    {
        $this->resetPage();
    }

    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function updatedFormPlaceId(): void
    {
        $this->form->venueId = null;
        $this->form->locationScope = '';
    }

    /** @return LengthAwarePaginator<int, array<string, mixed>> */
    #[Computed]
    public function events(): LengthAwarePaginator
    {
        $filters = $this->validatedFilters();
        $user = Auth::user();
        $query = ForumEvent::query()
            ->visibleTo($user instanceof User ? $user : null)
            ->select([
                'id',
                'owner_user_id',
                'organizer_user_id',
                'organizer_name',
                'stable_key',
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
                'pet_participation_mode',
                'accessibility_status',
                'accessibility_information',
                'current_version_number',
                'cost_minor',
                'currency',
                'forum_group_id',
                'archived_at',
                'metadata',
            ])
            ->with([
                'taxa:id,stable_key',
                'taxa.activeVersion:id,taxon_id,rank,scientific_name,is_active_version',
            ])
            ->withCount([
                'registrations as confirmed_registrations_count' => static fn (Builder $registrations) => $registrations
                    ->whereIn('status', [
                        ForumEventRegistrationStatus::Confirmed->value,
                        ForumEventRegistrationStatus::CheckedIn->value,
                    ]),
                'registrations as waitlisted_registrations_count' => static fn (Builder $registrations) => $registrations
                    ->where('status', ForumEventRegistrationStatus::Waitlisted->value),
            ])
            ->withAvg([
                'reviews as published_review_average' => static fn (Builder $reviews) => $reviews
                    ->where('status', ForumEventReviewStatus::Published->value),
            ], 'rating');

        if ($filters['search'] !== '') {
            $query->where(function (Builder $search) use ($filters): void {
                $like = '%'.$filters['search'].'%';
                $search
                    ->where('title', 'like', $like)
                    ->orWhere('summary', 'like', $like)
                    ->orWhere('organizer_name', 'like', $like)
                    ->orWhere('location_scope', 'like', $like)
                    ->orWhereHas(
                        'taxa.activeVersion',
                        static fn (Builder $versions) => $versions
                            ->where('scientific_name', 'like', $like),
                    )
                    ->orWhereHas(
                        'taxa.names',
                        static fn (Builder $names) => $names
                            ->where('is_active', true)
                            ->where('name', 'like', $like),
                    );
            });
        }

        if ($filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        if ($filters['format'] !== 'all') {
            $query->where('format', $filters['format']);
        }

        match ($filters['period']) {
            'past' => $query->where('ends_at', '<', now())->orderByDesc('starts_at'),
            'all' => $query->orderBy('starts_at'),
            default => $query
                ->where('ends_at', '>=', now())
                ->whereIn('status', collect(ForumEventStatus::cases())
                    ->filter(static fn (ForumEventStatus $status): bool => $status->isDiscoverable())
                    ->map(static fn (ForumEventStatus $status): string => $status->value)
                    ->all())
                ->orderBy('starts_at'),
        };

        $paginator = $query
            ->orderBy('id')
            ->paginate(12);
        $verifiedIds = $this->organizerVerification->verifiedUserIds(
            $paginator->getCollection()
                ->pluck('organizer_user_id')
                ->filter()
                ->map(static fn (mixed $id): int => (int) $id),
        );

        return $paginator->through(
            fn (ForumEvent $event): array => $this->presentEvent($event, $verifiedIds),
        );
    }

    /** @return array<string, string> */
    #[Computed]
    public function typeOptions(): array
    {
        return collect(ForumEventType::cases())
            ->mapWithKeys(static fn (ForumEventType $type): array => [
                $type->value => $type->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function formatOptions(): array
    {
        return collect(ForumEventFormat::cases())
            ->mapWithKeys(static fn (ForumEventFormat $format): array => [
                $format->value => $format->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function visibilityOptions(): array
    {
        $visibilities = [
            ForumEventVisibility::Public,
            ForumEventVisibility::Unlisted,
            ForumEventVisibility::Members,
            ForumEventVisibility::Invitation,
            ForumEventVisibility::Private,
        ];

        if ($this->organizationOptions() !== []) {
            $visibilities[] = ForumEventVisibility::Organization;
        }

        return collect($visibilities)->mapWithKeys(static fn (ForumEventVisibility $visibility): array => [
            $visibility->value => $visibility->label(),
        ])->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function organizationOptions(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        return Organization::query()
            ->select(['id', 'name'])
            ->eventOrganizableBy($user)
            ->allowingCapability(OrganizationRestrictionCapability::CreateEvents)
            ->orderBy('name')
            ->orderBy('id')
            ->limit(100)
            ->pluck('name', 'id')
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function placeOptions(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        return Place::query()
            ->usableForEventsBy($user)
            ->select(['id', 'name', 'public_region'])
            ->orderBy('name')
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->mapWithKeys(static fn (Place $place): array => [
                $place->id => __('places.presentation.place_option', [
                    'name' => $place->name,
                    'region' => $place->public_region,
                ]),
            ])
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function venueOptions(): array
    {
        $user = Auth::user();

        if (! $user instanceof User || $this->form->placeId === null) {
            return [];
        }

        $placeExists = Place::query()
            ->usableForEventsBy($user)
            ->whereKey($this->form->placeId)
            ->exists();

        if (! $placeExists) {
            return [];
        }

        return Venue::query()
            ->select(['id', 'place_id', 'timezone', 'human_capacity', 'animal_capacity'])
            ->where('place_id', $this->form->placeId)
            ->where('status', VenueStatus::Active->value)
            ->orderBy('id')
            ->limit(10)
            ->get()
            ->mapWithKeys(static fn (Venue $venue): array => [
                $venue->id => __('places.presentation.venue_option', [
                    'timezone' => $venue->timezone,
                    'people' => $venue->human_capacity ?? __('places.presentation.capacity_unknown'),
                    'animals' => $venue->animal_capacity ?? __('places.presentation.capacity_unknown'),
                ]),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function registrationPolicyOptions(): array
    {
        return collect(ForumEventRegistrationPolicy::cases())
            ->mapWithKeys(static fn (ForumEventRegistrationPolicy $policy): array => [
                $policy->value => $policy->label(),
            ])
            ->all();
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
    public function petParticipationOptions(): array
    {
        return collect(ForumEventPetParticipation::cases())
            ->mapWithKeys(static fn (ForumEventPetParticipation $mode): array => [
                $mode->value => $mode->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function accessibilityStatusOptions(): array
    {
        return collect(ForumEventAccessibilityStatus::cases())
            ->mapWithKeys(static fn (ForumEventAccessibilityStatus $status): array => [
                $status->value => $status->label(),
            ])
            ->all();
    }

    /** @return array<string, string> */
    #[Computed]
    public function localeOptions(): array
    {
        return collect(config('platform.supported_locales', ['en']))
            ->mapWithKeys(static fn (string $locale): array => [
                $locale => __('forum_journals.locales.'.$locale),
            ])
            ->all();
    }

    #[Computed]
    public function canCreate(): bool
    {
        return Gate::allows('create', ForumEvent::class);
    }

    public function create(CreateForumEvent $createEvent): void
    {
        $event = $createEvent->handle($this->requireUser(), $this->form->data());
        $this->feedback = __('forum_events.feedback.created');
        $this->form->reset();
        $this->initializeForm();
        $this->redirectRoute('meetups.show', $event, navigate: true);
    }

    public function render()
    {
        return view('livewire.forum.forum-event-directory');
    }

    /** @return array{search: string, type: string, format: string, period: string} */
    private function validatedFilters(): array
    {
        $validator = validator([
            'search' => trim($this->search),
            'type' => $this->type,
            'format' => $this->format,
            'period' => $this->period,
        ], [
            'search' => ['nullable', 'string', 'max:120'],
            'type' => [
                'required',
                Rule::in([
                    'all',
                    ...array_map(
                        static fn (ForumEventType $type): string => $type->value,
                        ForumEventType::cases(),
                    ),
                ]),
            ],
            'format' => [
                'required',
                Rule::in([
                    'all',
                    ...array_map(
                        static fn (ForumEventFormat $format): string => $format->value,
                        ForumEventFormat::cases(),
                    ),
                ]),
            ],
            'period' => ['required', Rule::in(['upcoming', 'past', 'all'])],
        ]);

        if ($validator->fails()) {
            $this->search = '';
            $this->type = 'all';
            $this->format = 'all';
            $this->period = 'upcoming';

            return [
                'search' => '',
                'type' => 'all',
                'format' => 'all',
                'period' => 'upcoming',
            ];
        }

        /** @var array{search: string|null, type: string, format: string, period: string} $validated */
        $validated = $validator->validated();

        return [
            'search' => (string) ($validated['search'] ?? ''),
            'type' => $validated['type'],
            'format' => $validated['format'],
            'period' => $validated['period'],
        ];
    }

    private function initializeForm(): void
    {
        $user = Auth::user();
        $timezone = $user instanceof User ? $user->timezone : 'UTC';
        $locale = $user instanceof User ? $user->locale : app()->getLocale();
        $startsAt = now($timezone)->addDays(7)->startOfHour();

        $this->form->startsAt = $startsAt->format('Y-m-d\TH:i');
        $this->form->endsAt = $startsAt->addHours(2)->format('Y-m-d\TH:i');
        $this->form->timezone = $timezone;
        $this->form->locale = $locale;
        $this->form->animalWelfareRules = __('forum_events.defaults.group_welfare_rules');
        $this->form->emergencyContactPlan = __('forum_events.defaults.group_emergency_plan');
        $this->form->idempotencyKey = (string) str()->uuid();
    }

    private function requireUser(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    /**
     * @param  array<int, bool>  $verifiedIds
     * @return array<string, mixed>
     */
    private function presentEvent(ForumEvent $event, array $verifiedIds): array
    {
        $confirmed = (int) $event->getAttribute('confirmed_registrations_count')
            + (int) data_get($event->metadata, 'legacy_base_attendees', 0);

        return [
            'id' => $event->id,
            'stable_key' => $event->stable_key,
            'title' => $event->title,
            'summary' => str($event->summary)->squish()->limit(220)->toString(),
            'type' => $event->type->label(),
            'format' => $event->format->label(),
            'status' => $event->status->label(),
            'visibility' => $event->visibility->label(),
            'starts_at' => $this->formatter->dateTime($event->starts_at, $event->timezone),
            'ends_at' => $this->formatter->dateTime($event->ends_at, $event->timezone),
            'location' => $event->location_scope ?? __('forum_events.defaults.online_location'),
            'pet_participation' => $event->pet_participation_mode->label(),
            'accessibility_status' => $event->accessibility_status->label(),
            'accessibility' => $event->accessibility_information,
            'version' => $event->current_version_number,
            'organizer_name' => $event->organizer_name,
            'organizer_verified' => $event->organizer_user_id !== null
                && isset($verifiedIds[$event->organizer_user_id]),
            'capacity' => $event->capacity,
            'confirmed_count' => $confirmed,
            'waitlist_count' => (int) $event->getAttribute('waitlisted_registrations_count')
                + (int) data_get($event->metadata, 'legacy_waitlist_count', 0),
            'review_average' => $event->getAttribute('published_review_average') === null
                ? null
                : round((float) $event->getAttribute('published_review_average'), 1),
            'cost' => $event->cost_minor === 0
                ? __('forum_events.labels.cost_free')
                : $this->formatter->currency($event->cost_minor / 100, $event->currency),
            'taxa' => $event->taxa
                ->map(static function (Taxon $taxon): string {
                    $version = $taxon->activeVersion;

                    return $version instanceof TaxonVersion
                        ? $version->scientific_name
                        : __('taxonomy.unidentified');
                })
                ->all(),
            'url' => route('meetups.show', $event),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SearchCaseType;
use App\Enums\SearchStatus;
use App\Enums\SearchTaskStatus;
use App\Enums\SearchVolunteerStatus;
use App\Enums\SightingStatus;
use App\Models\PetProfile;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchCaseEvent;
use App\Models\SearchContactRelay;
use App\Models\SearchSector;
use App\Models\SearchTask;
use App\Models\SearchUpdate;
use App\Models\SearchVolunteer;
use App\Models\Sighting;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class SearchPresenter
{
    private const COORDINATION_COLLECTION_LIMIT = 100;

    private const COORDINATION_HISTORY_LIMIT = 50;

    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumActor $actor,
        private readonly SearchTaxonomy $taxonomy,
        private readonly PlaceCatalog $places,
        private readonly QrCodeGenerator $qrCodes,
        private readonly LocaleFormatter $formatter,
        private readonly PetProfileAgeLabel $petAgeLabels,
        private readonly Gate $gate,
        private readonly PortalMediaUrl $mediaUrl,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function directory(array $filters): array
    {
        $query = SearchCase::query()
            ->forDirectory()
            ->publiclyVisible()
            ->search($filters['q'] ?? null)
            ->ofType($filters['type'] ?? null)
            ->withStatus($filters['status'] ?? null)
            ->forSpecies($filters['species'] ?? null)
            ->inCity($filters['city'] ?? null)
            ->withCount([
                'sightings as confirmed_sightings_count' => fn (Builder $sightings): Builder => $sightings
                    ->where('status', SightingStatus::Confirmed->value),
                'volunteers as active_volunteers_count' => fn (Builder $volunteers): Builder => $volunteers
                    ->where('status', SearchVolunteerStatus::Active->value),
                'tasks as open_tasks_count' => fn (Builder $tasks): Builder => $tasks
                    ->whereIn('status', [
                        SearchTaskStatus::Open->value,
                        SearchTaskStatus::Claimed->value,
                        SearchTaskStatus::InProgress->value,
                        SearchTaskStatus::NeedsHelp->value,
                    ]),
            ]);

        match ($filters['sort'] ?? 'latest-sighting') {
            'newest' => $query->latest('reported_at')->latest('id'),
            'urgent' => $query->orderByDesc('alerts_active')->latest('last_seen_at')->latest('id'),
            'nearest' => $query->orderBy('city')->orderBy('last_seen_area')->latest('id'),
            default => $query->latest('last_sighting_at')->latest('last_seen_at')->latest('id'),
        };

        $searchCases = $query->simplePaginate(9)->withQueryString();
        $searchCases->through(fn (SearchCase $searchCase): array => $this->card($searchCase));

        return [
            ...$this->page(__('messages.lost_found'), 'lost-found'),
            'search_cases' => $searchCases,
            'filters' => $filters,
            'types' => $this->taxonomy->types(),
            'statuses' => $this->taxonomy->directoryStatuses(),
            'species_options' => $this->taxonomy->species(),
            'sort_options' => $this->taxonomy->sortOptions(),
            'stats' => SearchCase::directoryStats(),
            'map_markers' => collect($searchCases->items())
                ->map(fn (array $item): array => Arr::only($item, [
                    'slug', 'pet_name', 'type', 'status_label', 'map_x', 'map_y',
                    'last_seen_area', 'last_seen_label',
                ]))
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    public function editor(?string $petKey = null): array
    {
        $user = $this->actor->requireUser();
        $speciesOptions = $this->taxonomy->species();
        $petProfiles = PetProfile::query()
            ->select([
                'id',
                'user_id',
                'profile_key',
                'slug',
                'name',
                'species',
                'breed',
                'birth_date',
                'birth_date_precision',
                'estimated_age_months',
                'estimated_age_recorded_at',
                'birthday_celebration_month',
                'birthday_celebration_day',
                'status',
            ])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('id')
            ->limit(50)
            ->get();
        $selectedPet = $petProfiles->first(
            static fn (PetProfile $profile): bool => filled($petKey)
                && in_array($petKey, [$profile->profile_key, $profile->slug], true),
        ) ?? $petProfiles->first();
        $defaultSpecies = $selectedPet instanceof PetProfile
            ? $this->petSpeciesKey($selectedPet, $speciesOptions)
            : 'other';

        return [
            ...$this->page(__('messages.report_a_missing_or_found_animal'), 'lost-found'),
            'types' => $this->taxonomy->types(),
            'type_descriptions' => $this->taxonomy->typeDescriptions(),
            'species_options' => $speciesOptions,
            'size_options' => $this->taxonomy->sizes(),
            'microchip_options' => $this->taxonomy->microchipStatuses(),
            'pet_options' => $petProfiles
                ->mapWithKeys(fn (PetProfile $profile): array => [
                    $profile->id => __('lost_found.interface.pet_option', [
                        'name' => $profile->name,
                        'species' => $speciesOptions[$this->petSpeciesKey($profile, $speciesOptions)],
                        'breed' => $profile->breed ?: __('lost_found.interface.breed_not_recorded'),
                    ]),
                ])
                ->all(),
            'default_pet_id' => $selectedPet?->id,
            'default_pet' => $selectedPet instanceof PetProfile
                ? [
                    'name' => $selectedPet->name,
                    'breed' => $selectedPet->breed,
                    'age' => $this->petAgeLabels->for($selectedPet),
                ]
                : null,
            'default_species' => $defaultSpecies,
            'default_type' => SearchCaseType::Lost->value,
            'default_size' => 'unknown',
            'default_microchip_status' => 'unknown',
            'default_last_seen_at' => now()->subMinutes(30)->format('Y-m-d\TH:i'),
            'default_country' => 'LT',
            'notification_radius_options' => collect([2, 5, 10, 25, 50])
                ->mapWithKeys(static fn (int $radius): array => [
                    $radius => __('presentation.kilometers', ['count' => $radius]),
                ])
                ->all(),
            'default_notification_radius' => 5,
        ];
    }

    /**
     * @param  array<string, string>  $speciesOptions
     */
    private function petSpeciesKey(PetProfile $profile, array $speciesOptions): string
    {
        $speciesKey = Str::kebab($profile->species);

        return array_key_exists($speciesKey, $speciesOptions) ? $speciesKey : 'other';
    }

    /** @return array<string, mixed> */
    public function detail(SearchCase $searchCase): array
    {
        $searchCase->increment('view_count');
        $this->loadTaxonomyContext($searchCase);

        $sightings = Sighting::query()
            ->select([
                'id', 'search_case_id', 'reporter_name', 'status', 'observed_at',
                'submitted_at', 'time_accuracy', 'public_area', 'public_latitude',
                'public_longitude', 'direction', 'confidence', 'contact_status',
                'animal_condition', 'danger', 'notes', 'photo_url',
                'video_url', 'is_anonymous', 'verified_at',
            ])
            ->where('search_case_id', $searchCase->id)
            ->where('status', SightingStatus::Confirmed->value)
            ->latest('observed_at')
            ->limit(12)
            ->get();

        $updates = SearchUpdate::query()
            ->select([
                'id', 'search_case_id', 'author_name', 'type', 'title', 'body',
                'visibility', 'public_area', 'occurred_at',
            ])
            ->where('search_case_id', $searchCase->id)
            ->public()
            ->latest('occurred_at')
            ->limit(16)
            ->get();

        $sectors = SearchSector::query()
            ->select(['id', 'search_case_id', 'code', 'label', 'status', 'priority'])
            ->where('search_case_id', $searchCase->id)
            ->orderBy('priority')
            ->orderBy('code')
            ->limit(16)
            ->get();

        $tasks = SearchTask::query()
            ->select([
                'id', 'search_case_id', 'search_sector_id', 'type', 'title',
                'description', 'status', 'safety_level', 'assignee_name',
                'assignee_key', 'starts_at', 'due_at', 'result',
            ])
            ->where('search_case_id', $searchCase->id)
            ->whereIn('status', [
                SearchTaskStatus::Open->value,
                SearchTaskStatus::Claimed->value,
                SearchTaskStatus::InProgress->value,
                SearchTaskStatus::NeedsHelp->value,
            ])
            ->with([
                'sector' => fn ($query) => $query->select(['id', 'code', 'label']),
            ])
            ->orderBy('due_at')
            ->limit(12)
            ->get();

        $alertReach = SearchAlert::query()
            ->where('search_case_id', $searchCase->id)
            ->where('status', 'sent')
            ->sum('recipient_count');

        $canManage = $searchCase->isManagedBy($this->actor->key());

        return [
            ...$this->page(__('presentation.lost_found_for', ['pet' => $searchCase->pet_name]), 'lost-found'),
            'search_case' => $this->publicDetail($searchCase),
            'sightings' => $sightings->map(fn (Sighting $sighting): array => $this->sighting($sighting))->all(),
            'updates' => $updates->map(fn (SearchUpdate $update): array => $this->update($update))->all(),
            'sectors' => $sectors->map(fn (SearchSector $sector): array => $this->sector($sector))->all(),
            'tasks' => $tasks->map(fn (SearchTask $task): array => $this->task($task))->all(),
            'organizations' => $this->nearbyOrganizations(),
            'map_markers' => $this->caseMarkers($searchCase, $sightings),
            'alert_reach' => $alertReach,
            'can_manage' => $canManage,
            'can_contact' => $this->gate->allows('contact', $searchCase),
            'can_submit_sighting' => $searchCase->alerts_active && ! $searchCase->status->isClosed(),
            'can_volunteer' => $searchCase->volunteer_join_open
                && $searchCase->alerts_active
                && ! $searchCase->status->isClosed(),
            'confidence_options' => $this->taxonomy->confidenceOptions(),
            'contact_statuses' => $this->taxonomy->contactStatuses(),
            'volunteer_capabilities' => $this->taxonomy->volunteerCapabilities(),
            'report_reasons' => $this->taxonomy->reportReasons(),
            'idempotency_key' => (string) Str::uuid(),
            'contact_idempotency_key' => (string) Str::uuid(),
            'relay_purposes' => $this->taxonomy->relayPurposes(),
        ];
    }

    /** @return array<string, mixed> */
    public function coordination(SearchCase $searchCase): array
    {
        $this->loadTaxonomyContext($searchCase);
        $searchCase->load([
            'sightings' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'reporter_key', 'reporter_name',
                    'status', 'observed_at', 'submitted_at', 'time_accuracy',
                    'public_area', 'public_latitude', 'public_longitude',
                    'exact_location', 'direction', 'distance', 'confidence',
                    'contact_status', 'animal_condition', 'danger', 'notes',
                    'photo_url', 'video_url', 'is_anonymous', 'risk_flags',
                    'verified_by_key', 'verified_at',
                ])
                ->latest('observed_at')
                ->latest('id')
                ->limit(self::COORDINATION_COLLECTION_LIMIT),
            'sectors' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'code', 'label', 'status', 'priority',
                    'map_bounds', 'risk_notes', 'access_notes', 'checked_by_key',
                    'checked_at',
                ])
                ->orderBy('priority')
                ->orderBy('code')
                ->orderBy('id')
                ->limit(self::COORDINATION_COLLECTION_LIMIT),
            'tasks' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'search_sector_id', 'assignee_key',
                    'assignee_name', 'type', 'title', 'description', 'status',
                    'safety_level', 'starts_at', 'due_at', 'claimed_at',
                    'completed_at', 'result', 'version',
                ])
                ->with(['sector' => fn ($sectors) => $sectors->select(['id', 'code', 'label'])])
                ->orderBy('due_at')
                ->orderBy('id')
                ->limit(self::COORDINATION_COLLECTION_LIMIT),
            'volunteers' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'actor_key', 'display_name', 'role',
                    'capabilities', 'status', 'privacy_level', 'available_until',
                    'joined_at', 'last_check_in_at', 'location_expires_at',
                ])
                ->latest('joined_at')
                ->latest('id')
                ->limit(self::COORDINATION_COLLECTION_LIMIT),
            'updates' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'author_name', 'type', 'visibility',
                    'title', 'body', 'public_area', 'occurred_at',
                ])
                ->latest('occurred_at')
                ->latest('id')
                ->limit(self::COORDINATION_COLLECTION_LIMIT),
            'alerts' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'kind', 'radius_km', 'region',
                    'channels', 'audiences', 'status', 'recipient_count',
                    'message', 'sent_at', 'stopped_at',
                ])
                ->latest('created_at')
                ->latest('id')
                ->limit(self::COORDINATION_HISTORY_LIMIT),
            'events' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'actor_user_id', 'event_type',
                    'previous_status', 'current_status', 'reason_translation_key',
                    'metadata', 'created_at',
                ])
                ->with(['actor' => fn ($actors) => $actors->select(['id', 'name', 'actor_key'])])
                ->latest('created_at')
                ->latest('id')
                ->limit(self::COORDINATION_HISTORY_LIMIT),
            'contactRelays' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'sender_user_id', 'recipient_user_id',
                    'purpose', 'message', 'status', 'read_at', 'created_at',
                ])
                ->with(['sender' => fn ($senders) => $senders->select(['id', 'name', 'actor_key'])])
                ->latest('created_at')
                ->latest('id')
                ->limit(self::COORDINATION_HISTORY_LIMIT),
        ]);

        return [
            ...$this->page(__('presentation.coordinate_search_for', ['pet' => $searchCase->pet_name]), 'lost-found'),
            'search_case' => [
                ...$this->publicDetail($searchCase),
                'hidden_marks' => $searchCase->hidden_marks,
                'exact_location' => $searchCase->exact_location,
                'contact_details' => $searchCase->contact_details,
                'risk_flags' => $searchCase->risk_flags ?? [],
            ],
            'sightings' => $searchCase->sightings
                ->map(fn (Sighting $sighting): array => [
                    ...$this->sighting($sighting),
                    'reporter_key' => $sighting->reporter_key,
                    'exact_location' => $sighting->exact_location,
                    'risk_flags' => $sighting->risk_flags ?? [],
                ])
                ->all(),
            'sectors' => $searchCase->sectors->map(fn (SearchSector $sector): array => [
                ...$this->sector($sector),
                'risk_notes' => $sector->risk_notes,
                'access_notes' => $sector->access_notes,
                'checked_by_key' => $sector->checked_by_key,
                'checked_label' => $this->formatter->relative($sector->checked_at),
            ])->all(),
            'tasks' => $searchCase->tasks->map(fn (SearchTask $task): array => $this->task($task))->all(),
            'volunteers' => $searchCase->volunteers
                ->map(fn (SearchVolunteer $volunteer): array => $this->volunteer($volunteer))
                ->all(),
            'updates' => $searchCase->updates->map(fn (SearchUpdate $update): array => $this->update($update))->all(),
            'alerts' => $searchCase->alerts->map(fn (SearchAlert $alert): array => $this->alert($alert))->all(),
            'events' => $searchCase->events
                ->map(fn (SearchCaseEvent $event): array => [
                    'id' => $event->id,
                    'type' => $event->event_type,
                    'label' => __($event->reason_translation_key),
                    'actor_name' => $event->actor?->name,
                    'previous_status' => $event->previous_status !== null
                        ? (SearchStatus::tryFrom($event->previous_status)?->label() ?? $event->previous_status)
                        : null,
                    'current_status' => $event->current_status !== null
                        ? (SearchStatus::tryFrom($event->current_status)?->label() ?? $event->current_status)
                        : null,
                    'created_label' => $this->formatter->dateTime($event->created_at),
                ])
                ->all(),
            'contact_relays' => $searchCase->contactRelays
                ->map(fn (SearchContactRelay $relay): array => [
                    'id' => $relay->id,
                    'sender_name' => $relay->sender->name,
                    'purpose' => $this->taxonomy->relayPurposes()[$relay->purpose] ?? $relay->purpose,
                    'message' => $relay->message,
                    'status' => $relay->status,
                    'created_label' => $this->formatter->dateTime($relay->created_at),
                ])
                ->all(),
            'organizations' => $this->nearbyOrganizations(),
            'map_markers' => $this->caseMarkers($searchCase, $searchCase->sightings),
            'statuses' => $this->taxonomy->statuses(),
            'task_types' => $this->taxonomy->taskTypes(),
        ];
    }

    /** @return array<string, mixed> */
    public function poster(SearchCase $searchCase): array
    {
        $url = route('lost-found.show', $searchCase);
        $detail = $this->publicDetail($searchCase);
        $headingKey = $searchCase->status->isClosed()
            ? 'found'
            : ($searchCase->type === SearchCaseType::Lost ? 'missing' : 'found_animal');

        return [
            ...$this->page(__('lost_found.poster.page_title', ['pet' => $searchCase->pet_name]), 'lost-found'),
            'html_locale' => str_replace('_', '-', App::currentLocale()),
            'search_case' => $detail,
            'public_url' => $url,
            'qr_code' => $this->qrCodes->dataUri($url),
            'poster' => [
                'heading' => __("lost_found.poster.headings.{$headingKey}"),
                'pet_summary' => __('lost_found.poster.pet_summary', [
                    'species' => $detail['species_label'],
                    'breed' => $searchCase->breed ?: __('lost_found.poster.breed_unknown'),
                    'color' => $detail['color'],
                ]),
                'image_alt' => __('lost_found.poster.image_alt', [
                    'pet' => $searchCase->pet_name,
                    'species' => mb_strtolower($detail['species_label']),
                    'color' => $detail['color'],
                ]),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function page(string $title, string $section): array
    {
        return [
            'owner' => $this->profiles->owner(),
            'page_title' => $title,
            'active_section' => $section,
        ];
    }

    /** @return array<string, mixed> */
    private function card(SearchCase $searchCase): array
    {
        return [
            'slug' => $searchCase->slug,
            'public_code' => $searchCase->public_code,
            'pet_name' => $searchCase->pet_name,
            'species_label' => $this->speciesLabel($searchCase->species),
            'breed' => $searchCase->breed,
            'color' => $searchCase->primary_color,
            'type' => $searchCase->type->value,
            'type_label' => $searchCase->type->label(),
            'type_icon' => $searchCase->type->icon(),
            'status' => $searchCase->status->value,
            'status_label' => $searchCase->status->label(),
            'status_tone' => $searchCase->status->tone(),
            'urgent' => $searchCase->alerts_active && $searchCase->status->isUrgent(),
            'description' => Str::limit($searchCase->description, 150),
            'distinctive_marks' => $searchCase->distinctive_marks,
            'health_notice' => $searchCase->health_notice,
            'last_seen_area' => $searchCase->last_seen_area,
            'last_seen_label' => $this->formatter->relative($searchCase->last_seen_at),
            'latest_update' => $searchCase->latest_update,
            'latest_update_label' => $this->formatter->relative($searchCase->last_sighting_at)
                ?? $this->formatter->relative($searchCase->updated_at),
            'cover_url' => $searchCase->cover_url,
            'confirmed_sightings_count' => (int) ($searchCase->confirmed_sightings_count ?? 0),
            'active_volunteers_count' => (int) ($searchCase->active_volunteers_count ?? 0),
            'open_tasks_count' => (int) ($searchCase->open_tasks_count ?? 0),
            'map_x' => $this->mapPosition($searchCase->public_longitude, $searchCase->id, 18),
            'map_y' => $this->mapPosition($searchCase->public_latitude, $searchCase->id, 27),
        ];
    }

    /** @return array<string, mixed> */
    private function publicDetail(SearchCase $searchCase): array
    {
        return [
            ...$this->card($searchCase),
            'owner_name' => $searchCase->owner_name,
            'owner_initials' => $searchCase->owner_initials,
            'coordinator_name' => $searchCase->coordinator_name,
            'sex' => $searchCase->sex ? Str::headline($searchCase->sex) : null,
            'age_label' => $searchCase->age_label,
            'size_label' => $searchCase->size
                ? ($this->taxonomy->sizes()[$searchCase->size] ?? Str::headline($searchCase->size))
                : null,
            'coat' => $searchCase->coat,
            'description' => $searchCase->description,
            'approach_instructions' => $searchCase->approach_instructions,
            'avoid_instructions' => $searchCase->avoid_instructions,
            'accessories' => $searchCase->accessories ?? [],
            'accessories_label' => collect($searchCase->accessories ?? [])->join(', '),
            'temperament' => $searchCase->temperament,
            'microchip_label' => $this->taxonomy->microchipStatuses()[$searchCase->microchip_status]
                ?? Str::headline($searchCase->microchip_status),
            'direction' => $searchCase->direction,
            'city' => $searchCase->city,
            'notification_radius_km' => $searchCase->notification_radius_km,
            'alerts_active' => $searchCase->alerts_active,
            'volunteer_join_open' => $searchCase->volunteer_join_open,
            'animal_secured' => $searchCase->animal_secured,
            'contact_protected' => $searchCase->contact_protected,
            'reward_offered' => $searchCase->reward_offered,
            'reward_summary' => $searchCase->reward_summary,
            'scientific_name' => $searchCase->relationLoaded('taxon')
                ? $searchCase->taxon?->activeVersion?->scientific_name
                : null,
            'domestic_classification' => $searchCase->relationLoaded('domesticClassification')
                ? $searchCase->domesticClassification?->canonical_name
                : null,
            'photos' => collect($searchCase->photos ?? [])
                ->map(fn (string $path): string => $this->mediaUrl->for($path))
                ->all(),
            'reported_label' => $this->formatter->dateTime($searchCase->reported_at),
            'returned_label' => $this->formatter->dateTime($searchCase->returned_at),
            'closure_reason' => $searchCase->closure_reason,
            'archived' => $searchCase->archived_at !== null,
            'archived_label' => $this->formatter->dateTime($searchCase->archived_at),
            'can_archive' => $searchCase->status->isClosed() && $searchCase->archived_at === null,
            'lock_version' => $searchCase->lock_version,
            'view_count' => $searchCase->view_count,
            'poster_url' => route('lost-found.poster', $searchCase),
        ];
    }

    private function loadTaxonomyContext(SearchCase $searchCase): void
    {
        $searchCase->loadMissing([
            'taxon' => fn ($taxa) => $taxa->select(['id', 'stable_key']),
            'taxon.activeVersion' => fn ($versions) => $versions
                ->select(['id', 'taxon_id', 'scientific_name', 'rank']),
            'domesticClassification' => fn ($classifications) => $classifications
                ->select(['id', 'taxon_id', 'canonical_name', 'classification_type']),
        ]);
    }

    /** @return array<string, mixed> */
    private function sighting(Sighting $sighting): array
    {
        return [
            'id' => $sighting->id,
            'reporter_name' => $sighting->is_anonymous ? __('messages.anonymous_witness') : $sighting->reporter_name,
            'status' => $sighting->status->value,
            'status_label' => $sighting->status->label(),
            'public_area' => $sighting->public_area,
            'observed_label' => $this->formatter->dateTime($sighting->observed_at),
            'submitted_label' => $this->formatter->relative($sighting->submitted_at),
            'direction' => $sighting->direction,
            'confidence' => $this->taxonomy->confidenceOptions()[$sighting->confidence]
                ?? Str::headline($sighting->confidence),
            'contact_status' => $this->taxonomy->contactStatuses()[$sighting->contact_status]
                ?? Str::headline($sighting->contact_status),
            'animal_condition' => $sighting->animal_condition,
            'danger' => $sighting->danger,
            'notes' => $sighting->notes,
            'photo_url' => filled($sighting->photo_url)
                ? $this->mediaUrl->for((string) $sighting->photo_url)
                : null,
            'video_url' => filled($sighting->video_url)
                ? $this->mediaUrl->for((string) $sighting->video_url)
                : null,
            'map_x' => $this->mapPosition($sighting->public_longitude, $sighting->id, 37),
            'map_y' => $this->mapPosition($sighting->public_latitude, $sighting->id, 49),
        ];
    }

    /** @return array<string, mixed> */
    private function sector(SearchSector $sector): array
    {
        return [
            'id' => $sector->id,
            'code' => $sector->code,
            'label' => $sector->label,
            'status' => $sector->status->value,
            'status_label' => $sector->status->label(),
            'priority' => $sector->priority,
        ];
    }

    /** @return array<string, mixed> */
    private function task(SearchTask $task): array
    {
        return [
            'id' => $task->id,
            'type' => $task->type,
            'type_label' => $this->taxonomy->taskTypes()[$task->type] ?? Str::headline($task->type),
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'status_label' => $task->status->label(),
            'safety_level' => $task->safety_level,
            'safety_label' => Str::headline($task->safety_level),
            'assignee_key' => $task->assignee_key,
            'assignee_name' => $task->assignee_name,
            'is_actor_assignee' => $task->assignee_key === $this->actor->key(),
            'sector' => $task->sector?->label,
            'starts_label' => $this->formatter->dateTime($task->starts_at),
            'due_label' => $this->formatter->dateTime($task->due_at),
            'result' => $task->result,
        ];
    }

    /** @return array<string, mixed> */
    private function volunteer(SearchVolunteer $volunteer): array
    {
        return [
            'id' => $volunteer->id,
            'display_name' => $volunteer->display_name,
            'role' => Str::headline($volunteer->role),
            'capabilities' => collect($volunteer->capabilities ?? [])
                ->map(fn (string $capability): string => $this->taxonomy->volunteerCapabilities()[$capability]
                    ?? Str::headline($capability))
                ->all(),
            'status' => $volunteer->status->value,
            'status_label' => $volunteer->status->label(),
            'available_until' => $this->formatter->dateTime($volunteer->available_until),
            'last_check_in' => $this->formatter->relative($volunteer->last_check_in_at),
        ];
    }

    /** @return array<string, mixed> */
    private function update(SearchUpdate $update): array
    {
        return [
            'id' => $update->id,
            'author_name' => $update->author_name,
            'type' => $update->type,
            'type_label' => Str::headline($update->type),
            'title' => $update->title,
            'body' => $update->body,
            'public_area' => $update->public_area,
            'visibility' => $update->visibility,
            'occurred_label' => $this->formatter->dateTime($update->occurred_at),
        ];
    }

    /** @return array<string, mixed> */
    private function alert(SearchAlert $alert): array
    {
        return [
            'id' => $alert->id,
            'kind' => Str::headline($alert->kind),
            'radius_km' => $alert->radius_km,
            'region' => $alert->region,
            'channels' => $alert->channels ?? [],
            'audiences' => $alert->audiences ?? [],
            'status' => $alert->status,
            'recipient_count' => $alert->recipient_count,
            'message' => $alert->message,
            'sent_label' => $this->formatter->relative($alert->sent_at),
        ];
    }

    /**
     * @param  Collection<int, Sighting>  $sightings
     * @return array<int, array<string, mixed>>
     */
    private function caseMarkers(SearchCase $searchCase, Collection $sightings): array
    {
        return collect([[
            'kind' => 'last-seen',
            'label' => __('messages.last_confirmed_location'),
            'area' => $searchCase->last_seen_area,
            'time' => $this->formatter->dateTime($searchCase->last_seen_at),
            'x' => $this->mapPosition($searchCase->public_longitude, $searchCase->id, 18),
            'y' => $this->mapPosition($searchCase->public_latitude, $searchCase->id, 27),
        ]])
            ->merge($sightings->map(fn (Sighting $sighting): array => [
                'kind' => $sighting->status === SightingStatus::Confirmed ? 'confirmed' : 'possible',
                'label' => $sighting->status->label(),
                'area' => $sighting->public_area,
                'time' => $this->formatter->dateTime($sighting->observed_at),
                'x' => $this->mapPosition($sighting->public_longitude, $sighting->id, 37),
                'y' => $this->mapPosition($sighting->public_latitude, $sighting->id, 49),
            ]))
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function nearbyOrganizations(): array
    {
        return collect($this->places->forCategories(
            ['vet', 'emergency-vet', 'shelter'],
            6,
        ))
            ->map(fn (array $place): array => [
                'key' => $place['key'],
                'name' => $place['name'],
                'category' => $place['category_label'],
                'icon' => $place['category_icon'],
                'general_location' => $place['general_location'],
                'open_label' => $place['open_label'],
                'phone' => $place['phone'],
                'emergency' => $place['emergency'],
            ])
            ->values()
            ->all();
    }

    private function speciesLabel(string $species): string
    {
        return $this->taxonomy->species()[$species] ?? Str::headline($species);
    }

    private function mapPosition(string|float|null $coordinate, int $fallback, int $salt): int
    {
        if ($coordinate !== null) {
            $fraction = fmod(abs((float) $coordinate * 1000), 70);

            return 15 + (int) $fraction;
        }

        return 15 + (($fallback * $salt) % 70);
    }
}

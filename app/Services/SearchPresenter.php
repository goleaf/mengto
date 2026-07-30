<?php

namespace App\Services;

use App\Enums\SearchTaskStatus;
use App\Enums\SearchVolunteerStatus;
use App\Enums\SightingStatus;
use App\Models\SearchAlert;
use App\Models\SearchCase;
use App\Models\SearchSector;
use App\Models\SearchTask;
use App\Models\SearchUpdate;
use App\Models\SearchVolunteer;
use App\Models\Sighting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SearchPresenter
{
    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumActor $actor,
        private readonly SearchTaxonomy $taxonomy,
        private readonly PlaceCatalog $places,
        private readonly QrCodeGenerator $qrCodes,
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
            ...$this->page('Lost & found', 'lost-found'),
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
        $petKey = filled($petKey) ? $petKey : 'scout';
        $pet = $this->profiles->pet($petKey) ?? $this->profiles->pet('scout');

        return [
            ...$this->page('Report a missing or found animal', 'lost-found'),
            'types' => $this->taxonomy->types(),
            'species_options' => $this->taxonomy->species(),
            'size_options' => $this->taxonomy->sizes(),
            'microchip_options' => $this->taxonomy->microchipStatuses(),
            'pet_options' => [
                'scout' => 'Scout · dog',
                'nori' => 'Nori · cat',
            ],
            'default_pet' => $pet,
        ];
    }

    /** @return array<string, mixed> */
    public function detail(SearchCase $searchCase): array
    {
        $searchCase->increment('view_count');

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
            ...$this->page($searchCase->pet_name.' · Lost & found', 'lost-found'),
            'search_case' => $this->publicDetail($searchCase),
            'sightings' => $sightings->map(fn (Sighting $sighting): array => $this->sighting($sighting))->all(),
            'updates' => $updates->map(fn (SearchUpdate $update): array => $this->update($update))->all(),
            'sectors' => $sectors->map(fn (SearchSector $sector): array => $this->sector($sector))->all(),
            'tasks' => $tasks->map(fn (SearchTask $task): array => $this->task($task))->all(),
            'organizations' => $this->nearbyOrganizations(),
            'map_markers' => $this->caseMarkers($searchCase, $sightings),
            'alert_reach' => $alertReach,
            'can_manage' => $canManage,
            'can_submit_sighting' => $searchCase->alerts_active && ! $searchCase->status->isClosed(),
            'can_volunteer' => $searchCase->volunteer_join_open
                && $searchCase->alerts_active
                && ! $searchCase->status->isClosed(),
            'confidence_options' => $this->taxonomy->confidenceOptions(),
            'contact_statuses' => $this->taxonomy->contactStatuses(),
            'volunteer_capabilities' => $this->taxonomy->volunteerCapabilities(),
            'report_reasons' => $this->taxonomy->reportReasons(),
            'idempotency_key' => (string) Str::uuid(),
        ];
    }

    /** @return array<string, mixed> */
    public function coordination(SearchCase $searchCase): array
    {
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
                ->latest('observed_at'),
            'sectors' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'code', 'label', 'status', 'priority',
                    'map_bounds', 'risk_notes', 'access_notes', 'checked_by_key',
                    'checked_at',
                ])
                ->orderBy('priority')
                ->orderBy('code'),
            'tasks' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'search_sector_id', 'assignee_key',
                    'assignee_name', 'type', 'title', 'description', 'status',
                    'safety_level', 'starts_at', 'due_at', 'claimed_at',
                    'completed_at', 'result', 'version',
                ])
                ->with(['sector' => fn ($sectors) => $sectors->select(['id', 'code', 'label'])])
                ->orderBy('due_at'),
            'volunteers' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'actor_key', 'display_name', 'role',
                    'capabilities', 'status', 'privacy_level', 'available_until',
                    'joined_at', 'last_check_in_at', 'location_expires_at',
                ])
                ->latest('joined_at'),
            'updates' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'author_name', 'type', 'visibility',
                    'title', 'body', 'public_area', 'occurred_at',
                ])
                ->latest('occurred_at'),
            'alerts' => fn ($query) => $query
                ->select([
                    'id', 'search_case_id', 'kind', 'radius_km', 'region',
                    'channels', 'audiences', 'status', 'recipient_count',
                    'message', 'sent_at', 'stopped_at',
                ])
                ->latest('created_at'),
        ]);

        return [
            ...$this->page('Coordinate '.$searchCase->pet_name."'s search", 'lost-found'),
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
                'checked_label' => $sector->checked_at?->diffForHumans(),
            ])->all(),
            'tasks' => $searchCase->tasks->map(fn (SearchTask $task): array => $this->task($task))->all(),
            'volunteers' => $searchCase->volunteers
                ->map(fn (SearchVolunteer $volunteer): array => $this->volunteer($volunteer))
                ->all(),
            'updates' => $searchCase->updates->map(fn (SearchUpdate $update): array => $this->update($update))->all(),
            'alerts' => $searchCase->alerts->map(fn (SearchAlert $alert): array => $this->alert($alert))->all(),
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

        return [
            ...$this->page('Poster · '.$searchCase->pet_name, 'lost-found'),
            'search_case' => $this->publicDetail($searchCase),
            'public_url' => $url,
            'qr_code' => $this->qrCodes->dataUri($url),
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
            'last_seen_label' => $searchCase->last_seen_at?->diffForHumans(),
            'latest_update' => $searchCase->latest_update,
            'latest_update_label' => $searchCase->last_sighting_at?->diffForHumans()
                ?? $searchCase->updated_at?->diffForHumans(),
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
            'microchip_label' => $this->taxonomy->microchipStatuses()[$searchCase->microchip_status]
                ?? Str::headline($searchCase->microchip_status),
            'direction' => $searchCase->direction,
            'city' => $searchCase->city,
            'notification_radius_km' => $searchCase->notification_radius_km,
            'alerts_active' => $searchCase->alerts_active,
            'volunteer_join_open' => $searchCase->volunteer_join_open,
            'animal_secured' => $searchCase->animal_secured,
            'contact_protected' => $searchCase->contact_protected,
            'photos' => $searchCase->photos ?? [],
            'reported_label' => $searchCase->reported_at?->format('M j, Y · H:i'),
            'returned_label' => $searchCase->returned_at?->format('M j, Y · H:i'),
            'closure_reason' => $searchCase->closure_reason,
            'view_count' => $searchCase->view_count,
            'poster_url' => route('lost-found.poster', $searchCase),
        ];
    }

    /** @return array<string, mixed> */
    private function sighting(Sighting $sighting): array
    {
        return [
            'id' => $sighting->id,
            'reporter_name' => $sighting->is_anonymous ? 'Anonymous witness' : $sighting->reporter_name,
            'status' => $sighting->status->value,
            'status_label' => $sighting->status->label(),
            'public_area' => $sighting->public_area,
            'observed_label' => $sighting->observed_at?->format('M j · H:i'),
            'submitted_label' => $sighting->submitted_at?->diffForHumans(),
            'direction' => $sighting->direction,
            'confidence' => $this->taxonomy->confidenceOptions()[$sighting->confidence]
                ?? Str::headline($sighting->confidence),
            'contact_status' => $this->taxonomy->contactStatuses()[$sighting->contact_status]
                ?? Str::headline($sighting->contact_status),
            'animal_condition' => $sighting->animal_condition,
            'danger' => $sighting->danger,
            'notes' => $sighting->notes,
            'photo_url' => $sighting->photo_url,
            'video_url' => $sighting->video_url,
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
            'starts_label' => $task->starts_at?->format('M j · H:i'),
            'due_label' => $task->due_at?->format('M j · H:i'),
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
            'available_until' => $volunteer->available_until?->format('M j · H:i'),
            'last_check_in' => $volunteer->last_check_in_at?->diffForHumans(),
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
            'occurred_label' => $update->occurred_at?->format('M j · H:i'),
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
            'sent_label' => $alert->sent_at?->diffForHumans(),
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
            'label' => 'Last confirmed location',
            'area' => $searchCase->last_seen_area,
            'time' => $searchCase->last_seen_at?->format('M j · H:i'),
            'x' => $this->mapPosition($searchCase->public_longitude, $searchCase->id, 18),
            'y' => $this->mapPosition($searchCase->public_latitude, $searchCase->id, 27),
        ]])
            ->merge($sightings->map(fn (Sighting $sighting): array => [
                'kind' => $sighting->status === SightingStatus::Confirmed ? 'confirmed' : 'possible',
                'label' => $sighting->status->label(),
                'area' => $sighting->public_area,
                'time' => $sighting->observed_at?->format('M j · H:i'),
                'x' => $this->mapPosition($sighting->public_longitude, $sighting->id, 37),
                'y' => $this->mapPosition($sighting->public_latitude, $sighting->id, 49),
            ]))
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function nearbyOrganizations(): array
    {
        return collect($this->places->all())
            ->filter(fn (array $place): bool => in_array(
                $place['primary_category'],
                ['vet', 'emergency-vet', 'shelter'],
                true,
            ))
            ->take(6)
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

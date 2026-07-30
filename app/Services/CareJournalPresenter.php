<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CareEntryStatus;
use App\Enums\CareEntryType;
use App\Enums\CareSyncStatus;
use App\Enums\CareTaskStatus;
use App\Enums\MedicationStatus;
use App\Models\AuditLog;
use App\Models\CareAccessGrant;
use App\Models\CareEntry;
use App\Models\CareJournal;
use App\Models\CareRoutine;
use App\Models\CareTask;
use App\Models\MedicalRecord;
use App\Models\Medication;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CareJournalPresenter
{
    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumActor $actor,
        private readonly LocaleFormatter $formatter,
    ) {}

    /** @return array<string, mixed> */
    public function directory(): array
    {
        $start = CarbonImmutable::now()->startOfDay();
        $end = $start->endOfDay();
        $records = CareJournal::query()
            ->forOwnerDirectory($this->actor->key())
            ->withCount([
                'entries as today_entries_count' => fn (Builder $query): Builder => $query
                    ->whereBetween('started_at', [$start, $end]),
                'entries as unusual_entries_count' => fn (Builder $query): Builder => $query
                    ->where('is_unusual', true)
                    ->where('started_at', '>=', $start->subDays(7)),
                'tasks as open_tasks_count' => fn (Builder $query): Builder => $query
                    ->whereIn('status', $this->openTaskStatuses()),
                'tasks as overdue_tasks_count' => fn (Builder $query): Builder => $query
                    ->whereIn('status', $this->openTaskStatuses())
                    ->where('due_at', '<', now()),
            ])
            ->latest('updated_at')
            ->simplePaginate(9);

        $records->through(fn (CareJournal $journal): array => $this->journalCard($journal));

        return [
            ...$this->page(__('messages.private_care_journals_f718a9186c'), 'care'),
            'journals' => $records,
        ];
    }

    /** @return array<string, mixed> */
    public function editor(): array
    {
        $existing = CareJournal::query()
            ->select(['id', 'owner_key', 'pet_profile_key'])
            ->where('owner_key', $this->actor->key())
            ->whereIn('pet_profile_key', ['scout', 'nori'])
            ->pluck('pet_profile_key')
            ->all();

        $options = collect(['scout', 'nori'])
            ->reject(fn (string $key): bool => in_array($key, $existing, true))
            ->mapWithKeys(function (string $key): array {
                $pet = $this->profiles->pet($key);

                return $pet === null ? [] : [$key => $pet['name'].' · '.$pet['species']];
            })
            ->all();

        return [
            ...$this->page(__('messages.create_a_private_care_journal_4275e0de97'), 'care'),
            'pet_options' => $options,
            'timezone' => 'Europe/Vilnius',
        ];
    }

    /** @return array<string, mixed> */
    public function show(CareJournal $journal, bool $manage = false): array
    {
        $today = CarbonImmutable::now($journal->timezone);
        $dayStart = $today->startOfDay();
        $dayEnd = $today->endOfDay();
        $weekStart = $dayStart->subDays(6);

        $entries = CareEntry::query()
            ->forPeriod($journal->id, $weekStart, $dayEnd)
            ->with([
                'media' => fn ($media) => $media->select([
                    'id', 'care_journal_id', 'care_entry_id', 'mime_type',
                    'size_bytes', 'alt_text', 'sensitivity', 'created_at',
                ]),
            ])
            ->latest('started_at')
            ->limit(180)
            ->get();

        $tasks = CareTask::query()
            ->openForJournal($journal->id)
            ->orderBy('due_at')
            ->limit(24)
            ->get();

        $routines = CareRoutine::query()
            ->activeForJournal($journal->id)
            ->orderBy('start_time')
            ->limit(16)
            ->get();

        $accessGrants = CareAccessGrant::query()
            ->select([
                'id', 'care_journal_id', 'recipient_name', 'recipient_role',
                'label', 'sections', 'permissions', 'allow_add',
                'allow_location', 'allow_media', 'max_views', 'views_used',
                'expires_at', 'last_opened_at', 'revoked_at', 'created_at',
            ])
            ->where('care_journal_id', $journal->id)
            ->latest('created_at')
            ->limit(20)
            ->get();

        $medical = $this->medicationSummary($journal);
        $todayEntries = $entries
            ->filter(fn (CareEntry $entry): bool => $entry->started_at?->betweenIncluded($dayStart, $dayEnd))
            ->values();

        $data = [
            ...$this->page(__('presentation.care_journal_for', ['pet' => $journal->pet_name]), 'care'),
            'care_journal' => $this->journal($journal),
            'today_summary' => $this->todaySummary($journal, $todayEntries, $tasks),
            'entries' => $entries
                ->take(40)
                ->map(fn (CareEntry $entry): array => $this->entry($journal, $entry))
                ->values()
                ->all(),
            'tasks' => $tasks->map(fn (CareTask $task): array => $this->task($task))->all(),
            'routines' => $routines->map(fn (CareRoutine $routine): array => $this->routine($routine))->all(),
            'weekly' => $this->weekly($entries, $weekStart, $dayEnd),
            'entry_types' => $this->entryTypes(),
            'access_section_options' => collect([
                'summary',
                'feeding',
                'water',
                'walks',
                'toilet',
                'sleep',
                'activity',
                'care',
                'observations',
                'tasks',
            ])->map(fn (string $section): array => [
                'value' => $section,
                'label' => Str::headline($section),
            ])->all(),
            'entry_idempotency_key' => (string) Str::uuid(),
            'form_defaults' => [
                'started_at' => $today->startOfMinute()->format('Y-m-d\TH:i'),
                'task_due_at' => $today->addHour()->startOfMinute()->format('Y-m-d\TH:i'),
                'routine_starts_on' => $today->toDateString(),
            ],
            'medical' => $medical,
            'access_grants' => $accessGrants
                ->map(fn (CareAccessGrant $grant): array => $this->grant($grant))
                ->all(),
        ];

        if (! $manage) {
            return $data;
        }

        $audits = AuditLog::query()
            ->select([
                'id', 'actor_key', 'actor_role', 'action', 'target_type',
                'target_id', 'metadata', 'created_at',
            ])
            ->whereIn('action', [
                'care-journal.created',
                'care-entry.created',
                'care-task.created',
                'care-routine.created',
                'care-access.created',
                'care-access.revoked',
                'care-media.downloaded',
            ])
            ->where('created_at', '>=', now()->subDays(30))
            ->latest('created_at')
            ->limit(30)
            ->get()
            ->filter(fn (AuditLog $log): bool => ($log->metadata['care_journal_id'] ?? null) === $journal->id
                || ($log->target_type === CareJournal::class && (int) $log->target_id === $journal->id))
            ->map(fn (AuditLog $log): array => [
                'action' => Str::headline($log->action),
                'actor' => $log->actor_role,
                'time' => $this->formatter->dateTime($log->created_at),
            ])
            ->values()
            ->all();

        return [
            ...$data,
            'audits' => $audits,
            'task_idempotency_key' => (string) Str::uuid(),
            'care_access_url' => session('care_access_url'),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function report(CareJournal $journal, array $filters): array
    {
        $timezone = $journal->timezone;
        $to = CarbonImmutable::parse($filters['to'] ?? 'today', $timezone)->endOfDay();
        $from = CarbonImmutable::parse(
            $filters['from'] ?? $to->subDays(6)->toDateString(),
            $timezone,
        )->startOfDay();

        if ($from->diffInDays($to) > 31) {
            $from = $to->subDays(31)->startOfDay();
        }

        $entries = CareEntry::query()
            ->forPeriod($journal->id, $from, $to)
            ->latest('started_at')
            ->limit(500)
            ->get();

        return [
            ...$this->page(__('presentation.care_report_for', ['pet' => $journal->pet_name]), 'care'),
            'care_journal' => $this->journal($journal),
            'period' => $this->formatter->date($from).' - '.$this->formatter->date($to),
            'entries' => $entries
                ->map(fn (CareEntry $entry): array => $this->entry($journal, $entry))
                ->all(),
            'weekly' => $this->weekly($entries, $from, $to),
            'source_note' => __('messages.this_report_contains_recorded_facts_only_missing_entries_eb18d34b1e'),
        ];
    }

    /** @return array<string, mixed> */
    public function shared(CareAccessGrant $grant, string $token): array
    {
        $journal = $grant->careJournal;
        $types = collect(CareEntryType::cases())
            ->filter(fn (CareEntryType $type): bool => $grant->canViewSection($type->section()))
            ->values();
        $entryQuery = CareEntry::query()
            ->forTimeline()
            ->where('care_journal_id', $journal->id)
            ->whereIn('type', $types->map->value->all())
            ->latest('started_at')
            ->limit(40);

        if ($grant->allow_media) {
            $entryQuery->with([
                'media' => fn ($media) => $media->select([
                    'id', 'care_journal_id', 'care_entry_id', 'mime_type',
                    'size_bytes', 'alt_text', 'sensitivity', 'created_at',
                ]),
            ]);
        }

        $entries = $entryQuery->get();
        $tasks = collect();

        if ($grant->canViewSection('tasks')) {
            $tasks = CareTask::query()
                ->openForJournal($journal->id)
                ->orderBy('due_at')
                ->limit(20)
                ->get();
        }

        return [
            ...$this->page(__('presentation.shared_care_plan_for', ['pet' => $journal->pet_name]), 'care'),
            'care_journal' => $this->journal($journal),
            'grant' => $this->grant($grant),
            'token' => $token,
            'entries' => $entries
                ->map(fn (CareEntry $entry): array => $this->entry(
                    $journal,
                    $entry,
                    $grant->allow_location,
                    $grant->allow_media,
                    $token,
                ))
                ->all(),
            'tasks' => $tasks->map(fn (CareTask $task): array => $this->task($task))->all(),
            'entry_types' => $types->map(fn (CareEntryType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
                'icon' => $type->icon(),
            ])->all(),
            'entry_idempotency_key' => (string) Str::uuid(),
            'form_defaults' => [
                'started_at' => CarbonImmutable::now($journal->timezone)
                    ->startOfMinute()
                    ->format('Y-m-d\TH:i'),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function page(string $title, string $activeSection): array
    {
        return [
            'owner' => $this->profiles->owner(),
            'page_title' => __('presentation.brand_title', ['title' => $title]),
            'active_section' => $activeSection,
        ];
    }

    /** @return array<string, mixed> */
    private function journal(CareJournal $journal): array
    {
        return [
            'id' => $journal->id,
            'slug' => $journal->slug,
            'pet_profile_key' => $journal->pet_profile_key,
            'pet_name' => $journal->pet_name,
            'species' => Str::headline($journal->species),
            'breed' => $journal->breed ?: __('messages.breed_not_recorded_ebcac0c0af'),
            'image_url' => $journal->image_url,
            'privacy' => Str::headline($journal->privacy),
            'timezone' => $journal->timezone,
            'current_caregiver' => $journal->current_caregiver_name ?: __('messages.not_assigned_13075c2336'),
            'show_url' => route('care-journals.show', $journal),
            'manage_url' => route('care-journals.manage', $journal),
            'report_url' => route('care-journals.report', $journal),
            'medical_url' => route('medical-records.index'),
        ];
    }

    /** @return array<string, mixed> */
    private function journalCard(CareJournal $journal): array
    {
        return [
            ...$this->journal($journal),
            'today_entries_count' => (int) $journal->today_entries_count,
            'open_tasks_count' => (int) $journal->open_tasks_count,
            'overdue_tasks_count' => (int) $journal->overdue_tasks_count,
            'unusual_entries_count' => (int) $journal->unusual_entries_count,
            'last_feeding' => $this->relative($journal->last_feeding_at),
            'last_walk' => $this->relative($journal->last_walk_at),
            'updated_at' => $this->formatter->relative($journal->updated_at),
        ];
    }

    /**
     * @param  Collection<int, CareEntry>  $todayEntries
     * @param  Collection<int, CareTask>  $tasks
     * @return array<string, mixed>
     */
    private function todaySummary(
        CareJournal $journal,
        Collection $todayEntries,
        Collection $tasks,
    ): array {
        $count = fn (CareEntryType $type): int => $todayEntries
            ->where('type', $type)
            ->whereNotIn('status', [
                CareEntryStatus::Cancelled,
                CareEntryStatus::Skipped,
            ])
            ->count();

        return [
            'feeding_count' => $count(CareEntryType::Feeding),
            'water_count' => $count(CareEntryType::Water),
            'walk_count' => $count(CareEntryType::Walk),
            'toilet_count' => $count(CareEntryType::Toilet),
            'sleep_minutes' => (int) $todayEntries
                ->where('type', CareEntryType::Sleep)
                ->sum('duration_minutes'),
            'activity_minutes' => (int) $todayEntries
                ->whereIn('type', [CareEntryType::Activity, CareEntryType::Training])
                ->sum('duration_minutes'),
            'unusual_count' => $todayEntries->where('is_unusual', true)->count(),
            'open_tasks' => $tasks->count(),
            'overdue_tasks' => $tasks->filter(
                fn (CareTask $task): bool => $task->due_at?->isPast(),
            )->count(),
            'last_feeding' => $this->relative($journal->last_feeding_at),
            'last_water' => $this->relative($journal->last_water_at),
            'last_walk' => $this->relative($journal->last_walk_at),
            'last_toilet' => $this->relative($journal->last_toilet_at),
        ];
    }

    /** @return array<string, mixed> */
    private function entry(
        CareJournal $journal,
        CareEntry $entry,
        bool $showLocation = true,
        bool $showMedia = true,
        ?string $accessToken = null,
    ): array {
        $context = $entry->context ?? [];

        if (! $showLocation) {
            unset($context['location_label'], $context['route_summary']);
        }

        $media = $showMedia && $entry->relationLoaded('media')
            ? $entry->media->map(fn ($item): array => [
                'id' => $item->id,
                'mime_type' => $item->mime_type,
                'alt_text' => $item->alt_text ?: __('messages.private_care_journal_attachment_dba75610bc'),
                'sensitivity' => $item->sensitivity,
                'sensitivity_label' => Str::headline($item->sensitivity),
                'download_url' => $accessToken === null
                    ? route('care-journals.media.download', [
                        'careJournal' => $journal,
                        'careMedia' => $item,
                    ])
                    : route('care-access.media.download', [
                        'token' => $accessToken,
                        'careMedia' => $item,
                    ]),
            ])->all()
            : [];

        return [
            'id' => $entry->id,
            'type' => $entry->type->value,
            'type_label' => $entry->type->label(),
            'icon' => $entry->type->icon(),
            'title' => $entry->title,
            'subtype' => $entry->subtype,
            'started_at' => $this->formatter->dateTime($entry->started_at),
            'started_date' => $entry->started_at?->toDateString(),
            'ended_at' => $this->formatter->time($entry->ended_at),
            'status' => $entry->status->value,
            'status_label' => $entry->status->label(),
            'status_tone' => $entry->status->tone(),
            'source' => $entry->source_type->label(),
            'source_name' => $entry->source_name,
            'source_recorded_at' => $this->formatter->dateTime($entry->source_recorded_at),
            'source_timezone' => $entry->source_timezone,
            'sync_status' => $entry->sync_status->value,
            'sync_label' => $entry->sync_status === CareSyncStatus::Synchronized
                ? __('presentation.synchronized_offline_entry')
                : __('presentation.recorded_online'),
            'verification' => Str::headline($entry->verification_status),
            'author_name' => $entry->author_name,
            'notes' => $entry->notes,
            'measurements' => $this->labeledDetails($entry->measurements ?? []),
            'context' => $this->labeledDetails($context),
            'quantity' => $entry->quantity_value !== null
                ? $this->formatter->number((float) $entry->quantity_value, 3).' '.$entry->quantity_unit
                : null,
            'duration' => $entry->duration_minutes !== null
                ? __('presentation.minutes', ['count' => $this->formatter->number($entry->duration_minutes)])
                : null,
            'distance' => $entry->distance_meters !== null
                ? __('presentation.kilometers', ['count' => $this->formatter->number($entry->distance_meters / 1000, 1)])
                : null,
            'appetite' => $entry->appetite ? Str::headline($entry->appetite) : null,
            'intensity' => $entry->intensity ? Str::headline($entry->intensity) : null,
            'facts' => collect([
                'Amount' => $entry->quantity_value !== null
                    ? $this->formatter->number((float) $entry->quantity_value, 3).' '.$entry->quantity_unit
                    : null,
                'Duration' => $entry->duration_minutes !== null
                    ? __('presentation.minutes', ['count' => $this->formatter->number($entry->duration_minutes)])
                    : null,
                'Distance' => $entry->distance_meters !== null
                    ? __('presentation.kilometers', ['count' => $this->formatter->number($entry->distance_meters / 1000, 1)])
                    : null,
                'Appetite' => $entry->appetite ? Str::headline($entry->appetite) : null,
                'Intensity' => $entry->intensity ? Str::headline($entry->intensity) : null,
            ])->filter()
                ->map(fn (string $value, string $label): array => compact('label', 'value'))
                ->values()
                ->all(),
            'is_unusual' => $entry->is_unusual,
            'media' => $media,
        ];
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<int, array{label: string, value: mixed}>
     */
    private function labeledDetails(array $details): array
    {
        return collect($details)
            ->map(fn (mixed $value, string $label): array => [
                'label' => Str::headline($label),
                'value' => $value,
            ])
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function task(CareTask $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'type' => $task->type->value,
            'type_label' => $task->type->label(),
            'icon' => $task->type->icon(),
            'assignee' => $task->assignee_name ?: __('messages.anyone_available_7913b79c03'),
            'due_at' => $this->formatter->dateTime($task->due_at),
            'is_overdue' => $task->due_at?->isPast() ?? false,
            'priority' => $task->priority->label(),
            'status' => $task->status->value,
            'status_label' => $task->status->label(),
            'instructions' => $task->instructions,
            'requires_confirmation' => $task->requires_individual_confirmation,
            'idempotency_key' => (string) Str::uuid(),
        ];
    }

    /** @return array<string, mixed> */
    private function routine(CareRoutine $routine): array
    {
        return [
            'id' => $routine->id,
            'name' => $routine->name,
            'period' => Str::headline($routine->period),
            'starts_on' => $this->formatter->date($routine->starts_on),
            'ends_on' => $this->formatter->date($routine->ends_on),
            'days' => collect($routine->days ?? [])->map(fn (string $day): string => Str::headline($day))->all(),
            'start_time' => $routine->start_time,
            'status' => $routine->status->label(),
            'version' => $routine->version,
            'instructions' => $routine->instructions,
        ];
    }

    /** @return array<string, mixed> */
    private function grant(CareAccessGrant $grant): array
    {
        return [
            'id' => $grant->id,
            'recipient_name' => $grant->recipient_name,
            'recipient_role' => Str::headline($grant->recipient_role),
            'label' => $grant->label,
            'sections' => collect($grant->sections)->map(fn (string $section): string => Str::headline($section))->all(),
            'allow_add' => $grant->allow_add,
            'allow_location' => $grant->allow_location,
            'allow_media' => $grant->allow_media,
            'views' => $grant->views_used.'/'.$grant->max_views,
            'expires_at' => $this->formatter->dateTime($grant->expires_at),
            'active' => $grant->canBeOpened(),
            'status' => $grant->canBeOpened() ? __('messages.active_9234069589') : __('messages.expired_or_revoked_eebe0c11e5'),
        ];
    }

    /**
     * @param  Collection<int, CareEntry>  $entries
     * @return array<int, array<string, mixed>>
     */
    private function weekly(
        Collection $entries,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): array {
        $days = max(1, min(32, $from->diffInDays($to) + 1));

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($entries, $from): array {
                $date = $from->addDays($offset);
                $dayEntries = $entries->filter(
                    fn (CareEntry $entry): bool => $entry->started_at?->isSameDay($date),
                );

                return [
                    'date' => $this->formatter->weekdayMonthDay($date),
                    'date_short' => $this->formatter->weekdayShort($date),
                    'feeding' => $dayEntries->where('type', CareEntryType::Feeding)->count(),
                    'water' => $dayEntries->where('type', CareEntryType::Water)->count(),
                    'walk_minutes' => (int) $dayEntries
                        ->where('type', CareEntryType::Walk)
                        ->sum('duration_minutes'),
                    'toilet' => $dayEntries->where('type', CareEntryType::Toilet)->count(),
                    'sleep_minutes' => (int) $dayEntries
                        ->where('type', CareEntryType::Sleep)
                        ->sum('duration_minutes'),
                    'activity_minutes' => (int) $dayEntries
                        ->whereIn('type', [CareEntryType::Activity, CareEntryType::Training])
                        ->sum('duration_minutes'),
                    'activity_bar_percent' => min(100, (int) $dayEntries
                        ->whereIn('type', [
                            CareEntryType::Walk,
                            CareEntryType::Activity,
                            CareEntryType::Training,
                        ])
                        ->sum('duration_minutes')),
                    'unusual' => $dayEntries->where('is_unusual', true)->count(),
                    'recorded' => $dayEntries->isNotEmpty(),
                ];
            })
            ->all();
    }

    /** @return array<string, mixed> */
    private function medicationSummary(CareJournal $journal): array
    {
        $medicalRecord = MedicalRecord::query()
            ->select(['id', 'owner_key', 'slug', 'pet_profile_key'])
            ->where('owner_key', $journal->owner_key)
            ->where('pet_profile_key', $journal->pet_profile_key)
            ->first();

        if ($medicalRecord === null) {
            return [
                'record_exists' => false,
                'record_url' => route('medical-records.create'),
                'active' => [],
            ];
        }

        $medications = Medication::query()
            ->forSchedule()
            ->where('medical_record_id', $medicalRecord->id)
            ->where('status', MedicationStatus::Active->value)
            ->with([
                'doses' => fn ($doses) => $doses
                    ->select([
                        'id', 'medical_record_id', 'medication_id',
                        'scheduled_for', 'administered_at', 'status',
                        'dose_given', 'administered_by_name',
                    ])
                    ->latest('scheduled_for')
                    ->limit(1),
            ])
            ->orderBy('next_dose_at')
            ->limit(12)
            ->get()
            ->map(fn (Medication $medication): array => [
                'name' => $medication->name,
                'dose' => $medication->dose,
                'schedule' => $medication->schedule_text,
                'next_dose' => $this->formatter->dateTime($medication->next_dose_at),
                'latest_status' => $medication->doses->first()?->status->label(),
                'latest_by' => $medication->doses->first()?->administered_by_name,
            ])
            ->all();

        return [
            'record_exists' => true,
            'record_url' => route('medical-records.show', $medicalRecord),
            'active' => $medications,
        ];
    }

    /** @return array<int, array<string, string>> */
    private function entryTypes(): array
    {
        return collect(CareEntryType::cases())
            ->map(fn (CareEntryType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
                'icon' => $type->icon(),
            ])
            ->all();
    }

    /** @return array<int, string> */
    private function openTaskStatuses(): array
    {
        return [
            CareTaskStatus::Planned->value,
            CareTaskStatus::DueSoon->value,
            CareTaskStatus::Postponed->value,
            CareTaskStatus::NeedsHelp->value,
        ];
    }

    private function relative(?DateTimeInterface $value): string
    {
        return $this->formatter->relative($value) ?? __('ui.not_recorded_b37c7879f6');
    }
}

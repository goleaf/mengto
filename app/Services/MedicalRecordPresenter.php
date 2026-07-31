<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MedicalEventType;
use App\Enums\MedicalReminderStatus;
use App\Enums\MedicationDoseStatus;
use App\Enums\MedicationStatus;
use App\Enums\VaccinationStatus;
use App\Models\AuditLog;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalDocument;
use App\Models\MedicalEvent;
use App\Models\MedicalRecord;
use App\Models\MedicalReminder;
use App\Models\Medication;
use App\Models\MedicationDose;
use App\Models\Vaccination;
use App\Models\WeightEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MedicalRecordPresenter
{
    public function __construct(
        private readonly ProfilePresenter $profiles,
        private readonly ForumActor $actor,
        private readonly QrCodeGenerator $qrCodes,
        private readonly LocaleFormatter $formatter,
    ) {}

    /** @return array<string, mixed> */
    public function directory(): array
    {
        $records = MedicalRecord::query()
            ->forOwnerDirectory($this->actor->key())
            ->withCount([
                'medications as active_medications_count' => fn (Builder $query): Builder => $query
                    ->where('status', MedicationStatus::Active->value),
                'reminders as upcoming_reminders_count' => fn (Builder $query): Builder => $query
                    ->whereIn('status', [
                        MedicalReminderStatus::Scheduled->value,
                        MedicalReminderStatus::Snoozed->value,
                    ])
                    ->where('due_at', '>=', now()->subDay()),
                'documents',
            ])
            ->latest('updated_at')
            ->simplePaginate(9);

        $records->through(fn (MedicalRecord $record): array => $this->recordCard($record));

        return [
            ...$this->page(__('messages.pet_health_records_911c3e19be'), 'health'),
            'records' => $records,
        ];
    }

    /** @return array<string, mixed> */
    public function editor(): array
    {
        $existing = MedicalRecord::query()
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
            ...$this->page(__('messages.create_a_private_health_record_78b6de7a5e'), 'health'),
            'pet_options' => $options,
            'timezone' => 'Europe/Vilnius',
        ];
    }

    /** @return array<string, mixed> */
    public function show(MedicalRecord $record, bool $manage = false): array
    {
        $events = MedicalEvent::query()
            ->forTimeline()
            ->where('medical_record_id', $record->id)
            ->latest('occurred_at')
            ->limit(24)
            ->get();

        $vaccinations = Vaccination::query()
            ->forOverview()
            ->where('medical_record_id', $record->id)
            ->latest('administered_on')
            ->latest('id')
            ->limit(12)
            ->get();

        $weights = WeightEntry::query()
            ->forChart()
            ->where('medical_record_id', $record->id)
            ->latest('measured_at')
            ->limit(18)
            ->get()
            ->sortBy('measured_at')
            ->values();

        $medications = Medication::query()
            ->forSchedule()
            ->where('medical_record_id', $record->id)
            ->with([
                'doses' => fn ($doses) => $doses
                    ->select([
                        'id', 'medical_record_id', 'medication_id',
                        'scheduled_for', 'administered_at', 'status',
                        'dose_given', 'administered_by_name', 'notes',
                    ])
                    ->latest('scheduled_for')
                    ->limit(4),
            ])
            ->orderBy('status')
            ->orderBy('next_dose_at')
            ->limit(16)
            ->get();

        $reminders = MedicalReminder::query()
            ->select([
                'id', 'medical_record_id', 'type', 'title', 'due_at',
                'timezone', 'priority', 'status', 'recipients', 'instructions',
                'related_type', 'related_id', 'confirmed_at',
            ])
            ->where('medical_record_id', $record->id)
            ->upcoming()
            ->orderBy('due_at')
            ->limit(12)
            ->get();

        $documents = MedicalDocument::query()
            ->select([
                'id', 'medical_record_id', 'type', 'title', 'original_name',
                'mime_type', 'size_bytes', 'source_type', 'source_name',
                'verification_status', 'expires_on', 'download_count',
                'created_at',
            ])
            ->where('medical_record_id', $record->id)
            ->latest('created_at')
            ->limit(16)
            ->get();

        $grants = MedicalAccessGrant::query()
            ->select([
                'id', 'medical_record_id', 'recipient_key', 'recipient_name',
                'recipient_role', 'label', 'sections', 'permissions',
                'allow_download', 'allow_edit', 'max_views', 'views_used',
                'expires_at', 'last_opened_at', 'revoked_at', 'created_at',
            ])
            ->where('medical_record_id', $record->id)
            ->latest('created_at')
            ->limit(16)
            ->get();

        $grantIds = $grants->pluck('id')->map(fn (int $id): string => (string) $id);
        $accessLogs = $grantIds->isEmpty()
            ? collect()
            : AuditLog::query()
                ->select([
                    'id', 'actor_key', 'actor_role', 'action', 'target_type',
                    'target_id', 'metadata', 'created_at',
                ])
                ->where('target_type', MedicalAccessGrant::class)
                ->whereIn('target_id', $grantIds)
                ->latest('created_at')
                ->limit(16)
                ->get();

        return [
            ...$this->page(
                ($manage ? 'Manage ' : '').$record->pet_name."'s health record",
                'health',
            ),
            'medical_record' => $this->recordDetail($record),
            'events' => $events->map(fn (MedicalEvent $event): array => $this->event($event))->all(),
            'vaccinations' => $vaccinations
                ->map(fn (Vaccination $vaccination): array => $this->vaccination($vaccination))
                ->all(),
            'weights' => $weights->map(fn (WeightEntry $weight): array => $this->weight($weight, $record))->all(),
            'weight_chart' => $this->weightChart($weights, $record),
            'medications' => $medications
                ->map(fn (Medication $medication): array => $this->medication($medication))
                ->all(),
            'reminders' => $reminders
                ->map(fn (MedicalReminder $reminder): array => $this->reminder($reminder))
                ->all(),
            'documents' => $documents
                ->map(fn (MedicalDocument $document): array => $this->document($document, $record))
                ->all(),
            'access_grants' => $grants
                ->map(fn (MedicalAccessGrant $grant): array => $this->grant($grant, $record))
                ->all(),
            'access_logs' => $accessLogs->map(fn (AuditLog $log): array => [
                'action' => Str::headline(str($log->action)->after('medical-access.')->toString()),
                'actor' => $log->actor_key,
                'role' => Str::headline($log->actor_role),
                'time' => $this->formatter->dateTime($log->created_at),
                'sections' => $log->metadata['sections'] ?? [],
            ])->all(),
            'entry_options' => $manage ? $this->entryOptions() : [],
            'medical_access_url' => session('medical_access_url'),
        ];
    }

    /** @return array<string, mixed> */
    public function emergency(MedicalRecord $record): array
    {
        $url = route('medical-records.emergency', $record);
        $medications = Medication::query()
            ->forSchedule()
            ->where('medical_record_id', $record->id)
            ->active()
            ->orderBy('next_dose_at')
            ->limit(12)
            ->get();

        return [
            ...$this->page(__('presentation.emergency_card_for', ['pet' => $record->pet_name]), 'health'),
            'medical_record' => $this->emergencyCard($record),
            'medications' => $medications
                ->map(fn (Medication $medication): array => $this->medication($medication))
                ->all(),
            'qr_code' => $this->qrCodes->dataUri($url),
            'updated_label' => $this->formatter->dateTime($record->updated_at),
        ];
    }

    /** @return array<string, mixed> */
    public function shared(MedicalAccessGrant $grant, string $token): array
    {
        $record = $grant->medicalRecord;
        $sections = $grant->sections ?? [];
        $data = [
            ...$this->page(__('presentation.shared_health_record_for', ['pet' => $record->pet_name]), 'health'),
            'grant' => [
                'label' => $grant->label,
                'recipient_name' => $grant->recipient_name,
                'recipient_role' => Str::headline($grant->recipient_role),
                'sections' => $sections,
                'expires_at' => $this->formatter->dateTime($grant->expires_at),
                'views_remaining' => $this->formatter->number(max(0, $grant->max_views - $grant->views_used)),
                'allow_download' => $grant->allow_download,
            ],
            'medical_record' => $this->sharedSummary($record, $sections),
            'events' => [],
            'medications' => [],
            'vaccinations' => [],
            'weights' => [],
            'documents' => [],
            'reminders' => [],
        ];

        if (in_array('timeline', $sections, true)) {
            $data['events'] = MedicalEvent::query()
                ->forTimeline()
                ->where('medical_record_id', $record->id)
                ->latest('occurred_at')
                ->limit(16)
                ->get()
                ->map(fn (MedicalEvent $event): array => $this->event($event))
                ->all();
        }

        if (array_intersect(['medications', 'emergency'], $sections) !== []) {
            $data['medications'] = Medication::query()
                ->forSchedule()
                ->where('medical_record_id', $record->id)
                ->active()
                ->orderBy('next_dose_at')
                ->limit(12)
                ->get()
                ->map(fn (Medication $medication): array => $this->medication($medication))
                ->all();
        }

        if (in_array('vaccinations', $sections, true)) {
            $data['vaccinations'] = Vaccination::query()
                ->forOverview()
                ->where('medical_record_id', $record->id)
                ->latest('administered_on')
                ->limit(12)
                ->get()
                ->map(fn (Vaccination $vaccination): array => $this->vaccination($vaccination))
                ->all();
        }

        if (in_array('weight', $sections, true)) {
            $data['weights'] = WeightEntry::query()
                ->forChart()
                ->where('medical_record_id', $record->id)
                ->latest('measured_at')
                ->limit(12)
                ->get()
                ->map(fn (WeightEntry $weight): array => $this->weight($weight, $record))
                ->all();
        }

        if (in_array('documents', $sections, true)) {
            $data['documents'] = MedicalDocument::query()
                ->select([
                    'id', 'medical_record_id', 'type', 'title', 'original_name',
                    'mime_type', 'size_bytes', 'source_type', 'source_name',
                    'verification_status', 'expires_on', 'created_at',
                ])
                ->where('medical_record_id', $record->id)
                ->latest('created_at')
                ->limit(16)
                ->get()
                ->map(fn (MedicalDocument $document): array => [
                    ...$this->document($document, $record),
                    'download_url' => $grant->allow_download
                        ? route('medical-access.documents.download', [
                            'token' => $token,
                            'medicalDocument' => $document,
                        ])
                        : null,
                ])
                ->all();
        }

        if (in_array('reminders', $sections, true)) {
            $data['reminders'] = MedicalReminder::query()
                ->select([
                    'id', 'medical_record_id', 'type', 'title', 'due_at',
                    'timezone', 'priority', 'status', 'instructions',
                ])
                ->where('medical_record_id', $record->id)
                ->upcoming()
                ->orderBy('due_at')
                ->limit(12)
                ->get()
                ->map(fn (MedicalReminder $reminder): array => $this->reminder($reminder))
                ->all();
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function page(string $title, string $section): array
    {
        return [
            'owner' => $this->profiles->owner(),
            'page_title' => __('presentation.brand_title', ['title' => $title]),
            'active_section' => $section,
        ];
    }

    /** @return array<string, mixed> */
    private function recordCard(MedicalRecord $record): array
    {
        return [
            'slug' => $record->slug,
            'pet_name' => $record->pet_name,
            'species' => Str::headline($record->species),
            'breed' => $record->breed,
            'image_url' => $record->image_url,
            'current_weight' => $this->weightLabel($record->current_weight_grams, $record->species),
            'last_visit' => $this->formatter->date($record->last_visit_at)
                ?? __('presentation.no_visit_recorded'),
            'next_appointment' => $this->formatter->dateTime($record->next_appointment_at),
            'active_medications_count' => (int) ($record->active_medications_count ?? 0),
            'upcoming_reminders_count' => (int) ($record->upcoming_reminders_count ?? 0),
            'documents_count' => (int) ($record->documents_count ?? 0),
            'updated_label' => $this->formatter->relative($record->updated_at),
        ];
    }

    /** @return array<string, mixed> */
    private function recordDetail(MedicalRecord $record): array
    {
        return [
            ...$this->recordCard($record),
            'pet_profile_key' => $record->pet_profile_key,
            'birth_date' => $this->formatter->date($record->birth_date),
            'birth_date_estimated' => $record->birth_date_estimated,
            'age' => $record->birth_date
                ? trans_choice('presentation.years_count', $record->birth_date->age, [
                    'count' => $this->formatter->number($record->birth_date->age),
                ])
                : __('presentation.unknown'),
            'sex' => $record->sex ? Str::headline($record->sex) : __('presentation.unknown'),
            'reproductive_status' => Str::headline($record->reproductive_status),
            'privacy' => Str::headline($record->privacy),
            'microchip_status' => Str::headline($record->microchip_status),
            'microchip_masked' => $record->maskedMicrochip(),
            'blood_group' => $record->blood_group,
            'critical_allergies' => $record->critical_allergies ?? [],
            'chronic_conditions' => $record->chronic_conditions ?? [],
            'emergency_notes' => $record->emergency_notes,
            'primary_clinic_name' => $record->primary_clinic_name,
            'primary_clinic_contact' => $record->primary_clinic_contact,
            'emergency_contact' => $record->emergency_contact ?? [],
            'timezone' => $record->timezone,
            'emergency_url' => route('medical-records.emergency', $record),
            'manage_url' => route('medical-records.manage', $record),
        ];
    }

    /** @return array<string, mixed> */
    private function emergencyCard(MedicalRecord $record): array
    {
        return [
            'slug' => $record->slug,
            'pet_name' => $record->pet_name,
            'species' => Str::headline($record->species),
            'breed' => $record->breed,
            'age' => $record->birth_date
                ? trans_choice('presentation.years_count', $record->birth_date->age, [
                    'count' => $this->formatter->number($record->birth_date->age),
                ])
                : __('presentation.unknown'),
            'current_weight' => $this->weightLabel($record->current_weight_grams, $record->species),
            'image_url' => $record->image_url,
            'critical_allergies' => $record->critical_allergies ?? [],
            'chronic_conditions' => $record->chronic_conditions ?? [],
            'emergency_notes' => $record->emergency_notes,
            'blood_group' => $record->blood_group,
            'microchip_status' => Str::headline($record->microchip_status),
            'primary_clinic_name' => $record->primary_clinic_name,
            'primary_clinic_contact' => $record->primary_clinic_contact,
            'emergency_contact' => $record->emergency_contact ?? [],
        ];
    }

    /** @param array<int, string> $sections @return array<string, mixed> */
    private function sharedSummary(MedicalRecord $record, array $sections): array
    {
        $summary = [
            'pet_name' => $record->pet_name,
            'species' => Str::headline($record->species),
            'breed' => $record->breed,
            'image_url' => $record->image_url,
        ];

        if (array_intersect(['summary', 'emergency'], $sections) !== []) {
            $summary['current_weight'] = $this->weightLabel($record->current_weight_grams, $record->species);
            $summary['microchip_status'] = Str::headline($record->microchip_status);
            $summary['microchip_masked'] = $record->maskedMicrochip();
            $summary['critical_allergies'] = $record->critical_allergies ?? [];
            $summary['chronic_conditions'] = $record->chronic_conditions ?? [];
            $summary['blood_group'] = $record->blood_group;
            $summary['primary_clinic_name'] = $record->primary_clinic_name;
        }

        if (in_array('emergency', $sections, true)) {
            $summary['emergency_notes'] = $record->emergency_notes;
            $summary['primary_clinic_contact'] = $record->primary_clinic_contact;
            $summary['emergency_contact'] = $record->emergency_contact ?? [];
        }

        if (in_array('weight', $sections, true) && ! isset($summary['current_weight'])) {
            $summary['current_weight'] = $this->weightLabel($record->current_weight_grams, $record->species);
        }

        return $summary;
    }

    /** @return array<string, mixed> */
    private function event(MedicalEvent $event): array
    {
        return [
            'id' => $event->id,
            'type' => $event->type->value,
            'type_label' => $event->type->label(),
            'icon' => $event->type->icon(),
            'title' => $event->title,
            'occurred_at' => $this->formatter->dateTime($event->occurred_at),
            'status' => Str::headline($event->status),
            'source' => $event->source_type->label(),
            'source_name' => $event->source_name,
            'verification' => $event->verification_status->label(),
            'verification_tone' => $event->verification_status->tone(),
            'summary' => $event->summary,
            'details' => $event->details ?? [],
            'follow_up' => $this->formatter->dateTime($event->follow_up_at),
            'is_critical' => $event->is_critical,
        ];
    }

    /** @return array<string, mixed> */
    private function vaccination(Vaccination $vaccination): array
    {
        $status = $vaccination->status;

        if ($vaccination->next_due_on?->isPast()
            && ! in_array($status, [
                VaccinationStatus::MedicalExemption,
                VaccinationStatus::Deferred,
            ], true)) {
            $status = VaccinationStatus::Overdue;
        } elseif ($vaccination->next_due_on?->between(now(), now()->addMonth())) {
            $status = VaccinationStatus::DueSoon;
        }

        return [
            'id' => $vaccination->id,
            'name' => $vaccination->name,
            'manufacturer' => $vaccination->manufacturer,
            'lot_number' => $vaccination->lot_number,
            'administered_on' => $this->formatter->date($vaccination->administered_on),
            'next_due_on' => $this->formatter->date($vaccination->next_due_on),
            'status' => $status->value,
            'status_label' => $status->label(),
            'clinic_name' => $vaccination->clinic_name,
            'veterinarian_name' => $vaccination->veterinarian_name,
            'reaction' => $vaccination->reaction,
            'verification' => $vaccination->verification_status->label(),
        ];
    }

    /** @return array<string, mixed> */
    private function weight(WeightEntry $weight, MedicalRecord $record): array
    {
        return [
            'id' => $weight->id,
            'measured_at' => $this->formatter->dateTime($weight->measured_at),
            'weight' => $this->weightLabel($weight->weight_grams, $record->species),
            'weight_grams' => $weight->weight_grams,
            'source' => $weight->source_type->label(),
            'source_name' => $weight->source_name,
            'context' => $weight->measurement_context,
            'notes' => $weight->notes,
            'verification' => $weight->verification_status->label(),
        ];
    }

    /** @return array<string, mixed> */
    private function medication(Medication $medication): array
    {
        $latestDose = $medication->relationLoaded('doses')
            ? $medication->doses->first()
            : null;

        return [
            'id' => $medication->id,
            'name' => $medication->name,
            'active_ingredient' => $medication->active_ingredient,
            'form' => Str::headline($medication->form),
            'concentration' => $medication->concentration,
            'dose' => $medication->dose,
            'route' => $medication->route,
            'schedule' => $medication->schedule_text,
            'starts_on' => $this->formatter->date($medication->starts_on),
            'ends_on' => $this->formatter->date($medication->ends_on),
            'next_dose_at' => $this->formatter->dateTime($medication->next_dose_at),
            'next_dose_value' => $medication->next_dose_at?->format('Y-m-d H:i:s'),
            'status' => $medication->status->value,
            'status_label' => $medication->status->label(),
            'reason' => $medication->reason,
            'prescribed_by' => $medication->prescribed_by_name,
            'clinic_name' => $medication->clinic_name,
            'instructions' => $medication->instructions,
            'is_high_risk' => $medication->is_high_risk,
            'remaining' => $medication->remaining_quantity !== null
                ? __('presentation.measurement', [
                    'value' => $this->formatter->number((float) $medication->remaining_quantity, 4),
                    'unit' => $medication->remaining_unit,
                ])
                : null,
            'verification' => $medication->verification_status->label(),
            'can_record_dose' => $medication->status->isDoseable(),
            'dose_idempotency_key' => (string) Str::uuid(),
            'latest_dose' => $latestDose instanceof MedicationDose ? [
                'status' => $latestDose->status->label(),
                'prevents_repeat' => $latestDose->status->preventsRepeat(),
                'scheduled_for' => $this->formatter->dateTime($latestDose->scheduled_for),
                'administered_at' => $this->formatter->dateTime($latestDose->administered_at),
                'administered_by' => $latestDose->administered_by_name,
            ] : null,
        ];
    }

    /** @return array<string, mixed> */
    private function reminder(MedicalReminder $reminder): array
    {
        return [
            'id' => $reminder->id,
            'type' => Str::headline($reminder->type),
            'title' => $reminder->title,
            'due_at' => $this->formatter->dateTime($reminder->due_at),
            'due_relative' => $this->formatter->relative($reminder->due_at),
            'priority' => Str::headline($reminder->priority),
            'status' => $reminder->status->value,
            'status_label' => $reminder->status->label(),
            'instructions' => $reminder->instructions,
        ];
    }

    /** @return array<string, mixed> */
    private function document(MedicalDocument $document, MedicalRecord $record): array
    {
        return [
            'id' => $document->id,
            'type' => Str::headline($document->type),
            'title' => $document->title,
            'original_name' => $document->original_name,
            'mime_type' => $document->mime_type,
            'size' => __('presentation.kilobytes', [
                'count' => $this->formatter->number($document->size_bytes / 1024, 1),
            ]),
            'source' => $document->source_type->label(),
            'source_name' => $document->source_name,
            'verification' => $document->verification_status->label(),
            'expires_on' => $this->formatter->date($document->expires_on),
            'created_at' => $this->formatter->date($document->created_at),
            'download_url' => route('medical-records.documents.download', [
                'medicalRecord' => $record,
                'document' => $document,
            ]),
        ];
    }

    /** @return array<string, mixed> */
    private function grant(MedicalAccessGrant $grant, MedicalRecord $record): array
    {
        $active = $grant->canBeOpened();

        return [
            'id' => $grant->id,
            'recipient_name' => $grant->recipient_name,
            'recipient_role' => Str::headline($grant->recipient_role),
            'label' => $grant->label,
            'sections' => collect($grant->sections)->map(fn (string $section): string => Str::headline($section))->all(),
            'allow_download' => $grant->allow_download,
            'allow_edit' => $grant->allow_edit,
            'views' => __('presentation.value_of_total', [
                'value' => $this->formatter->number($grant->views_used),
                'total' => $this->formatter->number($grant->max_views),
            ]),
            'expires_at' => $this->formatter->dateTime($grant->expires_at),
            'last_opened_at' => $this->formatter->dateTime($grant->last_opened_at),
            'active' => $active,
            'status' => $grant->revoked_at !== null
                ? __('presentation.revoked')
                : ($grant->expires_at?->isPast()
                    ? __('presentation.expired')
                    : ($active ? __('presentation.active') : __('presentation.view_limit_reached'))),
            'revoke_url' => route('medical-records.access.revoke', [
                'medicalRecord' => $record,
                'medicalAccessGrant' => $grant,
            ]),
        ];
    }

    /**
     * @param  Collection<int, WeightEntry>  $weights
     * @return array<string, mixed>
     */
    private function weightChart(Collection $weights, MedicalRecord $record): array
    {
        if ($weights->isEmpty()) {
            return [
                'has_data' => false,
                'path' => '',
                'points' => [],
                'minimum' => null,
                'maximum' => null,
                'trend' => __('presentation.not_enough_data'),
            ];
        }

        $minimum = (int) $weights->min('weight_grams');
        $maximum = (int) $weights->max('weight_grams');
        $range = max(1, $maximum - $minimum);
        $count = max(1, $weights->count() - 1);
        $points = $weights->values()->map(function (WeightEntry $weight, int $index) use ($count, $minimum, $range, $record): array {
            $x = 24 + (($index / $count) * 552);
            $y = 132 - ((($weight->weight_grams - $minimum) / $range) * 104);

            return [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'label' => $this->weightLabel($weight->weight_grams, $record->species),
                'date' => $this->formatter->monthDay($weight->measured_at),
            ];
        });

        $first = $weights->first()->weight_grams;
        $last = $weights->last()->weight_grams;
        $delta = $last - $first;

        return [
            'has_data' => true,
            'path' => $points->map(fn (array $point): string => $point['x'].','.$point['y'])->join(' '),
            'points' => $points->all(),
            'minimum' => $this->weightLabel($minimum, $record->species),
            'maximum' => $this->weightLabel($maximum, $record->species),
            'trend' => match (true) {
                abs($delta) < 50 => __('presentation.stable_across_period'),
                $delta > 0 => __('presentation.trend_up', [
                    'value' => $this->weightLabel(abs($delta), 'small'),
                ]),
                default => __('presentation.trend_down', [
                    'value' => $this->weightLabel(abs($delta), 'small'),
                ]),
            },
        ];
    }

    /** @return array<string, mixed> */
    private function entryOptions(): array
    {
        return [
            'event_types' => collect(MedicalEventType::cases())
                ->mapWithKeys(fn (MedicalEventType $type): array => [$type->value => $type->label()])
                ->all(),
            'vaccination_statuses' => collect(VaccinationStatus::cases())
                ->mapWithKeys(fn (VaccinationStatus $status): array => [$status->value => $status->label()])
                ->all(),
            'medication_statuses' => collect(MedicationStatus::cases())
                ->mapWithKeys(fn (MedicationStatus $status): array => [$status->value => $status->label()])
                ->all(),
            'dose_statuses' => collect(MedicationDoseStatus::cases())
                ->mapWithKeys(fn (MedicationDoseStatus $status): array => [$status->value => $status->label()])
                ->all(),
            'share_sections' => [
                'summary' => __('messages.health_summary_0550871563'),
                'emergency' => __('messages.emergency_instructions_136f946766'),
                'timeline' => __('messages.medical_timeline_81bce0a0b8'),
                'medications' => __('messages.active_medications_2a3bd50cbe'),
                'vaccinations' => __('messages.vaccinations_ed3861e631'),
                'weight' => __('messages.weight_history_a1ea27c673'),
                'documents' => __('messages.documents_b4e929d8bc'),
                'reminders' => __('messages.upcoming_reminders_2ca835d9e9'),
            ],
        ];
    }

    private function weightLabel(?int $grams, string $species): string
    {
        if ($grams === null) {
            return __('presentation.not_recorded');
        }

        if ($grams < 1000 || in_array($species, ['bird', 'rodent', 'small'], true)) {
            return __('presentation.grams', [
                'count' => $this->formatter->number($grams),
            ]);
        }

        return __('presentation.kilograms', [
            'count' => $this->formatter->number($grams / 1000, 2),
        ]);
    }
}

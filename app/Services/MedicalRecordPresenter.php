<?php

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
            ...$this->page('Pet health records', 'health'),
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
            ...$this->page('Create a private health record', 'health'),
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
                'time' => $log->created_at?->format('M j · H:i'),
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
            ...$this->page('Emergency card · '.$record->pet_name, 'health'),
            'medical_record' => $this->emergencyCard($record),
            'medications' => $medications
                ->map(fn (Medication $medication): array => $this->medication($medication))
                ->all(),
            'qr_code' => $this->qrCodes->dataUri($url),
            'updated_label' => $record->updated_at?->format('M j, Y · H:i'),
        ];
    }

    /** @return array<string, mixed> */
    public function shared(MedicalAccessGrant $grant, string $token): array
    {
        $record = $grant->medicalRecord;
        $sections = $grant->sections ?? [];
        $data = [
            ...$this->page($record->pet_name.' · Shared health record', 'health'),
            'grant' => [
                'label' => $grant->label,
                'recipient_name' => $grant->recipient_name,
                'recipient_role' => Str::headline($grant->recipient_role),
                'sections' => $sections,
                'expires_at' => $grant->expires_at?->format('M j, Y · H:i'),
                'views_remaining' => max(0, $grant->max_views - $grant->views_used),
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
            'page_title' => $title.' | PawCircle',
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
            'last_visit' => $record->last_visit_at?->format('M j, Y') ?? 'No visit recorded',
            'next_appointment' => $record->next_appointment_at?->format('M j · H:i'),
            'active_medications_count' => (int) ($record->active_medications_count ?? 0),
            'upcoming_reminders_count' => (int) ($record->upcoming_reminders_count ?? 0),
            'documents_count' => (int) ($record->documents_count ?? 0),
            'updated_label' => $record->updated_at?->diffForHumans(),
        ];
    }

    /** @return array<string, mixed> */
    private function recordDetail(MedicalRecord $record): array
    {
        return [
            ...$this->recordCard($record),
            'pet_profile_key' => $record->pet_profile_key,
            'birth_date' => $record->birth_date?->format('M j, Y'),
            'birth_date_estimated' => $record->birth_date_estimated,
            'age' => $record->birth_date ? $record->birth_date->age.' years' : 'Unknown',
            'sex' => $record->sex ? Str::headline($record->sex) : 'Unknown',
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
            'age' => $record->birth_date ? $record->birth_date->age.' years' : 'Unknown',
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
            'occurred_at' => $event->occurred_at?->format('M j, Y · H:i'),
            'status' => Str::headline($event->status),
            'source' => $event->source_type->label(),
            'source_name' => $event->source_name,
            'verification' => $event->verification_status->label(),
            'verification_tone' => $event->verification_status->tone(),
            'summary' => $event->summary,
            'details' => $event->details ?? [],
            'follow_up' => $event->follow_up_at?->format('M j, Y · H:i'),
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
            'administered_on' => $vaccination->administered_on?->format('M j, Y'),
            'next_due_on' => $vaccination->next_due_on?->format('M j, Y'),
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
            'measured_at' => $weight->measured_at?->format('M j, Y · H:i'),
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
            'starts_on' => $medication->starts_on?->format('M j, Y'),
            'ends_on' => $medication->ends_on?->format('M j, Y'),
            'next_dose_at' => $medication->next_dose_at?->format('M j · H:i'),
            'next_dose_value' => $medication->next_dose_at?->format('Y-m-d H:i:s'),
            'status' => $medication->status->value,
            'status_label' => $medication->status->label(),
            'reason' => $medication->reason,
            'prescribed_by' => $medication->prescribed_by_name,
            'clinic_name' => $medication->clinic_name,
            'instructions' => $medication->instructions,
            'is_high_risk' => $medication->is_high_risk,
            'remaining' => $medication->remaining_quantity !== null
                ? rtrim(rtrim($medication->remaining_quantity, '0'), '.').' '.$medication->remaining_unit
                : null,
            'verification' => $medication->verification_status->label(),
            'can_record_dose' => $medication->status->isDoseable(),
            'dose_idempotency_key' => (string) Str::uuid(),
            'latest_dose' => $latestDose instanceof MedicationDose ? [
                'status' => $latestDose->status->label(),
                'prevents_repeat' => $latestDose->status->preventsRepeat(),
                'scheduled_for' => $latestDose->scheduled_for?->format('M j · H:i'),
                'administered_at' => $latestDose->administered_at?->format('M j · H:i'),
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
            'due_at' => $reminder->due_at?->format('M j, Y · H:i'),
            'due_relative' => $reminder->due_at?->diffForHumans(),
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
            'size' => number_format($document->size_bytes / 1024, 1).' KB',
            'source' => $document->source_type->label(),
            'source_name' => $document->source_name,
            'verification' => $document->verification_status->label(),
            'expires_on' => $document->expires_on?->format('M j, Y'),
            'created_at' => $document->created_at?->format('M j, Y'),
            'download_url' => route('medical-records.documents.download', [
                'medicalRecord' => $record,
                'medicalDocument' => $document,
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
            'views' => $grant->views_used.' / '.$grant->max_views,
            'expires_at' => $grant->expires_at?->format('M j, Y · H:i'),
            'last_opened_at' => $grant->last_opened_at?->format('M j · H:i'),
            'active' => $active,
            'status' => $grant->revoked_at !== null
                ? 'Revoked'
                : ($grant->expires_at?->isPast() ? 'Expired' : ($active ? 'Active' : 'View limit reached')),
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
                'trend' => 'Not enough data',
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
                'date' => $weight->measured_at?->format('M j'),
            ];
        });

        $first = $weights->first()?->weight_grams;
        $last = $weights->last()?->weight_grams;
        $delta = ($first !== null && $last !== null) ? $last - $first : 0;

        return [
            'has_data' => true,
            'path' => $points->map(fn (array $point): string => $point['x'].','.$point['y'])->join(' '),
            'points' => $points->all(),
            'minimum' => $this->weightLabel($minimum, $record->species),
            'maximum' => $this->weightLabel($maximum, $record->species),
            'trend' => match (true) {
                abs($delta) < 50 => 'Stable across this period',
                $delta > 0 => 'Up '.$this->weightLabel(abs($delta), 'small'),
                default => 'Down '.$this->weightLabel(abs($delta), 'small'),
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
                'summary' => 'Health summary',
                'emergency' => 'Emergency instructions',
                'timeline' => 'Medical timeline',
                'medications' => 'Active medications',
                'vaccinations' => 'Vaccinations',
                'weight' => 'Weight history',
                'documents' => 'Documents',
                'reminders' => 'Upcoming reminders',
            ],
        ];
    }

    private function weightLabel(?int $grams, string $species): string
    {
        if ($grams === null) {
            return 'Not recorded';
        }

        if ($grams < 1000 || in_array($species, ['bird', 'rodent', 'small'], true)) {
            return number_format($grams).' g';
        }

        return number_format($grams / 1000, 2).' kg';
    }
}

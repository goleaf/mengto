<?php

namespace App\Actions;

use App\Enums\MedicalEventType;
use App\Enums\MedicalReminderStatus;
use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use App\Enums\MedicationStatus;
use App\Enums\VaccinationStatus;
use App\Models\AuditLog;
use App\Models\MedicalRecord;
use App\Services\ForumActor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateMedicalEntry
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(MedicalRecord $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Model {
            $entry = match ($data['entry_type']) {
                'weight' => $this->weight($record, $data),
                'vaccination' => $this->vaccination($record, $data),
                'medication' => $this->medication($record, $data),
                'reminder' => $this->reminder($record, $data),
                default => $this->event($record, $data),
            };

            $record->increment('lock_version');

            AuditLog::query()->create([
                'actor_key' => $this->actor->key(),
                'actor_role' => 'medical-record-owner',
                'action' => 'medical-entry.created',
                'target_type' => $entry::class,
                'target_id' => (string) $entry->getKey(),
                'metadata' => [
                    'medical_record_id' => $record->id,
                    'entry_type' => $data['entry_type'],
                    'source_type' => $data['source_type'] ?? 'owner',
                ],
            ]);

            return $entry;
        });
    }

    /** @param array<string, mixed> $data */
    private function event(MedicalRecord $record, array $data): Model
    {
        $source = MedicalSourceType::from($data['source_type'] ?? 'owner');
        $verification = $source === MedicalSourceType::Owner
            ? MedicalVerificationStatus::OwnerReported
            : MedicalVerificationStatus::NeedsReview;

        $event = $record->events()->create([
            'type' => MedicalEventType::from($data['event_type']),
            'title' => $data['title'],
            'occurred_at' => $data['occurred_at'],
            'timezone' => $record->timezone,
            'status' => $data['event_status'] ?? 'active',
            'source_type' => $source,
            'source_name' => $data['source_name'] ?: $this->actor->identity()['name'],
            'source_reference' => $data['source_reference'] ?? null,
            'verification_status' => $verification,
            'summary' => $data['summary'] ?? null,
            'details' => [
                'severity' => $data['severity'] ?? null,
                'next_step' => $data['next_step'] ?? null,
            ],
            'created_by_key' => $this->actor->key(),
            'created_by_name' => $this->actor->identity()['name'],
            'follow_up_at' => $data['follow_up_at'] ?? null,
            'is_critical' => (bool) ($data['is_critical'] ?? false),
        ]);

        if ($event->type === MedicalEventType::Visit) {
            $record->update(['last_visit_at' => $event->occurred_at]);
        }

        return $event;
    }

    /** @param array<string, mixed> $data */
    private function weight(MedicalRecord $record, array $data): Model
    {
        $grams = $this->grams((float) $data['weight'], $data['weight_unit']);
        $entry = $record->weightEntries()->create([
            'measured_at' => $data['measured_at'],
            'timezone' => $record->timezone,
            'weight_grams' => $grams,
            'tare_grams' => filled($data['tare'] ?? null)
                ? $this->grams((float) $data['tare'], $data['weight_unit'])
                : null,
            'source_type' => $data['source_type'] ?? 'owner',
            'source_name' => $data['source_name'] ?: __('messages.medical.home_scale'),
            'measurement_context' => $data['measurement_context'] ?? null,
            'notes' => $data['notes'] ?? null,
            'verification_status' => ($data['source_type'] ?? 'owner') === 'owner'
                ? 'owner-reported'
                : 'needs-review',
            'created_by_key' => $this->actor->key(),
        ]);

        $record->update(['current_weight_grams' => $grams]);

        return $entry;
    }

    /** @param array<string, mixed> $data */
    private function vaccination(MedicalRecord $record, array $data): Model
    {
        $source = $data['source_type'] ?? 'owner';

        return $record->vaccinations()->create([
            'name' => $data['title'],
            'manufacturer' => $data['manufacturer'] ?? null,
            'lot_number' => $data['lot_number'] ?? null,
            'product_expires_on' => $data['product_expires_on'] ?? null,
            'administered_on' => $data['administered_on'] ?? null,
            'next_due_on' => $data['next_due_on'] ?? null,
            'status' => VaccinationStatus::from($data['vaccination_status']),
            'dose' => $data['dose'] ?? null,
            'route' => $data['route'] ?? null,
            'clinic_name' => $data['source_name'] ?? null,
            'veterinarian_name' => $data['professional_name'] ?? null,
            'reaction' => $data['reaction'] ?? null,
            'verification_status' => $source === 'owner'
                ? MedicalVerificationStatus::OwnerReported
                : MedicalVerificationStatus::NeedsReview,
            'created_by_key' => $this->actor->key(),
        ]);
    }

    /** @param array<string, mixed> $data */
    private function medication(MedicalRecord $record, array $data): Model
    {
        return $record->medications()->create([
            'name' => $data['title'],
            'active_ingredient' => $data['active_ingredient'] ?? null,
            'form' => $data['medication_form'],
            'concentration' => $data['concentration'] ?? null,
            'dose' => $data['dose'],
            'route' => $data['route'],
            'schedule_type' => $data['schedule_type'],
            'schedule_text' => $data['schedule_text'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'] ?? null,
            'next_dose_at' => $data['next_dose_at'] ?? null,
            'timezone' => $record->timezone,
            'status' => MedicationStatus::from($data['medication_status']),
            'reason' => $data['reason'] ?? null,
            'prescribed_by_name' => $data['professional_name'] ?? null,
            'clinic_name' => $data['source_name'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'is_high_risk' => (bool) ($data['is_high_risk'] ?? false),
            'remaining_quantity' => $data['remaining_quantity'] ?? null,
            'remaining_unit' => $data['remaining_unit'] ?? null,
            'expires_on' => $data['expires_on'] ?? null,
            'verification_status' => ($data['source_type'] ?? 'owner') === 'owner'
                ? MedicalVerificationStatus::OwnerReported
                : MedicalVerificationStatus::NeedsReview,
            'created_by_key' => $this->actor->key(),
        ]);
    }

    /** @param array<string, mixed> $data */
    private function reminder(MedicalRecord $record, array $data): Model
    {
        return $record->reminders()->create([
            'type' => $data['reminder_type'],
            'title' => $data['title'],
            'due_at' => $data['due_at'],
            'timezone' => $record->timezone,
            'priority' => $data['priority'],
            'status' => MedicalReminderStatus::Scheduled,
            'recipients' => $data['recipients'] ?? [$this->actor->key()],
            'instructions' => $data['instructions'] ?? null,
            'created_by_key' => $this->actor->key(),
        ]);
    }

    private function grams(float $value, string $unit): int
    {
        return (int) round(match ($unit) {
            'g' => $value,
            'lb' => $value * 453.59237,
            'oz' => $value * 28.349523125,
            default => $value * 1000,
        });
    }
}

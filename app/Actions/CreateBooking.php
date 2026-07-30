<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Enums\ConsultationStatus;
use App\Enums\PaymentStatus;
use App\Models\AuditLog;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\Consultation;
use App\Models\DocumentGrant;
use App\Models\ExpertProfile;
use App\Models\Service;
use App\Services\ExpertTaxonomy;
use App\Services\ForumActor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateBooking
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly ExpertTaxonomy $taxonomy,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(ExpertProfile $profile, array $data): Booking
    {
        return DB::transaction(function () use ($profile, $data): Booking {
            $existing = Booking::query()
                ->select(['id', 'reference'])
                ->where('idempotency_key', $data['idempotency_key'])
                ->where('expert_profile_id', $profile->id)
                ->where('client_key', $this->actor->key())
                ->first();

            if ($existing !== null) {
                return Booking::query()->findOrFail($existing->id);
            }

            $service = Service::query()
                ->select([
                    'id', 'expert_profile_id', 'name', 'format', 'duration_minutes',
                    'price', 'currency', 'requires_payment', 'requires_approval',
                    'status',
                ])
                ->where('expert_profile_id', $profile->id)
                ->whereKey($data['service_id'])
                ->where('status', 'active')
                ->firstOrFail();

            $slot = AvailabilitySlot::query()
                ->select([
                    'id', 'expert_profile_id', 'service_id', 'starts_at', 'ends_at',
                    'timezone', 'format', 'location_label', 'capacity',
                    'booked_count', 'status',
                ])
                ->where('expert_profile_id', $profile->id)
                ->whereKey($data['availability_slot_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $slot->hasCapacity() || ($slot->service_id !== null && $slot->service_id !== $service->id)) {
                throw ValidationException::withMessages([
                    'availability_slot_id' => 'This appointment time is no longer available.',
                ]);
            }

            $pet = $this->taxonomy->petData()[$data['pet_key']] ?? null;
            if ($pet === null) {
                throw ValidationException::withMessages(['pet_key' => 'Choose an available pet profile.']);
            }

            $status = $service->requires_payment
                ? BookingStatus::AwaitingPayment
                : ($service->requires_approval ? BookingStatus::Pending : BookingStatus::Confirmed);
            $paymentStatus = $service->requires_payment
                ? PaymentStatus::Pending
                : PaymentStatus::NotRequired;

            $booking = Booking::query()->create([
                'expert_profile_id' => $profile->id,
                'service_id' => $service->id,
                'availability_slot_id' => $slot->id,
                'reference' => (string) Str::uuid(),
                'idempotency_key' => $data['idempotency_key'],
                'client_key' => $this->actor->key(),
                'client_name' => $this->actor->identity()['name'],
                'pet_key' => $data['pet_key'],
                'pet_name' => $pet['name'],
                'pet_species' => $pet['species'],
                'pet_age_label' => $pet['age'],
                'format' => $slot->format,
                'starts_at' => $slot->starts_at,
                'ends_at' => $slot->ends_at,
                'timezone' => $slot->timezone,
                'location_label' => $slot->location_label,
                'status' => $status,
                'questionnaire' => [
                    'main_question' => $data['main_question'],
                    'started_at' => $data['started_at'] ?? null,
                    'tried' => $data['tried'] ?? null,
                    'previous_professional' => $data['previous_professional'] ?? null,
                    'desired_result' => $data['desired_result'] ?? null,
                    'urgent_signs' => false,
                    'access_needs' => $data['access_needs'] ?? null,
                ],
                'documents' => [],
                'amount' => $service->price,
                'currency' => $service->currency,
                'payment_status' => $paymentStatus,
                'terms_accepted' => true,
                'data_consent' => true,
                'recording_consent' => (bool) ($data['recording_consent'] ?? false),
                'confirmed_at' => $status === BookingStatus::Confirmed ? now() : null,
            ]);

            $slot->increment('booked_count');
            if ($slot->fresh()->booked_count >= $slot->capacity) {
                $slot->update(['status' => 'booked']);
            }

            Consultation::query()->create([
                'booking_id' => $booking->id,
                'expert_profile_id' => $profile->id,
                'status' => ConsultationStatus::Scheduled,
                'action_plan' => [],
            ]);

            $document = $data['document'] ?? null;
            if ($document instanceof UploadedFile) {
                DocumentGrant::query()->create([
                    'booking_id' => $booking->id,
                    'expert_profile_id' => $profile->id,
                    'owner_key' => $this->actor->key(),
                    'label' => $data['document_label'] ?? 'Consultation document',
                    'document_type' => $data['document_type'] ?? 'supporting-document',
                    'file_path' => $document->store('consultation-documents', 'local'),
                    'permissions' => ['view'],
                    'expires_at' => now()->addDays(7),
                ]);
            }

            AuditLog::query()->create([
                'expert_profile_id' => $profile->id,
                'booking_id' => $booking->id,
                'actor_key' => $this->actor->key(),
                'actor_role' => 'client',
                'action' => 'booking.created',
                'target_type' => Booking::class,
                'target_id' => (string) $booking->id,
                'metadata' => ['status' => $status->value, 'format' => $slot->format],
            ]);

            return $booking;
        }, 3);
    }
}

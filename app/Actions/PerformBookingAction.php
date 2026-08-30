<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Enums\ConsultationStatus;
use App\Models\AuditLog;
use App\Models\AvailabilitySlot;
use App\Models\Booking;
use App\Models\DocumentGrant;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PerformBookingAction
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(Booking $booking, array $data): string
    {
        return DB::transaction(function () use ($booking, $data): string {
            return match ($data['action']) {
                'cancel' => $this->cancel($booking, (string) ($data['reason'] ?? __('messages.booking.cancelled_by_client'))),
                'request-reschedule' => $this->requestReschedule($booking, (string) ($data['reason'] ?? __('messages.booking.new_time_requested'))),
                'revoke-document' => $this->revokeDocument($booking, (int) $data['document_grant_id']),
                'complete-consultation' => $this->completeConsultation($booking, $data),
                default => throw ValidationException::withMessages(['action' => __('messages.unsupported_booking_action')]),
            };
        });
    }

    private function cancel(Booking $booking, string $reason): string
    {
        if (in_array($booking->status, [BookingStatus::Cancelled, BookingStatus::Completed], true)) {
            return __('messages.the_booking_is_already_closed');
        }

        $booking->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        if ($booking->availability_slot_id !== null) {
            $slot = AvailabilitySlot::query()
                ->select(['id', 'booked_count', 'status'])
                ->lockForUpdate()
                ->find($booking->availability_slot_id);

            if ($slot !== null) {
                $slot->update([
                    'booked_count' => max(0, $slot->booked_count - 1),
                    'status' => 'open',
                ]);
            }
        }

        $booking->documentGrants()->whereNull('revoked_at')->update(['revoked_at' => now()]);
        $this->audit($booking, 'booking.cancelled', ['reason' => $reason]);

        return __('messages.booking_cancelled_and_temporary_document_access_revoked');
    }

    private function requestReschedule(Booking $booking, string $reason): string
    {
        $booking->update([
            'status' => BookingStatus::RescheduleProposed,
            'reschedule_proposed_at' => now(),
            'cancellation_reason' => $reason,
        ]);
        $this->audit($booking, 'booking.reschedule-requested', ['reason' => $reason]);

        return __('messages.reschedule_request_sent_the_current_time_remains_visible_until_a_new_slot_is_agreed');
    }

    private function revokeDocument(Booking $booking, int $grantId): string
    {
        $grant = DocumentGrant::query()
            ->select(['id', 'booking_id', 'revoked_at'])
            ->where('booking_id', $booking->id)
            ->where('owner_key', $this->actor->key())
            ->findOrFail($grantId);

        if ($grant->revoked_at === null) {
            $grant->update(['revoked_at' => now()]);
            $this->audit($booking, 'document-access.revoked', ['grant_id' => $grant->id]);
        }

        return __('messages.document_access_revoked');
    }

    /** @param array<string, mixed> $data */
    private function completeConsultation(Booking $booking, array $data): string
    {
        if ($booking->expertProfile->owner_key !== $this->actor->key()) {
            throw ValidationException::withMessages(['action' => __('messages.only_the_responsible_specialist_can_confirm_a_summary')]);
        }

        $consultation = $booking->consultation;
        $consultation->update([
            'status' => ConsultationStatus::Completed,
            'ended_at' => now(),
            'client_summary' => $data['client_summary'],
            'action_plan' => array_values(array_filter($data['action_plan'] ?? [])),
            'referral_summary' => $data['referral_summary'] ?? null,
            'follow_up_until' => filled($data['follow_up_until']) ? $data['follow_up_until'] : null,
            'summary_confirmed_at' => now(),
        ]);
        $booking->update(['status' => BookingStatus::Completed, 'completed_at' => now()]);
        $this->audit($booking, 'consultation.summary-confirmed', []);

        return __('messages.consultation_summary_confirmed_and_shared_with_the_client');
    }

    /** @param array<string, mixed> $metadata */
    private function audit(Booking $booking, string $action, array $metadata): void
    {
        AuditLog::query()->create([
            'expert_profile_id' => $booking->expert_profile_id,
            'booking_id' => $booking->id,
            'actor_key' => $this->actor->key(),
            'actor_role' => $booking->client_key === $this->actor->key() ? 'client' : 'specialist',
            'action' => $action,
            'target_type' => Booking::class,
            'target_id' => (string) $booking->id,
            'metadata' => $metadata,
        ]);
    }
}

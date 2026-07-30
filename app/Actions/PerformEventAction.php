<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\EventCatalog;
use App\Services\EventContentCatalog;
use App\Services\EventState;
use Illuminate\Validation\ValidationException;

final class PerformEventAction
{
    private const ACTIONS = [
        'toggle-event-interest',
        'register-event',
        'cancel-event-registration',
        'complete-event-payment',
        'toggle-event-calendar',
        'toggle-event-reminder',
        'check-in-event',
        'acknowledge-event-reschedule',
        'set-event-travel-status',
        'send-event-message',
        'publish-event-announcement',
        'approve-event-application',
        'decline-event-application',
        'promote-event-waitlist',
        'reschedule-event',
        'cancel-event',
        'add-event-photo',
        'submit-event-review',
        'create-event-report',
    ];

    public function __construct(
        private readonly EventCatalog $events,
        private readonly EventContentCatalog $content,
        private readonly EventState $state,
    ) {}

    public function supports(string $action): bool
    {
        return in_array($action, self::ACTIONS, true);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    public function handle(array $data): array
    {
        return match ((string) $data['action']) {
            'toggle-event-interest' => $this->toggleInterest($data),
            'register-event' => $this->register($data),
            'cancel-event-registration' => $this->cancelRegistration($data),
            'complete-event-payment' => $this->completePayment($data),
            'toggle-event-calendar' => $this->toggleCalendar($data),
            'toggle-event-reminder' => $this->toggleReminder($data),
            'check-in-event' => $this->checkIn($data),
            'acknowledge-event-reschedule' => $this->acknowledgeReschedule($data),
            'set-event-travel-status' => $this->setTravelStatus($data),
            'send-event-message' => $this->sendMessage($data),
            'publish-event-announcement' => $this->publishAnnouncement($data),
            'approve-event-application' => $this->resolveApplication($data, 'approved'),
            'decline-event-application' => $this->resolveApplication($data, 'declined'),
            'promote-event-waitlist' => $this->promoteWaitlist($data),
            'reschedule-event' => $this->reschedule($data),
            'cancel-event' => $this->cancel($data),
            'add-event-photo' => $this->addPhoto($data),
            'submit-event-review' => $this->submitReview($data),
            'create-event-report' => $this->createReport($data),
            default => throw ValidationException::withMessages([
                'action' => __('messages.this_action_is_unavailable_c64fa3888d'),
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function toggleInterest(array $data): array
    {
        $event = $this->requireEvent($data);
        $active = $this->state->toggleInterest($event['key']);

        return $this->result(
            $active
                ? __('messages.event_saved_to_your_events', ['event' => $event['title']])
                : __('messages.event_removed_from_saved_events', ['event' => $event['title']]),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function register(array $data): array
    {
        $event = $this->requireEvent($data);
        $ticketType = (string) ($data['ticket_type'] ?? 'standard');
        $ticket = collect($this->content->content($event)['ticket_options'])
            ->firstWhere('key', $ticketType);

        if ($ticket === null) {
            throw ValidationException::withMessages([
                'ticket_type' => __('messages.choose_an_available_event_ticket_8219ef1b57'),
            ]);
        }

        if (! $event['pets_allowed'] && ($data['event_pet'] ?? 'owner-only') !== 'owner-only') {
            throw ValidationException::withMessages([
                'event_pet' => __('messages.this_event_is_for_owners_without_resident_pets_af7d5ede9e'),
            ]);
        }

        $existing = $this->state->registration($event['key']);
        $registration = $this->state->register($event, [
            ...$data,
            'ticket_price_minor' => $ticket['price_minor'],
        ]);

        if ($existing !== null && $registration['id'] === $existing['id']) {
            return $this->result(
                __('messages.event_registration_status_unchanged', [
                    'status' => $this->registrationStatusLabel($registration['status']),
                ]),
                $data,
                'tickets',
            );
        }

        $message = match ($registration['status']) {
            'pending' => __('messages.your_application_was_sent_to_the_event_organizer_6d61f0e964'),
            'waitlisted' => __('messages.the_event_is_full_you_joined_the_waitlist_b610dcb36d'),
            'payment_required' => __('messages.your_place_is_reserved_temporarily_complete_the_prototyp_75c673bba7'),
            default => __('messages.your_event_registration_is_confirmed_f0795d25c2'),
        };

        return $this->result($message, $data, 'tickets');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function cancelRegistration(array $data): array
    {
        $event = $this->requireEvent($data);
        $registration = $this->state->cancelRegistration($event['key']);

        if ($registration === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.this_event_registration_can_no_longer_be_cancelled_a596414870'),
            ]);
        }

        $message = $registration['payment_status'] === 'refunded'
            ? __('messages.registration_cancelled_the_prototype_payment_is_marked_r_747c7aa77d')
            : __('messages.registration_cancelled_and_the_place_was_released_2dfb575a0a');

        return $this->result($message, $data, 'tickets');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function completePayment(array $data): array
    {
        $event = $this->requireEvent($data);
        $outcome = (string) ($data['payment_outcome'] ?? 'success');
        $registration = $this->state->completePayment($event['key'], $outcome);

        if ($registration === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.this_registration_does_not_have_a_pending_prototype_paym_45528aaeb7'),
            ]);
        }

        return $this->result(
            $outcome === 'failure'
                ? __('messages.payment_simulation_failed_no_charge_or_duplicate_ticket__ca8f20e048')
                : __('messages.payment_simulation_complete_your_unique_ticket_is_ready_e1ea884072'),
            $data,
            'tickets',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function toggleCalendar(array $data): array
    {
        $event = $this->requireEvent($data);
        $active = $this->state->toggleCalendar($event['key']);

        return $this->result(
            $active ? __('messages.event_added_to_your_calendar_d7defae028') : __('messages.event_removed_from_your_calendar_5bde4e038c'),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function toggleReminder(array $data): array
    {
        $event = $this->requireEvent($data);
        $active = $this->state->toggleReminder($event['key']);

        return $this->result(
            $active ? __('messages.event_reminders_enabled_b95d6adbd8') : __('messages.event_reminders_paused_61da156d54'),
            $data,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function checkIn(array $data): array
    {
        $event = $this->requireEvent($data);
        $registration = $this->state->checkIn(
            $event['key'],
            (string) ($data['check_in_method'] ?? 'qr'),
        );

        if ($registration === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.a_confirmed_ticket_is_required_before_check_in_16f71b923a'),
            ]);
        }

        return $this->result(
            __('messages.attendance_confirmed_repeating_check_in_will_not_create__33ff2e10b4'),
            $data,
            'tickets',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function acknowledgeReschedule(array $data): array
    {
        $event = $this->requireEvent($data);

        if ($this->state->acknowledgeReschedule($event['key']) === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.register_before_confirming_the_revised_date_c29c3a138a'),
            ]);
        }

        return $this->result(__('messages.you_confirmed_the_revised_event_details_8cf15fa70f'), $data, 'tickets');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function setTravelStatus(array $data): array
    {
        $event = $this->requireEvent($data);
        $status = (string) ($data['travel_status'] ?? '');

        if ($this->state->registration($event['key']) === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.register_before_sharing_an_arrival_status_9d52e4b7e5'),
            ]);
        }

        $this->state->setTravelStatus($event['key'], $status);

        return $this->result(
            __('messages.event_arrival_status_updated', [
                'status' => $this->travelStatusLabel($status),
            ]),
            $data,
            'tickets',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function sendMessage(array $data): array
    {
        $event = $this->requireEvent($data);

        if ($this->state->registration($event['key']) === null && ! $event['managed_by_current_user']) {
            throw ValidationException::withMessages([
                'target' => __('messages.register_or_apply_before_joining_the_event_chat_11b60b97c0'),
            ]);
        }

        $this->state->addMessage($event['key'], [
            'name' => __('messages.mia_carter_0e5b29cc3b'),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return $this->result(__('messages.message_posted_in_the_event_chat_7a19e5952c'), $data, 'chat');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function publishAnnouncement(array $data): array
    {
        $event = $this->requireManagedEvent($data);
        $this->state->addAnnouncement($event['key'], [
            'title' => $this->requireText($data, 'title'),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return $this->result(
            __('messages.announcement_published_for_registered_attendees_08fce09435'),
            $data,
            'announcements',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function resolveApplication(array $data, string $status): array
    {
        $event = $this->requireManagedEvent($data);
        $application = (string) ($data['event_application'] ?? '');

        if (! $this->state->resolveApplication($event['key'], $application, $status)) {
            throw ValidationException::withMessages([
                'event_application' => __('messages.this_event_application_is_no_longer_pending_67f9dc1936'),
            ]);
        }

        return $this->result(
            $status === 'approved'
                ? __('messages.application_approved_54aea150a8')
                : __('messages.application_declined_without_exposing_a_private_reason_eb107d39fc'),
            $data,
            'manage',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function promoteWaitlist(array $data): array
    {
        $event = $this->requireManagedEvent($data);
        $candidate = (string) ($data['event_candidate'] ?? '');

        if (! $this->state->promoteWaitlist($event['key'], $candidate)) {
            throw ValidationException::withMessages([
                'event_candidate' => __('messages.this_waitlist_place_can_no_longer_be_promoted_9ba5f83776'),
            ]);
        }

        return $this->result(
            __('messages.the_next_eligible_person_received_a_temporary_place_hold_c781d73d9d'),
            $data,
            'manage',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function reschedule(array $data): array
    {
        $event = $this->requireManagedEvent($data);
        $this->state->reschedule($event['key'], [
            'date' => (string) ($data['event_date'] ?? ''),
            'time' => (string) ($data['event_time'] ?? ''),
            'note' => $this->requireText($data, 'event_note'),
        ]);

        return $this->result(
            __('messages.event_rescheduled_existing_attendees_must_confirm_the_ne_b63f030570'),
            $data,
            'manage',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function cancel(array $data): array
    {
        $event = $this->requireManagedEvent($data);
        $this->state->cancelEvent(
            $event['key'],
            (string) ($data['event_reason'] ?? __('messages.cancelled_by_organizer_1612180e9f')),
        );

        return $this->result(
            __('messages.event_cancelled_new_payments_are_stopped_and_attendee_ob_1a80e5e30b'),
            $data,
            'manage',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function addPhoto(array $data): array
    {
        $event = $this->requireEvent($data);
        $registration = $this->state->registration($event['key']);

        if (! $event['managed_by_current_user'] && $registration === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.only_organizers_and_attendees_can_add_event_photos_8053307018'),
            ]);
        }

        $this->state->addPhoto($event['key'], [
            'src' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=1200&h=900&q=85',
            'alt' => __('messages.dog_resting_on_grass_during_a_calm_community_event_9516b92512'),
            'caption' => trim((string) ($data['photo_caption'] ?? __('messages.shared_by_an_event_attendee_with_consent_61cae35695'))),
        ]);

        return $this->result(
            __('messages.photo_added_to_the_event_album_for_moderation_5c38948be4'),
            $data,
            'media',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function submitReview(array $data): array
    {
        $event = $this->requireEvent($data);

        if (! $this->state->addReview($event['key'], [
            'rating' => (int) ($data['event_rating'] ?? 0),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ])) {
            throw ValidationException::withMessages([
                'target' => __('messages.only_checked_in_attendees_can_publish_a_verified_event_r_297ec1da98'),
            ]);
        }

        return $this->result(
            __('messages.your_verified_attendance_review_was_published_165d478885'),
            $data,
            'reviews',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function createReport(array $data): array
    {
        $event = $this->requireEvent($data);
        $this->state->addReport($event['key'], [
            'reason' => (string) ($data['category'] ?? ''),
            'body' => $this->requireText($data, 'body'),
            'created_at' => now()->toAtomString(),
        ]);

        return $this->result(__('messages.your_private_event_report_was_received_604e1b7721'), $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function requireEvent(array $data): array
    {
        $event = $this->events->find((string) ($data['target'] ?? ''));

        if ($event === null) {
            throw ValidationException::withMessages([
                'target' => __('messages.choose_an_available_event_38bf68e8c6'),
            ]);
        }

        return $event;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function requireManagedEvent(array $data): array
    {
        $event = $this->requireEvent($data);

        if (! $event['managed_by_current_user']) {
            throw ValidationException::withMessages([
                'target' => __('messages.only_an_authorized_event_organizer_can_perform_this_acti_a24696f6e5'),
            ]);
        }

        return $event;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function result(string $message, array $data, string $defaultTab = 'overview'): array
    {
        if (! array_key_exists('event_return_tab', $data)) {
            return [
                'message' => $message,
                'route' => null,
            ];
        }

        return [
            'message' => $message,
            'route' => 'meetups.show',
            'parameters' => [
                'event' => (string) ($data['target'] ?? ''),
                'tab' => (string) ($data['event_return_tab'] ?? $defaultTab),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function requireText(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));

        if ($value === '') {
            throw ValidationException::withMessages([
                $key => __('messages.this_field_is_required_68cadcee19'),
            ]);
        }

        return $value;
    }

    private function registrationStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => __('messages.status_pending'),
            'waitlisted' => __('messages.status_waitlisted'),
            'payment_required' => __('messages.status_payment_required'),
            'confirmed' => __('messages.status_confirmed'),
            'cancelled' => __('messages.status_cancelled'),
            default => __('messages.status_unknown'),
        };
    }

    private function travelStatusLabel(string $status): string
    {
        return match ($status) {
            'leaving' => __('messages.travel_status_leaving'),
            'approaching' => __('messages.travel_status_approaching'),
            'late' => __('messages.travel_status_late'),
            'arrived' => __('messages.travel_status_arrived'),
            'cannot-find' => __('messages.travel_status_cannot_find'),
            'not-coming' => __('messages.travel_status_not_coming'),
            default => __('messages.status_unknown'),
        };
    }
}

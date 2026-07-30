<?php

namespace App\Services;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class PawCircleEventState
{
    private const SESSION_KEY = 'paw-circle.events.v1';

    public function __construct(private readonly Session $session) {}

    /**
     * @return array<string, mixed>|null
     */
    public function registration(string $event): ?array
    {
        return $this->state()['registrations'][$event] ?? null;
    }

    /**
     * @param  array<string, mixed>  $event
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function register(array $event, array $data): array
    {
        return Cache::lock('paw-circle:event-registration:'.$event['key'], 5)->block(
            2,
            function () use ($event, $data): array {
                $existing = $this->registration($event['key']);

                if ($existing !== null && ! in_array($existing['status'], ['cancelled', 'declined'], true)) {
                    return $existing;
                }

                $remaining = $this->remainingSeats($event);
                $priceMinor = (int) ($data['ticket_price_minor'] ?? $event['price_minor']);
                $status = match (true) {
                    $remaining <= 0 => 'waitlisted',
                    $event['registration_policy'] === 'approval' => 'pending',
                    $priceMinor > 0 => 'payment_required',
                    default => 'confirmed',
                };
                $now = now()->toAtomString();
                $registration = [
                    'id' => (string) Str::uuid(),
                    'event' => $event['key'],
                    'status' => $status,
                    'pet' => (string) ($data['event_pet'] ?? 'owner-only'),
                    'ticket_type' => (string) ($data['ticket_type'] ?? 'standard'),
                    'ticket_price_minor' => $priceMinor,
                    'currency' => $event['currency'],
                    'guest_count' => (int) ($data['guest_count'] ?? 0),
                    'attendance_format' => (string) ($data['attendance_format'] ?? $event['format']),
                    'requirements_note' => trim((string) ($data['requirements_note'] ?? '')),
                    'photo_consent' => (string) ($data['photo_consent'] ?? 'ask-first'),
                    'accepted_rules' => ($data['accepted_rules'] ?? 'no') === 'yes',
                    'payment_status' => $priceMinor > 0 ? 'pending' : 'not_required',
                    'payment_reference' => null,
                    'ticket_code' => $status === 'confirmed' ? $this->ticketCode($event['key']) : null,
                    'checked_in_at' => null,
                    'reschedule_acknowledged' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $state = $this->state();
                $state['registrations'][$event['key']] = $registration;
                $this->recordHistory($state, $event['key'], 'Registration created with status '.$status.'.');
                $this->store($state);

                return $registration;
            },
        );
    }

    /**
     * @param  array<string, mixed>  $event
     */
    public function remainingSeats(array $event): int
    {
        $registration = $this->registration($event['key']);
        $localSeats = $registration !== null && $this->consumesSeat($registration['status']) ? 1 : 0;

        return max(0, (int) $event['capacity'] - (int) $event['base_attendees'] - $localSeats);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function cancelRegistration(string $event): ?array
    {
        $registration = $this->registration($event);

        if ($registration === null || in_array($registration['status'], ['cancelled', 'declined'], true)) {
            return null;
        }

        $registration['status'] = 'cancelled';
        $registration['payment_status'] = $registration['payment_status'] === 'paid'
            ? 'refunded'
            : 'cancelled';
        $registration['updated_at'] = now()->toAtomString();

        $state = $this->state();
        $state['registrations'][$event] = $registration;
        $this->recordHistory($state, $event, 'Registration cancelled and the place released.');
        $this->store($state);

        return $registration;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function completePayment(string $event, string $outcome): ?array
    {
        $registration = $this->registration($event);

        if ($registration === null || ! in_array($registration['status'], ['payment_required', 'payment_failed'], true)) {
            return null;
        }

        if ($outcome === 'failure') {
            $registration['status'] = 'payment_failed';
            $registration['payment_status'] = 'failed';
            $message = 'Prototype payment failed without creating a charge.';
        } else {
            $registration['status'] = 'confirmed';
            $registration['payment_status'] = 'paid';
            $registration['payment_reference'] ??= 'PAY-'.Str::upper(Str::random(10));
            $registration['ticket_code'] ??= $this->ticketCode($event);
            $message = 'Prototype payment confirmed and a ticket was issued.';
        }

        $registration['updated_at'] = now()->toAtomString();
        $state = $this->state();
        $state['registrations'][$event] = $registration;
        $this->recordHistory($state, $event, $message);
        $this->store($state);

        return $registration;
    }

    public function toggleInterest(string $event): bool
    {
        $state = $this->state();
        $active = ! ($state['interested'][$event] ?? false);
        $state['interested'][$event] = $active;
        $this->recordHistory($state, $event, $active ? 'Marked interested.' : 'Removed interested status.');
        $this->store($state);

        return $active;
    }

    public function isInterested(string $event): bool
    {
        return (bool) ($this->state()['interested'][$event] ?? false);
    }

    public function toggleCalendar(string $event): bool
    {
        $state = $this->state();
        $active = ! ($state['calendar'][$event] ?? false);
        $state['calendar'][$event] = $active;
        $this->recordHistory($state, $event, $active ? 'Added to personal calendar.' : 'Removed from personal calendar.');
        $this->store($state);

        return $active;
    }

    public function isInCalendar(string $event): bool
    {
        return (bool) ($this->state()['calendar'][$event] ?? false);
    }

    public function toggleReminder(string $event): bool
    {
        $state = $this->state();
        $active = ! ($state['reminders'][$event] ?? false);
        $state['reminders'][$event] = $active;
        $this->recordHistory($state, $event, $active ? 'Event reminders enabled.' : 'Event reminders paused.');
        $this->store($state);

        return $active;
    }

    public function reminderIsEnabled(string $event): bool
    {
        return (bool) ($this->state()['reminders'][$event] ?? false);
    }

    public function setTravelStatus(string $event, string $status): void
    {
        $state = $this->state();
        $state['travel_status'][$event] = $status;
        $this->recordHistory($state, $event, 'Arrival status updated to '.Str::headline($status).'.');
        $this->store($state);
    }

    public function travelStatus(string $event): ?string
    {
        return $this->state()['travel_status'][$event] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function checkIn(string $event, string $method): ?array
    {
        $registration = $this->registration($event);

        if ($registration === null || ! in_array($registration['status'], ['confirmed', 'checked_in'], true)) {
            return null;
        }

        if ($registration['status'] !== 'checked_in') {
            $registration['status'] = 'checked_in';
            $registration['checked_in_at'] = now()->toAtomString();
            $registration['check_in_method'] = $method;
            $registration['updated_at'] = now()->toAtomString();
        }

        $state = $this->state();
        $state['registrations'][$event] = $registration;
        $this->recordHistory($state, $event, 'Attendance checked in by '.$method.'.');
        $this->store($state);

        return $registration;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function acknowledgeReschedule(string $event): ?array
    {
        $registration = $this->registration($event);

        if ($registration === null) {
            return null;
        }

        $registration['reschedule_acknowledged'] = true;
        $registration['updated_at'] = now()->toAtomString();
        $state = $this->state();
        $state['registrations'][$event] = $registration;
        $this->recordHistory($state, $event, 'Rescheduled date acknowledged.');
        $this->store($state);

        return $registration;
    }

    /**
     * @param  array{name: string, body: string, created_at: string}  $message
     */
    public function addMessage(string $event, array $message): void
    {
        $state = $this->state();
        $state['messages'][$event] ??= [];
        array_push($state['messages'][$event], $message);
        $state['messages'][$event] = array_slice($state['messages'][$event], -30);
        $this->recordHistory($state, $event, 'A participant message was added.');
        $this->store($state);
    }

    /**
     * @return array<int, array{name: string, body: string, created_at: string}>
     */
    public function messages(string $event): array
    {
        return $this->state()['messages'][$event] ?? [];
    }

    /**
     * @param  array{title: string, body: string, created_at: string}  $announcement
     */
    public function addAnnouncement(string $event, array $announcement): void
    {
        $state = $this->state();
        $state['announcements'][$event] ??= [];
        array_unshift($state['announcements'][$event], $announcement);
        $state['announcements'][$event] = array_slice($state['announcements'][$event], 0, 12);
        $this->recordHistory($state, $event, 'Organizer published an announcement.');
        $this->store($state);
    }

    /**
     * @return array<int, array{title: string, body: string, created_at: string}>
     */
    public function announcements(string $event): array
    {
        return $this->state()['announcements'][$event] ?? [];
    }

    /**
     * @param  array{rating: int, body: string, created_at: string}  $review
     */
    public function addReview(string $event, array $review): bool
    {
        $registration = $this->registration($event);

        if ($registration === null || $registration['status'] !== 'checked_in') {
            return false;
        }

        $state = $this->state();
        $state['reviews'][$event] = [$review];
        $this->recordHistory($state, $event, 'Verified-attendance review submitted.');
        $this->store($state);

        return true;
    }

    /**
     * @return array<int, array{rating: int, body: string, created_at: string}>
     */
    public function reviews(string $event): array
    {
        return $this->state()['reviews'][$event] ?? [];
    }

    /**
     * @param  array{reason: string, body: string, created_at: string}  $report
     */
    public function addReport(string $event, array $report): void
    {
        $state = $this->state();
        array_unshift($state['reports'], ['event' => $event, ...$report]);
        $state['reports'] = array_slice($state['reports'], 0, 20);
        $this->recordHistory($state, $event, 'Private event report submitted.');
        $this->store($state);
    }

    public function resolveApplication(string $event, string $application, string $status): bool
    {
        $state = $this->state();
        $current = $state['applications'][$event][$application] ?? 'pending';

        if ($current !== 'pending') {
            return false;
        }

        $state['applications'][$event][$application] = $status;
        $this->recordHistory($state, $event, 'Application '.$application.' marked '.$status.'.');
        $this->store($state);

        return true;
    }

    public function applicationStatus(string $event, string $application): string
    {
        return $this->state()['applications'][$event][$application] ?? 'pending';
    }

    public function promoteWaitlist(string $event, string $candidate): bool
    {
        $state = $this->state();
        $current = $state['waitlist'][$event][$candidate] ?? 'waiting';

        if ($current !== 'waiting') {
            return false;
        }

        $state['waitlist'][$event][$candidate] = 'promoted';
        $this->recordHistory($state, $event, 'Waitlist candidate '.$candidate.' promoted with a temporary hold.');
        $this->store($state);

        return true;
    }

    public function waitlistStatus(string $event, string $candidate): string
    {
        return $this->state()['waitlist'][$event][$candidate] ?? 'waiting';
    }

    /**
     * @param  array{date: string, time: string, note: string}  $change
     */
    public function reschedule(string $event, array $change): void
    {
        $state = $this->state();
        $state['changes'][$event] ??= [];
        array_unshift($state['changes'][$event], [
            'type' => 'rescheduled',
            'date' => $change['date'],
            'time' => $change['time'],
            'note' => $change['note'],
            'created_at' => now()->toAtomString(),
        ]);
        $state['event_status'][$event] = 'rescheduled';

        if (isset($state['registrations'][$event])) {
            $state['registrations'][$event]['reschedule_acknowledged'] = false;
        }

        $this->recordHistory($state, $event, 'Event rescheduled; attendee confirmation required.');
        $this->store($state);
    }

    public function cancelEvent(string $event, string $reason): void
    {
        $state = $this->state();
        $state['event_status'][$event] = 'cancelled';
        $state['changes'][$event] ??= [];
        array_unshift($state['changes'][$event], [
            'type' => 'cancelled',
            'date' => '',
            'time' => '',
            'note' => $reason,
            'created_at' => now()->toAtomString(),
        ]);
        $this->recordHistory($state, $event, 'Organizer cancelled the event.');
        $this->store($state);
    }

    public function eventStatus(string $event, string $fallback): string
    {
        return $this->state()['event_status'][$event] ?? $fallback;
    }

    /**
     * @return array<int, array{type: string, date: string, time: string, note: string, created_at: string}>
     */
    public function changes(string $event): array
    {
        return $this->state()['changes'][$event] ?? [];
    }

    /**
     * @param  array{src: string, alt: string, caption: string}  $photo
     */
    public function addPhoto(string $event, array $photo): void
    {
        $state = $this->state();
        $state['photos'][$event] ??= [];
        array_unshift($state['photos'][$event], $photo);
        $state['photos'][$event] = array_slice($state['photos'][$event], 0, 12);
        $this->recordHistory($state, $event, 'Photo added to the event album.');
        $this->store($state);
    }

    /**
     * @return array<int, array{src: string, alt: string, caption: string}>
     */
    public function photos(string $event): array
    {
        return $this->state()['photos'][$event] ?? [];
    }

    /**
     * @return array<int, array{message: string, created_at: string}>
     */
    public function history(string $event): array
    {
        return $this->state()['history'][$event] ?? [];
    }

    private function consumesSeat(string $status): bool
    {
        return in_array($status, ['payment_required', 'payment_failed', 'confirmed', 'checked_in'], true);
    }

    private function ticketCode(string $event): string
    {
        return 'PC-'.Str::upper(Str::substr(Str::slug($event), 0, 6)).'-'.Str::upper(Str::random(6));
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function recordHistory(array &$state, string $event, string $message): void
    {
        $state['history'][$event] ??= [];
        array_unshift($state['history'][$event], [
            'message' => $message,
            'created_at' => now()->toAtomString(),
        ]);
        $state['history'][$event] = array_slice($state['history'][$event], 0, 30);
    }

    /**
     * @return array<string, mixed>
     */
    private function state(): array
    {
        $stored = $this->session->get(self::SESSION_KEY, []);

        return [
            'registrations' => $stored['registrations'] ?? [],
            'interested' => $stored['interested'] ?? [],
            'calendar' => $stored['calendar'] ?? [],
            'reminders' => $stored['reminders'] ?? [],
            'messages' => $stored['messages'] ?? [],
            'announcements' => $stored['announcements'] ?? [],
            'reviews' => $stored['reviews'] ?? [],
            'reports' => $stored['reports'] ?? [],
            'applications' => $stored['applications'] ?? [],
            'waitlist' => $stored['waitlist'] ?? [],
            'changes' => $stored['changes'] ?? [],
            'event_status' => $stored['event_status'] ?? [],
            'travel_status' => $stored['travel_status'] ?? [],
            'photos' => $stored['photos'] ?? [],
            'history' => $stored['history'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function store(array $state): void
    {
        $this->session->put(self::SESSION_KEY, $state);
    }
}

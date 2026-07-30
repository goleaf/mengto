<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class EventPresenter
{
    public function __construct(
        private readonly EventCatalog $catalog,
        private readonly EventContentCatalog $content,
        private readonly EventState $state,
        private readonly ProfilePresenter $profiles,
        private readonly CreatedContentPresenter $created,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function directory(
        string $query = '',
        string $filter = 'recommended',
        string $sort = 'soonest',
        string $view = 'list',
    ): array {
        $query = trim($query);
        $filter = array_key_exists($filter, $this->filterOptions()) ? $filter : 'recommended';
        $sort = array_key_exists($sort, $this->sortOptions()) ? $sort : 'soonest';
        $view = array_key_exists($view, $this->viewOptions()) ? $view : 'list';
        $events = array_map(
            fn (array $event): array => $this->decorateDirectoryEvent($event),
            $this->catalog->all(),
        );
        $events = array_values(array_filter(
            $events,
            fn (array $event): bool => $this->matches($event, $query, $filter),
        ));
        usort($events, fn (array $left, array $right): int => $this->compare($left, $right, $sort));
        $events = [...$this->createdEvents($query, $filter), ...$events];

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => 'Events | PawCircle',
            'active_section' => 'meetups',
            'summary' => [
                'eyebrow' => 'Events and real-world plans',
                'title' => 'Find a gathering that fits you and your pet',
                'description' => 'Walks, training, shows, shelter days, volunteer actions, celebrations, and online learning with clear participation and safety details.',
                'count' => count($events).' '.Str::plural('event', count($events)),
                'highlights' => [
                    ['label' => 'Next', 'value' => 'Thu, Jul 30', 'detail' => 'urgent local search'],
                    ['label' => 'This week', 'value' => '5 events', 'detail' => 'online and nearby'],
                    ['label' => 'Saved', 'value' => (string) $this->interestedCount(), 'detail' => 'events marked interested'],
                    ['label' => 'Timezone', 'value' => 'Pacific', 'detail' => 'shown in local time'],
                ],
            ],
            'events' => [
                'items' => $events,
                'query' => $query,
                'filter' => $filter,
                'sort' => $sort,
                'view' => $view,
                'filters' => array_values($this->filterOptions()),
                'sort_options' => $this->sortOptions(),
                'view_options' => $this->viewOptions(),
                'browse_url' => route('pet-social.meetups.index'),
                'create_url' => route('pet-social.compose', ['kind' => 'meetup']),
                'calendar' => $this->calendarSummary($events),
                'map' => $this->mapSummary($events),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $key, string $tab = 'overview'): ?array
    {
        $event = $this->catalog->find($key);

        if ($event === null) {
            return null;
        }

        $tabOptions = $this->tabOptions((bool) $event['managed_by_current_user']);
        $tab = array_key_exists($tab, $tabOptions) ? $tab : 'overview';
        $registration = $this->state->registration($key);
        $event['status'] = $this->state->eventStatus($key, $event['status']);
        $event = $this->decorateEvent($event, $registration);
        $canViewPrivateDetails = $this->canViewPrivateDetails($event, $registration);
        $content = $this->content->content($event);
        $content['schedule'] = $this->applyScheduleChangeToProgram($event, $content['schedule']);
        $content['chat'] = $this->chat($key, $content['chat']);
        $content['announcements'] = $this->announcements($key, $content['announcements']);
        $content['gallery'] = $this->gallery($key, $content['gallery']);
        $content['reviews'] = $this->reviews($key, $content['reviews']);
        $content['applications'] = $this->applications($key, $content['applications']);
        $content['waitlist'] = $this->waitlist($key, $content['waitlist']);
        $content['history'] = $this->history($key);
        $content['location']['revealed_exact'] = $canViewPrivateDetails
            ? $content['location']['exact']
            : 'Exact details unlock after approval and any required payment.';
        $content['location']['revealed_online_link'] = $canViewPrivateDetails
            ? $content['location']['online_link']
            : null;

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => $event['title'].' | PawCircle',
            'active_section' => 'meetups',
            'event' => $event,
            'tabs' => $this->tabs($event, $tab, $tabOptions),
            'active_tab' => $tab,
            'content' => $content,
            'registration' => $this->registrationPanel($event, $registration, $content['ticket_options'], $tab),
            'can_view_private_details' => $canViewPrivateDetails,
            'organizer_tools' => $event['managed_by_current_user']
                ? $this->organizerTools($event, $tab)
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function decorateDirectoryEvent(array $event): array
    {
        $event = $this->applyScheduleChange($event);
        $registration = $this->state->registration($event['key']);
        $startsAt = CarbonImmutable::parse($event['starts_at']);
        $remaining = $this->state->remainingSeats($event);
        $status = $this->state->eventStatus($event['key'], $event['status']);

        return [
            ...$event,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_tone' => $this->statusTone($status),
            'detail_route' => 'pet-social.meetups.show',
            'detail_parameters' => ['event' => $event['key']],
            'day' => Str::upper($startsAt->format('D')),
            'date' => $startsAt->format('d'),
            'date_label' => $startsAt->format('D, M j'),
            'date_accessible' => $startsAt->format('l, F j, Y \a\t g:i A T'),
            'datetime' => $event['starts_at'],
            'time' => $startsAt->format('g:i A'),
            'place' => $event['general_location'],
            'neighborhood' => $event['format'] === 'online'
                ? 'Timezone-aware online access'
                : 'Exact entrance after confirmation',
            'attendees' => $event['base_attendees'].' confirmed',
            'remaining' => $remaining,
            'capacity_label' => $remaining > 0 ? $remaining.' places left' : 'Waitlist available',
            'price_label' => $this->priceLabel($event),
            'format_label' => Str::headline($event['format']),
            'privacy_label' => Str::headline($event['privacy']),
            'rsvp' => $registration !== null && in_array($registration['status'], ['confirmed', 'checked_in'], true),
            'registration_status' => $registration['status'] ?? null,
            'interested' => $this->state->isInterested($event['key']),
            'primary_action' => [
                'label' => 'View event',
                'icon' => 'arrow-up-right',
                'variant' => 'primary',
                'href' => route('pet-social.meetups.show', ['event' => $event['key']]),
            ],
            'interest_action' => [
                'label' => $this->state->isInterested($event['key']) ? 'Interested' : 'Save event',
                'icon' => $this->state->isInterested($event['key']) ? 'bookmark-check' : 'bookmark',
                'active' => $this->state->isInterested($event['key']),
                'endpoint' => route('pet-social.actions.perform'),
                'payload' => [
                    'action' => 'toggle-event-interest',
                    'target' => $event['key'],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @param  array<string, mixed>|null  $registration
     * @return array<string, mixed>
     */
    private function decorateEvent(array $event, ?array $registration): array
    {
        $event = $this->applyScheduleChange($event);
        $startsAt = CarbonImmutable::parse($event['starts_at']);
        $endsAt = CarbonImmutable::parse($event['ends_at']);
        $remaining = $this->state->remainingSeats($event);

        return [
            ...$event,
            'eyebrow' => $event['verification_label'] ?? Str::headline($event['category']).' event',
            'long_description' => $event['description'],
            'status_label' => $this->statusLabel($event['status']),
            'status_tone' => $this->statusTone($event['status']),
            'date_label' => $startsAt->format('D, M j'),
            'date_accessible' => $startsAt->format('l, F j, Y \a\t g:i A T'),
            'time_label' => $startsAt->format('g:i A').'–'.$endsAt->format('g:i A'),
            'price_label' => $this->priceLabel($event),
            'remaining' => $remaining,
            'capacity_label' => $remaining > 0 ? $remaining.' places left' : 'Waitlist available',
            'registration_status' => $registration['status'] ?? null,
            'registration_label' => $this->registrationLabel($registration['status'] ?? null),
            'in_calendar' => $this->state->isInCalendar($event['key']),
            'reminder_enabled' => $this->state->reminderIsEnabled($event['key']),
            'meta' => [
                [
                    'icon' => 'calendar-days',
                    'label' => $startsAt->format('D, M j · g:i A').' · '.$event['timezone'],
                    'datetime' => $event['starts_at'],
                    'aria_label' => $startsAt->format('l, F j, Y \a\t g:i A T'),
                ],
                [
                    'icon' => $event['format'] === 'online' ? 'video' : 'map-pin',
                    'label' => $event['general_location'],
                ],
                ['icon' => 'user-round', 'label' => 'Organized by '.$event['organizer']],
                ['icon' => $event['privacy'] === 'public' ? 'globe-2' : 'lock-keyhole', 'label' => Str::headline($event['privacy']).' event'],
            ],
            'stats' => [
                ['label' => 'Confirmed', 'value' => (string) $event['base_attendees'], 'detail' => $remaining > 0 ? $remaining.' places remain' : 'waitlist open'],
                ['label' => 'Duration', 'value' => $startsAt->diffForHumans($endsAt, true), 'detail' => $event['activity_level'].' format'],
                ['label' => 'Ticket', 'value' => $this->priceLabel($event), 'detail' => Str::headline($event['registration_policy']).' registration'],
                ['label' => 'Language', 'value' => $event['language'], 'detail' => 'event and materials'],
            ],
            'primary_action' => $this->primaryDetailAction($event, $registration),
            'secondary_actions' => [
                [
                    'label' => $this->state->isInCalendar($event['key']) ? 'In calendar' : 'Add to calendar',
                    'icon' => $this->state->isInCalendar($event['key']) ? 'calendar-check' : 'calendar-plus',
                    'active' => $this->state->isInCalendar($event['key']),
                    'endpoint' => route('pet-social.actions.perform'),
                    'payload' => [
                        'action' => 'toggle-event-calendar',
                        'target' => $event['key'],
                    ],
                ],
                [
                    'label' => 'Share',
                    'icon' => 'send',
                    'href' => route('pet-social.share.show', ['target' => $event['key']]),
                ],
                [
                    'label' => 'Report',
                    'icon' => 'flag',
                    'href' => route('pet-social.compose', [
                        'kind' => 'report-event',
                        'target' => $event['key'],
                    ]),
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @param  array<string, mixed>|null  $registration
     * @return array<string, mixed>
     */
    private function primaryDetailAction(array $event, ?array $registration): array
    {
        if ($registration === null || in_array($registration['status'], ['cancelled', 'declined'], true)) {
            return [
                'label' => $event['registration_policy'] === 'approval' ? 'Apply to attend' : 'Register',
                'icon' => 'ticket-check',
                'variant' => 'primary',
                'href' => route('pet-social.meetups.show', ['event' => $event['key'], 'tab' => 'tickets']),
            ];
        }

        if (in_array($registration['status'], ['payment_required', 'payment_failed'], true)) {
            return [
                'label' => $registration['status'] === 'payment_failed' ? 'Retry payment' : 'Complete payment',
                'icon' => 'credit-card',
                'variant' => 'primary',
                'href' => route('pet-social.meetups.show', ['event' => $event['key'], 'tab' => 'tickets']),
            ];
        }

        return [
            'label' => $this->registrationLabel($registration['status']),
            'icon' => $registration['status'] === 'checked_in' ? 'badge-check' : 'ticket-check',
            'variant' => 'paper',
            'href' => route('pet-social.meetups.show', ['event' => $event['key'], 'tab' => 'tickets']),
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @param  array<string, mixed>|null  $registration
     * @param  array<int, array<string, mixed>>  $ticketOptions
     * @return array<string, mixed>
     */
    private function registrationPanel(
        array $event,
        ?array $registration,
        array $ticketOptions,
        string $tab,
    ): array {
        return [
            'status' => $registration['status'] ?? null,
            'status_label' => $this->registrationLabel($registration['status'] ?? null),
            'registration' => $registration,
            'travel_status' => $this->state->travelStatus($event['key']),
            'ticket_options' => array_map(
                fn (array $ticket): array => [
                    ...$ticket,
                    'price_label' => $this->formatMoney((int) $ticket['price_minor'], $ticket['currency']),
                ],
                $ticketOptions,
            ),
            'pets' => [
                'scout' => 'Scout · Border Collie',
                'nori' => 'Nori · Domestic Shorthair',
                'owner-only' => 'Attend without a pet',
            ],
            'can_register_pet' => (bool) $event['pets_allowed'],
            'terms' => $event['price_minor'] > 0
                ? 'Prototype checkout only. No card details are collected. Cancel before the published deadline for the represented full refund.'
                : 'Cancelling releases this place to the next eligible person on the waitlist.',
            'register_action' => route('pet-social.actions.perform'),
            'register_payload' => [
                'action' => 'register-event',
                'target' => $event['key'],
                'event_return_tab' => $tab,
            ],
            'calendar_action' => [
                'label' => $event['in_calendar'] ? 'Remove from calendar' : 'Add to calendar',
                'icon' => $event['in_calendar'] ? 'calendar-x' : 'calendar-plus',
                'active' => $event['in_calendar'],
                'endpoint' => route('pet-social.actions.perform'),
                'payload' => [
                    'action' => 'toggle-event-calendar',
                    'target' => $event['key'],
                    'event_return_tab' => $tab,
                ],
            ],
            'reminder_action' => [
                'label' => $event['reminder_enabled'] ? 'Reminders on' : 'Enable reminders',
                'icon' => $event['reminder_enabled'] ? 'bell-ring' : 'bell',
                'active' => $event['reminder_enabled'],
                'endpoint' => route('pet-social.actions.perform'),
                'payload' => [
                    'action' => 'toggle-event-reminder',
                    'target' => $event['key'],
                    'event_return_tab' => $tab,
                ],
            ],
            'cancel_action' => $registration !== null && ! in_array($registration['status'], ['cancelled', 'declined'], true)
                ? [
                    'label' => 'Cancel registration',
                    'icon' => 'ticket-x',
                    'endpoint' => route('pet-social.actions.perform'),
                    'payload' => [
                        'action' => 'cancel-event-registration',
                        'target' => $event['key'],
                        'event_return_tab' => $tab,
                    ],
                ]
                : null,
            'check_in_action' => $registration !== null && in_array($registration['status'], ['confirmed', 'checked_in'], true)
                ? [
                    'label' => $registration['status'] === 'checked_in' ? 'Checked in' : 'QR check-in',
                    'icon' => $registration['status'] === 'checked_in' ? 'badge-check' : 'qr-code',
                    'active' => $registration['status'] === 'checked_in',
                    'endpoint' => route('pet-social.actions.perform'),
                    'payload' => [
                        'action' => 'check-in-event',
                        'target' => $event['key'],
                        'check_in_method' => 'qr',
                        'event_return_tab' => $tab,
                    ],
                ]
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function organizerTools(array $event, string $tab): array
    {
        return [
            'announcement_action' => route('pet-social.actions.perform'),
            'reschedule_action' => route('pet-social.actions.perform'),
            'cancel_action' => [
                'label' => 'Cancel event',
                'icon' => 'calendar-x',
                'endpoint' => route('pet-social.actions.perform'),
                'payload' => [
                    'action' => 'cancel-event',
                    'target' => $event['key'],
                    'event_return_tab' => $tab,
                    'event_reason' => 'Organizer cancelled the event and notified registered attendees.',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @param  array<string, mixed>|null  $registration
     */
    private function canViewPrivateDetails(array $event, ?array $registration): bool
    {
        if ($event['managed_by_current_user']) {
            return true;
        }

        return $registration !== null
            && in_array($registration['status'], ['payment_required', 'confirmed', 'checked_in'], true);
    }

    /**
     * @param  array<int, array<string, string>>  $seed
     * @return array<int, array<string, string>>
     */
    private function chat(string $event, array $seed): array
    {
        $created = array_map(static fn (array $message): array => [
            'name' => $message['name'],
            'initials' => 'MC',
            'tone' => 'sun',
            'body' => $message['body'],
            'time' => CarbonImmutable::parse($message['created_at'])->format('g:i A'),
        ], $this->state->messages($event));

        return [...$seed, ...$created];
    }

    /**
     * @param  array<int, array<string, string>>  $seed
     * @return array<int, array<string, string>>
     */
    private function announcements(string $event, array $seed): array
    {
        $created = array_map(static fn (array $announcement): array => [
            'title' => $announcement['title'],
            'body' => $announcement['body'],
            'time' => CarbonImmutable::parse($announcement['created_at'])->format('D · g:i A'),
            'icon' => 'megaphone',
        ], $this->state->announcements($event));

        return [...$created, ...$seed];
    }

    /**
     * @param  array<int, array<string, string>>  $seed
     * @return array<int, array<string, string>>
     */
    private function gallery(string $event, array $seed): array
    {
        $created = array_map(static fn (array $photo): array => [
            'src' => $photo['src'],
            'small' => $photo['src'],
            'medium' => $photo['src'],
            'alt' => $photo['alt'],
            'caption' => $photo['caption'],
        ], $this->state->photos($event));

        return [...$created, ...$seed];
    }

    /**
     * @param  array<int, array<string, string>>  $seed
     * @return array<int, array<string, string>>
     */
    private function reviews(string $event, array $seed): array
    {
        $created = array_map(static fn (array $review): array => [
            'name' => 'Mia Carter',
            'initials' => 'MC',
            'tone' => 'sun',
            'rating' => (string) $review['rating'],
            'title' => 'Your verified-attendance review',
            'body' => $review['body'],
            'meta' => 'Verified attendee · '.CarbonImmutable::parse($review['created_at'])->format('M j'),
        ], $this->state->reviews($event));

        return [...$created, ...$seed];
    }

    /**
     * @param  array<int, array<string, string>>  $applications
     * @return array<int, array<string, string>>
     */
    private function applications(string $event, array $applications): array
    {
        return array_map(fn (array $application): array => [
            ...$application,
            'state' => $this->state->applicationStatus($event, $application['key']),
            'status' => Str::headline($this->state->applicationStatus($event, $application['key'])),
        ], $applications);
    }

    /**
     * @param  array<int, array<string, string>>  $waitlist
     * @return array<int, array<string, string>>
     */
    private function waitlist(string $event, array $waitlist): array
    {
        return array_map(fn (array $candidate): array => [
            ...$candidate,
            'state' => $this->state->waitlistStatus($event, $candidate['key']),
            'status' => Str::headline($this->state->waitlistStatus($event, $candidate['key'])),
        ], $waitlist);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function history(string $event): array
    {
        $changes = array_map(static fn (array $change): array => [
            'message' => Str::headline($change['type']).': '.$change['note'],
            'created_at' => $change['created_at'],
        ], $this->state->changes($event));

        return [...$changes, ...$this->state->history($event)];
    }

    /**
     * @param  array<string, string>  $tabOptions
     * @return array<int, array{label: string, href: string, active: bool, icon: string}>
     */
    private function tabs(array $event, string $active, array $tabOptions): array
    {
        $icons = [
            'overview' => 'layout-dashboard',
            'tickets' => 'ticket-check',
            'schedule' => 'list-ordered',
            'attendees' => 'users',
            'pets' => 'paw-print',
            'chat' => 'messages-square',
            'announcements' => 'megaphone',
            'location' => 'map',
            'media' => 'images',
            'rules' => 'shield-check',
            'reviews' => 'star',
            'manage' => 'sliders-horizontal',
        ];

        return array_map(static fn (string $label, string $key): array => [
            'label' => $label,
            'href' => route('pet-social.meetups.show', ['event' => $event['key'], 'tab' => $key]),
            'active' => $active === $key,
            'icon' => $icons[$key],
        ], $tabOptions, array_keys($tabOptions));
    }

    /**
     * @return array<string, string>
     */
    private function tabOptions(bool $managed): array
    {
        return array_filter([
            'overview' => 'Overview',
            'tickets' => 'Registration',
            'schedule' => 'Schedule',
            'attendees' => 'People',
            'pets' => 'Pets',
            'chat' => 'Chat',
            'announcements' => 'Announcements',
            'location' => 'Place',
            'media' => 'Photos',
            'rules' => 'Rules',
            'reviews' => 'Reviews',
            'manage' => $managed ? 'Manage' : null,
        ], static fn (?string $label): bool => $label !== null);
    }

    /**
     * @return array<string, array{label: string, value: string}>
     */
    private function filterOptions(): array
    {
        return [
            'recommended' => ['label' => 'Recommended', 'value' => 'recommended'],
            'walks' => ['label' => 'Walks', 'value' => 'walks'],
            'training' => ['label' => 'Training', 'value' => 'training'],
            'shows' => ['label' => 'Shows', 'value' => 'shows'],
            'adoption' => ['label' => 'Adoption', 'value' => 'adoption'],
            'online' => ['label' => 'Online', 'value' => 'online'],
            'free' => ['label' => 'Free', 'value' => 'free'],
            'interested' => ['label' => 'Saved', 'value' => 'interested'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'soonest' => 'Soonest first',
            'recommended' => 'Best match',
            'closest' => 'Closest first',
            'name' => 'Name',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function viewOptions(): array
    {
        return [
            'list' => 'List',
            'calendar' => 'Calendar',
            'map' => 'Map',
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function matches(array $event, string $query, string $filter): bool
    {
        $filterMatches = match ($filter) {
            'walks' => in_array($event['event_type'], ['group-walk', 'puppy-walk', 'hike'], true),
            'training' => in_array($event['event_type'], ['puppy-training', 'training-course'], true),
            'shows' => $event['event_type'] === 'pet-show',
            'adoption' => $event['event_type'] === 'adoption-day',
            'online' => $event['format'] === 'online',
            'free' => $event['price_minor'] === 0,
            'interested' => $event['interested'],
            default => true,
        };

        if (! $filterMatches || $query === '') {
            return $filterMatches;
        }

        return Str::contains(
            Str::lower(implode(' ', [
                $event['title'],
                $event['short_description'],
                $event['category'],
                $event['organizer'],
                $event['general_location'],
                ...$event['tags'],
            ])),
            Str::lower($query),
        );
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    private function compare(array $left, array $right, string $sort): int
    {
        return match ($sort) {
            'name' => strcasecmp($left['title'], $right['title']),
            'closest' => $this->distanceValue($left['distance']) <=> $this->distanceValue($right['distance']),
            'recommended' => ((int) $right['managed_by_current_user'] <=> (int) $left['managed_by_current_user'])
                ?: strcmp($left['starts_at'], $right['starts_at']),
            default => strcmp($left['starts_at'], $right['starts_at']),
        };
    }

    private function distanceValue(string $distance): float
    {
        return is_numeric(strtok($distance, ' ')) ? (float) strtok($distance, ' ') : 9999.0;
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function applyScheduleChange(array $event): array
    {
        $change = $this->state->changes($event['key'])[0] ?? null;

        if (
            $change === null
            || $change['type'] !== 'rescheduled'
            || $change['date'] === ''
            || $change['time'] === ''
        ) {
            return $event;
        }

        $originalStart = CarbonImmutable::parse($event['starts_at']);
        $originalEnd = CarbonImmutable::parse($event['ends_at']);
        $startsAt = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            $change['date'].' '.$change['time'],
            $originalStart->getTimezone(),
        );

        return [
            ...$event,
            'original_starts_at' => $event['starts_at'],
            'starts_at' => $startsAt->toAtomString(),
            'ends_at' => $startsAt->addSeconds((int) $originalStart->diffInSeconds($originalEnd))->toAtomString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @param  array<int, array<string, string>>  $schedule
     * @return array<int, array<string, string>>
     */
    private function applyScheduleChangeToProgram(array $event, array $schedule): array
    {
        if (! isset($event['original_starts_at'])) {
            return $schedule;
        }

        $originalStart = CarbonImmutable::parse($event['original_starts_at']);
        $newStart = CarbonImmutable::parse($event['starts_at']);
        $offsetMinutes = (int) $originalStart->diffInMinutes($newStart, false);

        return array_map(static function (array $item) use ($offsetMinutes, $originalStart): array {
            if (preg_match('/^\d{1,2}:\d{2} (AM|PM)$/', $item['time']) !== 1) {
                return $item;
            }

            $time = CarbonImmutable::createFromFormat(
                'Y-m-d g:i A',
                $originalStart->format('Y-m-d').' '.$item['time'],
                $originalStart->getTimezone(),
            );

            if ($time === false) {
                return $item;
            }

            return [
                ...$item,
                'time' => $time->addMinutes($offsetMinutes)->format('g:i A'),
            ];
        }, $schedule);
    }

    private function interestedCount(): int
    {
        return count(array_filter(
            $this->catalog->all(),
            fn (array $event): bool => $this->state->isInterested($event['key']),
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @return array<int, array<string, mixed>>
     */
    private function calendarSummary(array $events): array
    {
        return array_map(static fn (array $event): array => [
            'date' => $event['date_label'],
            'title' => $event['title'],
            'time' => $event['time'],
            'href' => route(
                $event['detail_route'] ?? 'pet-social.meetups.show',
                $event['detail_parameters'] ?? ['event' => $event['key']],
            ),
            'status' => $event['status_label'],
        ], array_slice($events, 0, 6));
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @return array<int, array<string, mixed>>
     */
    private function mapSummary(array $events): array
    {
        return array_map(static fn (array $event): array => [
            'title' => $event['title'],
            'place' => $event['general_location'] ?? $event['place'],
            'distance' => $event['distance'],
            'category' => $event['category'],
            'href' => route(
                $event['detail_route'] ?? 'pet-social.meetups.show',
                $event['detail_parameters'] ?? ['event' => $event['key']],
            ),
        ], array_values(array_filter(
            $events,
            static fn (array $event): bool => $event['format'] !== 'online',
        )));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function createdEvents(string $query, string $filter): array
    {
        if (! in_array($filter, ['recommended', 'walks', 'free'], true)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (array $event): array => [
                ...$event,
                'short_description' => $event['description'],
                'general_location' => $event['place'],
                'organizer' => $event['host'],
                'organizer_initials' => $event['host_initials'],
                'organizer_type' => 'owner',
                'starts_at' => $event['datetime'],
                'base_attendees' => 1,
                'status' => 'registration_open',
                'status_label' => 'Registration open',
                'status_tone' => 'safe',
                'format' => $event['format'],
                'format_label' => Str::headline($event['format']),
                'privacy' => $event['privacy'],
                'privacy_label' => Str::headline($event['privacy']),
                'price_minor' => (int) round($event['ticket_price'] * 100),
                'price_label' => $event['ticket_model'] === 'paid'
                    ? '$'.number_format($event['ticket_price'], 2)
                    : 'Free',
                'capacity_label' => $event['capacity'].' places',
                'remaining' => $event['capacity'],
                'verification_label' => null,
                'commercial_label' => null,
                'recommendation_reason' => 'Created by you',
                'managed_by_current_user' => true,
                'registration_status' => null,
                'interested' => false,
                'primary_action' => [
                    'label' => 'Open event',
                    'icon' => 'arrow-up-right',
                    'variant' => 'primary',
                    'href' => route($event['detail_route'], $event['detail_parameters']),
                ],
                'interest_action' => null,
            ], $this->created->meetups()),
            static function (array $event) use ($filter, $query): bool {
                $matchesFilter = match ($filter) {
                    'walks' => Str::contains(Str::lower($event['category']), 'walk'),
                    'free' => $event['price_minor'] === 0,
                    default => true,
                };

                if (! $matchesFilter) {
                    return false;
                }

                if ($query === '') {
                    return true;
                }

                return Str::contains(
                    Str::lower($event['title'].' '.$event['description'].' '.$event['place']),
                    Str::lower($query),
                );
            },
        ));
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'registration_open' => 'Registration open',
            'few_spots' => 'Few places left',
            'waitlist' => 'Waitlist',
            'registration_closed' => 'Registration closed',
            'urgent' => 'Urgent local action',
            'live' => 'Happening now',
            'completed' => 'Completed',
            'rescheduled' => 'Rescheduled',
            'cancelled' => 'Cancelled',
            'archived' => 'Archived',
            default => Str::headline($status),
        };
    }

    private function statusTone(string $status): string
    {
        return match ($status) {
            'urgent', 'cancelled' => 'danger',
            'few_spots', 'rescheduled', 'waitlist' => 'attention',
            'completed', 'archived' => 'surface',
            default => 'safe',
        };
    }

    private function registrationLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'Application pending',
            'waitlisted' => 'On waitlist',
            'payment_required' => 'Payment required',
            'payment_failed' => 'Payment needs retry',
            'confirmed' => 'Registration confirmed',
            'checked_in' => 'Checked in',
            'cancelled' => 'Registration cancelled',
            'declined' => 'Application not approved',
            default => 'Not registered',
        };
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function priceLabel(array $event): string
    {
        return $event['price_minor'] === 0
            ? 'Free'
            : $this->formatMoney($event['price_minor'], $event['currency']);
    }

    private function formatMoney(int $minor, string $currency): string
    {
        if ($minor === 0) {
            return 'Free';
        }

        return '$'.number_format($minor / 100, 2).' '.$currency;
    }
}

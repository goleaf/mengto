<?php

declare(strict_types=1);

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
        private readonly LocaleFormatter $formatter,
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
            'page_title' => __('messages.events_pawcircle_288cf095b2'),
            'active_section' => 'meetups',
            'summary' => [
                'eyebrow' => __('messages.events_and_real_world_plans_87cc63efbb'),
                'title' => __('messages.find_a_gathering_that_fits_you_and_your_pet_fc1ee35b7e'),
                'description' => __('messages.walks_training_shows_shelter_days_volunteer_actions_cele_19ce6770c6'),
                'count' => trans_choice('presentation.events_count', count($events), ['count' => count($events)]),
                'highlights' => [
                    ['label' => __('messages.next_1ff57a29d7'), 'value' => __('messages.thu_jul_30_e977db7bc6'), 'detail' => __('messages.urgent_local_search_dd5c8c3338')],
                    ['label' => __('messages.this_week_8c4eef5ab2'), 'value' => __('messages.5_events_270a2dfe87'), 'detail' => __('messages.online_and_nearby_0589949bbf')],
                    ['label' => __('messages.saved_b5c120b316'), 'value' => (string) $this->interestedCount(), 'detail' => __('messages.events_marked_interested_6f51ab9229')],
                    ['label' => __('messages.timezone_4ceca1d52c'), 'value' => __('messages.pacific_fa3fca02a6'), 'detail' => __('messages.shown_in_local_time_5ff65dd9b9')],
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
                'browse_url' => route('meetups.index'),
                'create_url' => route('compose', ['kind' => 'meetup']),
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
            : __('messages.exact_details_unlock_after_approval_and_any_required_pay_178d911ade');
        $content['location']['revealed_online_link'] = $canViewPrivateDetails
            ? $content['location']['online_link']
            : null;

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => __('presentation.brand_title', ['title' => $event['title']]),
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
            'detail_route' => 'meetups.show',
            'detail_parameters' => ['event' => $event['key']],
            'day' => $this->formatter->weekdayShort($startsAt),
            'date' => $this->formatter->dayNumber($startsAt),
            'date_label' => $this->formatter->weekdayMonthDay($startsAt),
            'date_accessible' => $this->formatter->accessibleDateTime($startsAt),
            'datetime' => $event['starts_at'],
            'time' => $this->formatter->time($startsAt),
            'place' => $event['general_location'],
            'neighborhood' => $event['format'] === 'online'
                ? __('messages.timezone_aware_online_access_89162dea4a')
                : __('messages.exact_entrance_after_confirmation_bbc63d0c75'),
            'attendees' => __('presentation.confirmed_count', ['count' => $event['base_attendees']]),
            'remaining' => $remaining,
            'capacity_label' => $remaining > 0
                ? trans_choice('presentation.places_left', $remaining, ['count' => $remaining])
                : __('messages.waitlist_available_4e67f7386e'),
            'price_label' => $this->priceLabel($event),
            'format_label' => Str::headline($event['format']),
            'privacy_label' => Str::headline($event['privacy']),
            'organizer_type_label' => Str::headline($event['organizer_type']),
            'rsvp' => $registration !== null && in_array($registration['status'], ['confirmed', 'checked_in'], true),
            'registration_status' => $registration['status'] ?? null,
            'interested' => $this->state->isInterested($event['key']),
            'primary_action' => [
                'label' => __('messages.view_event_691f700a56'),
                'icon' => 'arrow-up-right',
                'variant' => 'primary',
                'href' => route('meetups.show', ['event' => $event['key']]),
            ],
            'interest_action' => [
                'label' => $this->state->isInterested($event['key']) ? __('messages.interested_bcd3071318') : __('messages.save_event_2ea84a3cf7'),
                'icon' => $this->state->isInterested($event['key']) ? 'bookmark-check' : 'bookmark',
                'active' => $this->state->isInterested($event['key']),
                'endpoint' => route('actions.perform'),
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
            'eyebrow' => $event['verification_label'] ?? __('presentation.event_type', [
                'type' => Str::headline($event['category']),
            ]),
            'long_description' => $event['description'],
            'format_label' => Str::headline($event['format']),
            'event_type_label' => Str::headline($event['event_type']),
            'status_label' => $this->statusLabel($event['status']),
            'status_tone' => $this->statusTone($event['status']),
            'date_label' => $this->formatter->weekdayMonthDay($startsAt),
            'date_accessible' => $this->formatter->accessibleDateTime($startsAt),
            'time_label' => __('presentation.time_range', [
                'start' => $this->formatter->time($startsAt),
                'end' => $this->formatter->time($endsAt),
            ]),
            'price_label' => $this->priceLabel($event),
            'remaining' => $remaining,
            'capacity_label' => $remaining > 0
                ? trans_choice('presentation.places_left', $remaining, ['count' => $remaining])
                : __('messages.waitlist_available_4e67f7386e'),
            'registration_status' => $registration['status'] ?? null,
            'registration_label' => $this->registrationLabel($registration['status'] ?? null),
            'in_calendar' => $this->state->isInCalendar($event['key']),
            'reminder_enabled' => $this->state->reminderIsEnabled($event['key']),
            'meta' => [
                [
                    'icon' => 'calendar-days',
                    'label' => __('presentation.datetime_timezone', [
                        'date' => $this->formatter->dateTime($startsAt),
                        'timezone' => $event['timezone'],
                    ]),
                    'datetime' => $event['starts_at'],
                    'aria_label' => $this->formatter->accessibleDateTime($startsAt),
                ],
                [
                    'icon' => $event['format'] === 'online' ? 'video' : 'map-pin',
                    'label' => $event['general_location'],
                ],
                ['icon' => 'user-round', 'label' => __('messages.organized_by_23a2f98e95').$event['organizer']],
                [
                    'icon' => $event['privacy'] === 'public' ? 'globe-2' : 'lock-keyhole',
                    'label' => __('presentation.event_type', ['type' => Str::headline($event['privacy'])]),
                ],
            ],
            'stats' => [
                [
                    'label' => __('messages.confirmed_fe00b67b6d'),
                    'value' => (string) $event['base_attendees'],
                    'detail' => $remaining > 0
                        ? trans_choice('presentation.places_remain', $remaining, ['count' => $remaining])
                        : __('messages.waitlist_open_466525cae5'),
                ],
                [
                    'label' => __('messages.duration_4fc52a3c4c'),
                    'value' => $this->formatter->relative($startsAt, $endsAt),
                    'detail' => __('presentation.format_type', ['type' => $event['activity_level']]),
                ],
                [
                    'label' => __('messages.ticket_567a8b5f8f'),
                    'value' => $this->priceLabel($event),
                    'detail' => __('presentation.registration_type', [
                        'type' => Str::headline($event['registration_policy']),
                    ]),
                ],
                ['label' => __('messages.language_a4fe65264e'), 'value' => $event['language'], 'detail' => __('messages.event_and_materials_796d9206fc')],
            ],
            'primary_action' => $this->primaryDetailAction($event, $registration),
            'secondary_actions' => [
                [
                    'label' => $this->state->isInCalendar($event['key']) ? __('messages.in_calendar_d98da7757d') : __('messages.add_to_calendar_d0efffa65e'),
                    'icon' => $this->state->isInCalendar($event['key']) ? 'calendar-check' : 'calendar-plus',
                    'active' => $this->state->isInCalendar($event['key']),
                    'endpoint' => route('actions.perform'),
                    'payload' => [
                        'action' => 'toggle-event-calendar',
                        'target' => $event['key'],
                    ],
                ],
                [
                    'label' => __('messages.share_29887a5ff9'),
                    'icon' => 'send',
                    'href' => route('share.show', ['target' => $event['key']]),
                ],
                [
                    'label' => __('messages.report_b6ce788d97'),
                    'icon' => 'flag',
                    'href' => route('compose', [
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
                'label' => $event['registration_policy'] === 'approval' ? __('messages.apply_to_attend_b4fcde563f') : __('messages.register_bb7234ec12'),
                'icon' => 'ticket-check',
                'variant' => 'primary',
                'href' => route('meetups.show', ['event' => $event['key'], 'tab' => 'tickets']),
            ];
        }

        if (in_array($registration['status'], ['payment_required', 'payment_failed'], true)) {
            return [
                'label' => $registration['status'] === 'payment_failed' ? __('messages.retry_payment_4967348989') : __('messages.complete_payment_c030632f07'),
                'icon' => 'credit-card',
                'variant' => 'primary',
                'href' => route('meetups.show', ['event' => $event['key'], 'tab' => 'tickets']),
            ];
        }

        return [
            'label' => $this->registrationLabel($registration['status']),
            'icon' => $registration['status'] === 'checked_in' ? 'badge-check' : 'ticket-check',
            'variant' => 'paper',
            'href' => route('meetups.show', ['event' => $event['key'], 'tab' => 'tickets']),
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
        $presentedRegistration = $registration === null
            ? null
            : [
                ...$registration,
                'ticket_type_label' => Str::headline(
                    (string) $registration['ticket_type'],
                ),
            ];

        return [
            'status' => $registration['status'] ?? null,
            'status_label' => $this->registrationLabel($registration['status'] ?? null),
            'registration' => $presentedRegistration,
            'travel_status' => $this->state->travelStatus($event['key']),
            'ticket_options' => array_map(
                fn (array $ticket): array => [
                    ...$ticket,
                    'price_label' => $this->formatMoney((int) $ticket['price_minor'], $ticket['currency']),
                ],
                $ticketOptions,
            ),
            'pets' => [
                'scout' => __('messages.scout_border_collie_ef16e9718e'),
                'nori' => __('messages.nori_domestic_shorthair_e6993a1200'),
                'owner-only' => __('messages.attend_without_a_pet_e8895fcb0b'),
            ],
            'can_register_pet' => (bool) $event['pets_allowed'],
            'terms' => $event['price_minor'] > 0
                ? __('messages.prototype_checkout_only_no_card_details_are_collected_ca_b3c60255f3')
                : __('messages.cancelling_releases_this_place_to_the_next_eligible_pers_242f72a279'),
            'register_action' => route('actions.perform'),
            'register_payload' => [
                'action' => 'register-event',
                'target' => $event['key'],
                'event_return_tab' => $tab,
            ],
            'calendar_action' => [
                'label' => $event['in_calendar'] ? __('messages.remove_from_calendar_3f92fdcba2') : __('messages.add_to_calendar_d0efffa65e'),
                'icon' => $event['in_calendar'] ? 'calendar-x' : 'calendar-plus',
                'active' => $event['in_calendar'],
                'endpoint' => route('actions.perform'),
                'payload' => [
                    'action' => 'toggle-event-calendar',
                    'target' => $event['key'],
                    'event_return_tab' => $tab,
                ],
            ],
            'reminder_action' => [
                'label' => $event['reminder_enabled'] ? __('messages.reminders_on_95be8e8e05') : __('messages.enable_reminders_8f87ace1ec'),
                'icon' => $event['reminder_enabled'] ? 'bell-ring' : 'bell',
                'active' => $event['reminder_enabled'],
                'endpoint' => route('actions.perform'),
                'payload' => [
                    'action' => 'toggle-event-reminder',
                    'target' => $event['key'],
                    'event_return_tab' => $tab,
                ],
            ],
            'cancel_action' => $registration !== null && ! in_array($registration['status'], ['cancelled', 'declined'], true)
                ? [
                    'label' => __('messages.cancel_registration_52c206b7c7'),
                    'icon' => 'ticket-x',
                    'endpoint' => route('actions.perform'),
                    'payload' => [
                        'action' => 'cancel-event-registration',
                        'target' => $event['key'],
                        'event_return_tab' => $tab,
                    ],
                ]
                : null,
            'check_in_action' => $registration !== null && in_array($registration['status'], ['confirmed', 'checked_in'], true)
                ? [
                    'label' => $registration['status'] === 'checked_in' ? __('messages.checked_in_66477affd2') : __('messages.qr_check_in_93e5dbb8e6'),
                    'icon' => $registration['status'] === 'checked_in' ? 'badge-check' : 'qr-code',
                    'active' => $registration['status'] === 'checked_in',
                    'endpoint' => route('actions.perform'),
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
            'announcement_action' => route('actions.perform'),
            'reschedule_action' => route('actions.perform'),
            'cancel_action' => [
                'label' => __('messages.cancel_event_e437654aaf'),
                'icon' => 'calendar-x',
                'endpoint' => route('actions.perform'),
                'payload' => [
                    'action' => 'cancel-event',
                    'target' => $event['key'],
                    'event_return_tab' => $tab,
                    'event_reason' => __('messages.organizer_cancelled_the_event_and_notified_registered_at_f39f49b88e'),
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
        $created = array_map(fn (array $message): array => [
            'name' => $message['name'],
            'initials' => 'MC',
            'tone' => 'sun',
            'body' => $message['body'],
            'time' => $this->formatter->time(CarbonImmutable::parse($message['created_at'])),
        ], $this->state->messages($event));

        return [...$seed, ...$created];
    }

    /**
     * @param  array<int, array<string, string>>  $seed
     * @return array<int, array<string, string>>
     */
    private function announcements(string $event, array $seed): array
    {
        $created = array_map(fn (array $announcement): array => [
            'title' => $announcement['title'],
            'body' => $announcement['body'],
            'time' => __('presentation.weekday_time', [
                'weekday' => $this->formatter->weekdayShort(CarbonImmutable::parse($announcement['created_at'])),
                'time' => $this->formatter->time(CarbonImmutable::parse($announcement['created_at'])),
            ]),
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
        $created = array_map(fn (array $review): array => [
            'name' => __('messages.mia_carter_0e5b29cc3b'),
            'initials' => 'MC',
            'tone' => 'sun',
            'rating' => (string) $review['rating'],
            'title' => __('messages.your_verified_attendance_review_a35d3415d1'),
            'body' => $review['body'],
            'meta' => __('presentation.verified_attendee_date', [
                'date' => $this->formatter->monthDay(CarbonImmutable::parse($review['created_at'])),
            ]),
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

        return array_map(fn (array $item): array => [
            ...$item,
            'created_at_label' => $this->formatter->dateTime(CarbonImmutable::parse($item['created_at'])),
        ], [...$changes, ...$this->state->history($event)]);
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
            'href' => route('meetups.show', ['event' => $event['key'], 'tab' => $key]),
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
            'overview' => __('messages.overview_d4b1ea5708'),
            'tickets' => __('messages.registration_c793e0d9a1'),
            'schedule' => __('messages.schedule_f4830a1dae'),
            'attendees' => __('messages.people_7db2089705'),
            'pets' => __('messages.pets_7dc1cd7eaf'),
            'chat' => __('messages.chat_460b3a7da0'),
            'announcements' => __('messages.announcements_fe02680f24'),
            'location' => __('messages.place_e9463dccf0'),
            'media' => __('messages.photos_5e3147ab51'),
            'rules' => __('messages.rules_4228aeb07c'),
            'reviews' => __('messages.reviews_84cb7871b7'),
            'manage' => $managed ? __('messages.manage_5a23444828') : null,
        ], static fn (?string $label): bool => $label !== null);
    }

    /**
     * @return array<string, array{label: string, value: string}>
     */
    private function filterOptions(): array
    {
        return [
            'recommended' => ['label' => __('messages.recommended_d70604e843'), 'value' => 'recommended'],
            'walks' => ['label' => __('messages.walks_22e4ca854b'), 'value' => 'walks'],
            'training' => ['label' => __('messages.training_36a798e3f3'), 'value' => 'training'],
            'shows' => ['label' => __('messages.shows_e714ae21d3'), 'value' => 'shows'],
            'adoption' => ['label' => __('messages.adoption_9b33128339'), 'value' => 'adoption'],
            'online' => ['label' => __('messages.online_0d21bd5202'), 'value' => 'online'],
            'free' => ['label' => __('messages.free_f411a1fb62'), 'value' => 'free'],
            'interested' => ['label' => __('messages.saved_b5c120b316'), 'value' => 'interested'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'soonest' => __('messages.soonest_first_482c320bda'),
            'recommended' => __('messages.best_match_d83ab68f74'),
            'closest' => __('messages.closest_first_f178d8be90'),
            'name' => __('messages.name_dcd1d5223f'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function viewOptions(): array
    {
        return [
            'list' => __('messages.list_6f202f54a7'),
            'calendar' => __('messages.calendar_d5d0a30b51'),
            'map' => __('messages.map_be176b0015'),
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

        return array_map(function (array $item) use ($offsetMinutes, $originalStart): array {
            if (preg_match('/^\d{1,2}:\d{2} (AM|PM)$/', $item['time']) !== 1) {
                return $item;
            }

            $time = CarbonImmutable::createFromFormat(
                'Y-m-d g:i A',
                $originalStart->format('Y-m-d').' '.$item['time'],
                $originalStart->getTimezone(),
            );

            return [
                ...$item,
                'time' => $this->formatter->time($time->addMinutes($offsetMinutes)),
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
                $event['detail_route'] ?? 'meetups.show',
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
                $event['detail_route'] ?? 'meetups.show',
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
                'status_label' => __('messages.registration_open_86babcde8a'),
                'status_tone' => 'safe',
                'format' => $event['format'],
                'format_label' => Str::headline($event['format']),
                'privacy' => $event['privacy'],
                'privacy_label' => Str::headline($event['privacy']),
                'price_minor' => (int) round($event['ticket_price'] * 100),
                'price_label' => $event['ticket_model'] === 'paid'
                    ? $this->formatter->currency($event['ticket_price'], 'USD')
                    : __('presentation.free'),
                'capacity_label' => trans_choice('presentation.places_count', $event['capacity'], [
                    'count' => $event['capacity'],
                ]),
                'remaining' => $event['capacity'],
                'verification_label' => null,
                'commercial_label' => null,
                'recommendation_reason' => __('messages.created_by_you_39467b6ea2'),
                'managed_by_current_user' => true,
                'registration_status' => null,
                'interested' => false,
                'primary_action' => [
                    'label' => __('messages.open_event_cc653b1ecb'),
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
            'registration_open' => __('messages.registration_open_86babcde8a'),
            'few_spots' => __('messages.few_places_left_b7f0c7c7a5'),
            'waitlist' => __('messages.waitlist_ec08d977c6'),
            'registration_closed' => __('messages.registration_closed_832cf70d8e'),
            'urgent' => __('messages.urgent_local_action_62ab4f273a'),
            'live' => __('messages.happening_now_00e5738136'),
            'completed' => __('messages.completed_22a970d2e5'),
            'rescheduled' => __('messages.rescheduled_1930debae7'),
            'cancelled' => __('messages.cancelled_d353a99eb4'),
            'archived' => __('messages.archived_bdb86505f8'),
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
            'pending' => __('messages.application_pending_60bb128855'),
            'waitlisted' => __('messages.on_waitlist_94b64b697b'),
            'payment_required' => __('messages.payment_required_9b7e0bd8dc'),
            'payment_failed' => __('messages.payment_needs_retry_7a1c2b91ec'),
            'confirmed' => __('messages.registration_confirmed_a2652e51d7'),
            'checked_in' => __('messages.checked_in_66477affd2'),
            'cancelled' => __('messages.registration_cancelled_49d0544142'),
            'declined' => __('messages.application_not_approved_f76b41c65f'),
            default => __('messages.not_registered_ca374c23ed'),
        };
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function priceLabel(array $event): string
    {
        return $event['price_minor'] === 0
            ? __('presentation.free')
            : $this->formatMoney($event['price_minor'], $event['currency']);
    }

    private function formatMoney(int $minor, string $currency): string
    {
        if ($minor === 0) {
            return __('presentation.free');
        }

        return $this->formatter->currency($minor / 100, $currency);
    }
}

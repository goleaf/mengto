<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\EventCatalog;
use App\Services\EventState;
use Illuminate\Validation\ValidationException;

final class PerformEventAction
{
    private const ACTIONS = [
        'toggle-event-interest',
        'toggle-event-calendar',
        'toggle-event-reminder',
    ];

    public function __construct(
        private readonly EventCatalog $events,
        private readonly EventState $state,
    ) {}

    public function supports(string $action): bool
    {
        return in_array($action, self::ACTIONS, true);
    }

    /**
     * The legacy endpoint is intentionally limited to personal UI preferences.
     * Authoritative event mutations are handled by the ForumEvent actions.
     *
     * @param  array<string, mixed>  $data
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    public function handle(array $data): array
    {
        return match ((string) $data['action']) {
            'toggle-event-interest' => $this->toggleInterest($data),
            'toggle-event-calendar' => $this->toggleCalendar($data),
            'toggle-event-reminder' => $this->toggleReminder($data),
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
    private function toggleCalendar(array $data): array
    {
        $event = $this->requireEvent($data);
        $active = $this->state->toggleCalendar($event['key']);

        return $this->result(
            $active
                ? __('messages.event_added_to_your_calendar_d7defae028')
                : __('messages.event_removed_from_your_calendar_5bde4e038c'),
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
            $active
                ? __('messages.event_reminders_enabled_b95d6adbd8')
                : __('messages.event_reminders_paused_61da156d54'),
            $data,
        );
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
     * @return array{message: string, route: string|null, parameters?: array<string, string>}
     */
    private function result(string $message, array $data): array
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
            ],
        ];
    }
}

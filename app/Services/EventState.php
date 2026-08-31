<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Stores personal, non-authoritative event preferences only.
 *
 * Registration, attendance, waitlist, payment, messaging, moderation, and
 * lifecycle changes belong to the canonical ForumEvent domain.
 */
final class EventState
{
    private const STATE_NAMESPACE = 'events.state.v1';

    public function __construct(private readonly PersistentStateStore $states) {}

    public function toggleInterest(string $event): bool
    {
        return $this->togglePreference('interested', $event);
    }

    public function isInterested(string $event): bool
    {
        return $this->isPreferenceEnabled('interested', $event);
    }

    public function toggleCalendar(string $event): bool
    {
        return $this->togglePreference('calendar', $event);
    }

    public function isInCalendar(string $event): bool
    {
        return $this->isPreferenceEnabled('calendar', $event);
    }

    public function toggleReminder(string $event): bool
    {
        return $this->togglePreference('reminders', $event);
    }

    public function reminderIsEnabled(string $event): bool
    {
        return $this->isPreferenceEnabled('reminders', $event);
    }

    private function togglePreference(string $collection, string $event): bool
    {
        $state = $this->state();
        $active = ! (bool) data_get($state, $collection.'.'.$event, false);
        data_set($state, $collection.'.'.$event, $active);
        $this->states->put(self::STATE_NAMESPACE, $state);

        return $active;
    }

    private function isPreferenceEnabled(string $collection, string $event): bool
    {
        return (bool) data_get($this->state(), $collection.'.'.$event, false);
    }

    /** @return array<string, mixed> */
    private function state(): array
    {
        return $this->states->get(self::STATE_NAMESPACE);
    }
}

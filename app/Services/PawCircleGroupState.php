<?php

namespace App\Services;

use Illuminate\Contracts\Session\Session;

final class PawCircleGroupState
{
    private const SESSION_KEY = 'paw-circle.groups.v1';

    public function __construct(private readonly Session $session) {}

    public function membership(string $group): ?string
    {
        $status = $this->state()['memberships'][$group] ?? null;

        return $status === 'none' ? null : $status;
    }

    public function join(string $group, string $privacy): string
    {
        $state = $this->state();
        $status = $privacy === 'public' ? 'joined' : 'pending';
        $state['memberships'][$group] = $status;
        $this->store($state);

        return $status;
    }

    public function cancelRequest(string $group): bool
    {
        if ($this->membership($group) !== 'pending') {
            return false;
        }

        return $this->clearMembership($group);
    }

    public function leave(string $group): bool
    {
        if ($this->membership($group) !== 'joined') {
            return false;
        }

        return $this->clearMembership($group);
    }

    public function notificationLevel(string $group): string
    {
        return $this->state()['notifications'][$group] ?? 'important';
    }

    public function setNotificationLevel(string $group, string $level): bool
    {
        if ($this->membership($group) !== 'joined') {
            return false;
        }

        $state = $this->state();
        $state['notifications'][$group] = $level;
        $this->store($state);

        return true;
    }

    public function vote(string $group, string $poll, string $option): bool
    {
        if ($this->membership($group) !== 'joined') {
            return false;
        }

        $state = $this->state();
        $state['poll_votes'][$group][$poll] = $option;
        $this->store($state);

        return true;
    }

    public function pollVote(string $group, string $poll): ?string
    {
        return $this->state()['poll_votes'][$group][$poll] ?? null;
    }

    public function dismissRecommendation(string $group): void
    {
        $state = $this->state();
        $state['dismissed'][] = $group;
        $state['dismissed'] = array_values(array_unique($state['dismissed']));
        $state['last_dismissed'] = $group;
        $this->store($state);
    }

    public function recommendationIsDismissed(string $group): bool
    {
        return in_array($group, $this->state()['dismissed'], true);
    }

    public function lastDismissed(): ?string
    {
        return $this->state()['last_dismissed'];
    }

    public function undoRecommendationDismissal(string $group): bool
    {
        if (! $this->recommendationIsDismissed($group)) {
            return false;
        }

        $state = $this->state();
        $state['dismissed'] = array_values(array_filter(
            $state['dismissed'],
            static fn (string $dismissed): bool => $dismissed !== $group,
        ));
        $state['last_dismissed'] = null;
        $this->store($state);

        return true;
    }

    /**
     * @param  array{target: string, reason: string, body: string, created_at: string}  $report
     */
    public function addReport(array $report): void
    {
        $state = $this->state();
        array_unshift($state['reports'], $report);
        $state['reports'] = array_slice($state['reports'], 0, 20);
        $this->store($state);
    }

    /**
     * @return array<int, array{target: string, reason: string, body: string, created_at: string}>
     */
    public function reports(): array
    {
        return $this->state()['reports'];
    }

    private function clearMembership(string $group): bool
    {
        $state = $this->state();
        $state['memberships'][$group] = 'none';
        unset($state['notifications'][$group]);
        $this->store($state);

        return true;
    }

    /**
     * @return array{
     *     memberships: array<string, string>,
     *     notifications: array<string, string>,
     *     poll_votes: array<string, array<string, string>>,
     *     dismissed: array<int, string>,
     *     last_dismissed: string|null,
     *     reports: array<int, array{target: string, reason: string, body: string, created_at: string}>
     * }
     */
    private function state(): array
    {
        $stored = $this->session->get(self::SESSION_KEY, []);

        return [
            'memberships' => [
                'apartment-pets' => 'joined',
                'trail-tails' => 'joined',
                'cat-people' => 'pending',
                ...($stored['memberships'] ?? []),
            ],
            'notifications' => [
                'apartment-pets' => 'important',
                'trail-tails' => 'events',
                ...($stored['notifications'] ?? []),
            ],
            'poll_votes' => $stored['poll_votes'] ?? [],
            'dismissed' => $stored['dismissed'] ?? [],
            'last_dismissed' => $stored['last_dismissed'] ?? null,
            'reports' => $stored['reports'] ?? [],
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

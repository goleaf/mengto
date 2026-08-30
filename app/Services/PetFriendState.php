<?php

declare(strict_types=1);

namespace App\Services;

final class PetFriendState
{
    private const STATE_NAMESPACE = 'pet-friends.state.v1';

    public function __construct(private readonly PersistentStateStore $states) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function relationships(): array
    {
        $stored = $this->state()['relationships'] ?? [];

        return [
            ...$this->relationshipDefaults(),
            ...$stored,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function relationship(string $first, string $second): ?array
    {
        return $this->relationships()[$this->pairKey($first, $second)] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forPet(string $pet): array
    {
        return array_values(array_filter(
            $this->relationships(),
            static fn (array $relationship): bool => in_array(
                $pet,
                [$relationship['first'], $relationship['second']],
                true,
            ),
        ));
    }

    /**
     * @param  array{intent: string, message: string, met_at: string, share_area: bool}  $details
     */
    public function sendRequest(string $source, string $target, array $details): bool
    {
        $current = $this->relationship($source, $target);

        if ($current !== null && in_array($current['status'], ['pending', 'accepted', 'paused', 'blocked'], true)) {
            return false;
        }

        $state = $this->state();
        $key = $this->pairKey($source, $target);
        $state['relationships'][$key] = [
            'key' => $key,
            'first' => min($source, $target),
            'second' => max($source, $target),
            'requester' => $source,
            'recipient' => $target,
            'status' => 'pending',
            'intents' => [$details['intent']],
            'message' => $details['message'],
            'met_at' => $details['met_at'],
            'share_area' => $details['share_area'],
            'requested_at' => now()->toAtomString(),
            'accepted_at' => '',
            'last_activity' => __('messages.request_sent_just_now'),
        ];
        $this->store($state);

        return true;
    }

    public function cancelRequest(string $source, string $target): bool
    {
        $relationship = $this->relationship($source, $target);

        if (
            $relationship === null
            || $relationship['status'] !== 'pending'
            || $relationship['requester'] !== $source
        ) {
            return false;
        }

        return $this->setStatus($source, $target, 'removed', __('messages.request_cancelled'));
    }

    public function resolveRequest(string $source, string $target, string $status): bool
    {
        $relationship = $this->relationship($source, $target);

        if (
            $relationship === null
            || $relationship['status'] !== 'pending'
            || $relationship['recipient'] !== $source
            || ! in_array($status, ['accepted', 'declined'], true)
        ) {
            return false;
        }

        $state = $this->state();
        $key = $this->pairKey($source, $target);
        $state['relationships'][$key] = [
            ...$relationship,
            'status' => $status,
            'accepted_at' => $status === 'accepted' ? now()->toAtomString() : '',
            'last_activity' => $status === 'accepted' ? __('messages.friends_since_today') : __('messages.request_declined'),
        ];
        $this->store($state);

        return true;
    }

    public function togglePause(string $source, string $target): ?string
    {
        $relationship = $this->relationship($source, $target);

        if ($relationship === null || ! in_array($relationship['status'], ['accepted', 'paused'], true)) {
            return null;
        }

        $status = $relationship['status'] === 'accepted' ? 'paused' : 'accepted';
        $activity = $status === 'paused' ? __('messages.friendship_paused') : __('messages.friendship_restored');
        $this->setStatus($source, $target, $status, $activity);

        return $status;
    }

    public function removeFriendship(string $source, string $target): bool
    {
        $relationship = $this->relationship($source, $target);

        if ($relationship === null || ! in_array($relationship['status'], ['accepted', 'paused'], true)) {
            return false;
        }

        return $this->setStatus($source, $target, 'removed', __('messages.friendship_removed'));
    }

    public function setBlocked(string $source, string $target, bool $blocked): void
    {
        $relationship = $this->relationship($source, $target);
        $state = $this->state();
        $key = $this->pairKey($source, $target);

        $state['relationships'][$key] = [
            ...($relationship ?? [
                'key' => $key,
                'first' => min($source, $target),
                'second' => max($source, $target),
                'requester' => $source,
                'recipient' => $target,
                'intents' => ['friend'],
                'message' => '',
                'met_at' => '',
                'share_area' => false,
                'requested_at' => '',
                'accepted_at' => '',
            ]),
            'status' => $blocked ? 'blocked' : 'removed',
            'last_activity' => $blocked ? __('messages.profile_blocked') : __('messages.block_removed'),
        ];
        $state['last_blocked'][$source] = $blocked ? $target : null;
        $this->store($state);
    }

    public function lastBlocked(string $source): ?string
    {
        return $this->state()['last_blocked'][$source] ?? null;
    }

    public function dismissRecommendation(string $source, string $target): void
    {
        $state = $this->state();
        $state['dismissed'][$source] ??= [];
        $state['dismissed'][$source][] = $target;
        $state['dismissed'][$source] = array_values(array_unique($state['dismissed'][$source]));
        $state['last_dismissed'][$source] = $target;
        $this->store($state);
    }

    public function recommendationIsDismissed(string $source, string $target): bool
    {
        return in_array($target, $this->state()['dismissed'][$source] ?? [], true);
    }

    public function lastDismissed(string $source): ?string
    {
        return $this->state()['last_dismissed'][$source] ?? null;
    }

    public function undoRecommendationDismissal(string $source, string $target): bool
    {
        if (! $this->recommendationIsDismissed($source, $target)) {
            return false;
        }

        $state = $this->state();
        $state['dismissed'][$source] = array_values(array_filter(
            $state['dismissed'][$source] ?? [],
            static fn (string $dismissed): bool => $dismissed !== $target,
        ));
        $state['last_dismissed'][$source] = null;
        $this->store($state);

        return true;
    }

    public function otherPet(array $relationship, string $source): string
    {
        return $relationship['first'] === $source
            ? (string) $relationship['second']
            : (string) $relationship['first'];
    }

    public function pairKey(string $first, string $second): string
    {
        $pets = [$first, $second];
        sort($pets);

        return implode('--', $pets);
    }

    private function setStatus(string $source, string $target, string $status, string $activity): bool
    {
        $relationship = $this->relationship($source, $target);

        if ($relationship === null) {
            return false;
        }

        $state = $this->state();
        $state['relationships'][$this->pairKey($source, $target)] = [
            ...$relationship,
            'status' => $status,
            'last_activity' => $activity,
        ];
        $this->store($state);

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    private function state(): array
    {
        $state = $this->states->get(self::STATE_NAMESPACE);

        return [
            'relationships' => $state['relationships'] ?? [],
            'dismissed' => $state['dismissed'] ?? [],
            'last_dismissed' => $state['last_dismissed'] ?? [],
            'last_blocked' => $state['last_blocked'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function store(array $state): void
    {
        $this->states->put(self::STATE_NAMESPACE, $state);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function relationshipDefaults(): array
    {
        $defaults = [
            $this->defaultRelationship(
                'pet-scout',
                'pet-mochi',
                'pet-scout',
                'pet-mochi',
                'accepted',
                ['walk', 'neighbor'],
                __('messages.we_met_through_ari_after_a_calm_fields_park_walk'),
                __('messages.fields_park'),
                '2025-09-14T10:00:00-07:00',
                __('messages.walked_together_3_days_ago'),
            ),
            $this->defaultRelationship(
                'pet-nori',
                'pet-pip',
                'pet-nori',
                'pet-pip',
                'accepted',
                ['play', 'neighbor'],
                __('messages.quiet_indoor_friends_through_lena_and_mia'),
                __('messages.kerns_neighborhood'),
                '2026-02-08T11:30:00-08:00',
                __('messages.shared_a_photo_2_weeks_ago'),
            ),
            $this->defaultRelationship(
                'pet-juniper',
                'pet-scout',
                'pet-juniper',
                'pet-scout',
                'pending',
                ['walk', 'training'],
                __('messages.we_met_near_the_river_trail_juniper_does_best_with_a_parallel_first_walk'),
                __('messages.sellwood_riverfront'),
                '',
                __('messages.requested_yesterday'),
            ),
            $this->defaultRelationship(
                'pet-scout',
                'pet-luna-labrador',
                'pet-scout',
                'pet-luna-labrador',
                'pending',
                ['walk', 'play'],
                __('messages.scout_and_luna_have_similar_energy_we_could_start_with_a_leashed_park_loop'),
                __('messages.wallace_park'),
                '',
                __('messages.requested_4_days_ago'),
            ),
            $this->defaultRelationship(
                'pet-olive-rabbit',
                'pet-nori',
                'pet-olive-rabbit',
                'pet-nori',
                'pending',
                ['neighbor'],
                __('messages.we_share_quiet_indoor_enrichment_ideas_any_visit_would_use_separate_rooms'),
                __('messages.sellwood_neighbors'),
                '',
                __('messages.requested_2_days_ago'),
            ),
        ];

        return array_column($defaults, null, 'key');
    }

    /**
     * @param  array<int, string>  $intents
     * @return array<string, mixed>
     */
    private function defaultRelationship(
        string $first,
        string $second,
        string $requester,
        string $recipient,
        string $status,
        array $intents,
        string $message,
        string $metAt,
        string $acceptedAt,
        string $lastActivity,
    ): array {
        $key = $this->pairKey($first, $second);

        return [
            'key' => $key,
            'first' => min($first, $second),
            'second' => max($first, $second),
            'requester' => $requester,
            'recipient' => $recipient,
            'status' => $status,
            'intents' => $intents,
            'message' => $message,
            'met_at' => $metAt,
            'share_area' => true,
            'requested_at' => '2026-07-25T10:00:00-07:00',
            'accepted_at' => $acceptedAt,
            'last_activity' => $lastActivity,
        ];
    }
}

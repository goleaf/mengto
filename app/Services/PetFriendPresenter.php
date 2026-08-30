<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PetFriendPresenter
{
    public function __construct(
        private readonly PetFriendCatalog $catalog,
        private readonly PetFriendState $friendState,
        private readonly PrototypeState $state,
        private readonly ProfilePresenter $profiles,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function page(
        string $pet = 'scout',
        string $tab = 'friends',
        string $intent = 'all',
        string $sort = 'compatibility',
        string $query = '',
    ): array {
        $source = $this->source($pet);
        $tab = in_array($tab, ['friends', 'requests', 'discover', 'walks'], true) ? $tab : 'friends';
        $intent = array_key_exists($intent, $this->intentOptions()) ? $intent : 'all';
        $sort = array_key_exists($sort, $this->sortOptions()) ? $sort : 'compatibility';
        $query = trim($query);
        $summary = $this->summary($source['id']);
        $items = $this->items($source['id'], $tab, $intent, $sort, $query);

        return [
            'owner' => $this->profiles->owner(),
            'page_title' => __('presentation.brand_title', [
                'title' => __('presentation.pet_friends_for', ['pet' => $source['name']]),
            ]),
            'active_section' => 'circle',
            'summary' => [
                'eyebrow' => __('messages.owner_managed_pet_friendships'),
                'title' => $source['name'].'’s social circle',
                'description' => __('messages.review_every_connection_as_an_owner_keep_exact_locations_private_and_choose_the_right_first_meeting_format'),
                'count' => trans_choice('presentation.results_count', count($items), ['count' => count($items)]),
                'stats' => $summary,
            ],
            'friend_center' => [
                'source' => $source,
                'pet_switcher' => $this->petSwitcher($source['id'], $tab, $intent, $sort, $query),
                'tab' => $tab,
                'tabs' => $this->tabs($source['id'], $tab, $intent, $sort, $query, $summary),
                'intent' => $intent,
                'intent_options' => $this->intentOptions(),
                'sort' => $sort,
                'sort_options' => $this->sortOptions(),
                'query' => $query,
                'items' => $items,
                'empty' => $this->emptyState($tab, $source['name']),
                'endpoint' => route('actions.perform'),
                'browse_url' => route('pet-friends.index'),
                'last_dismissed' => $this->lastDismissed($source['id'], $tab, $intent, $sort, $query),
                'last_blocked' => $this->lastBlocked($source['id'], $tab, $intent, $sort, $query),
                'safety_note' => [
                    'title' => __('messages.owners_make_every_decision'),
                    'description' => __('messages.compatibility_notes_are_conversation_starters_not_a_safety_guarantee_use_a_public_place_and_a_calm_owner_led_first_introduction'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function source(string $pet): array
    {
        $source = $this->catalog->find('pet-'.$pet);

        return $source ?? $this->catalog->find('pet-scout');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function items(
        string $source,
        string $tab,
        string $intent,
        string $sort,
        string $query,
    ): array {
        $items = [];

        foreach ($this->catalog->candidates($source) as $candidate) {
            $target = (string) $candidate['id'];
            $relationship = $this->friendState->relationship($source, $target);

            if (! $this->belongsToTab($tab, $source, $target, $relationship)) {
                continue;
            }

            $item = $this->decorate($source, $candidate, $relationship, $tab, $intent, $sort, $query);

            if (! $this->matches($item, $intent, $query)) {
                continue;
            }

            $items[] = $item;
        }

        usort($items, fn (array $left, array $right): int => $this->compare($left, $right, $sort));

        return $items;
    }

    /**
     * @param  array<string, mixed>|null  $relationship
     */
    private function belongsToTab(
        string $tab,
        string $source,
        string $target,
        ?array $relationship,
    ): bool {
        if ($this->state->isActive('blocks', $target)) {
            return false;
        }

        return match ($tab) {
            'friends' => $relationship !== null
                && in_array($relationship['status'], ['accepted', 'paused'], true),
            'requests' => $relationship !== null && $relationship['status'] === 'pending',
            'walks' => $relationship !== null
                && $relationship['status'] === 'accepted'
                && in_array('walk', $relationship['intents'], true),
            default => ($relationship === null || in_array($relationship['status'], ['removed', 'declined'], true))
                && ! $this->friendState->recommendationIsDismissed($source, $target),
        };
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @param  array<string, mixed>|null  $relationship
     * @return array<string, mixed>
     */
    private function decorate(
        string $source,
        array $candidate,
        ?array $relationship,
        string $tab,
        string $intent,
        string $sort,
        string $query,
    ): array {
        $target = (string) $candidate['id'];
        $compatibility = $this->catalog->compatibility($source, $target);
        $direction = $relationship === null
            ? null
            : ($relationship['requester'] === $source ? 'outgoing' : 'incoming');
        $returnState = $this->returnState($tab, $intent, $sort, $query);
        $status = $this->status($relationship, $direction);
        $walkHref = $this->walkHref($candidate);

        return [
            ...$candidate,
            'key' => $this->friendState->pairKey($source, $target),
            'href' => route($candidate['route_name'], $candidate['route_parameters']),
            'type_label' => $candidate['breed'].' · '.$candidate['age'],
            'followers' => __('messages.managed_by').$candidate['owner'],
            'context' => $relationship['last_activity'] ?? __('messages.suggested_from_shared_routines'),
            'relationship' => $relationship,
            'direction' => $direction,
            'status' => $status,
            'compatibility' => [
                'reason' => $compatibility['reason'],
                'shared' => $compatibility['shared'],
                'cautions' => $compatibility['cautions'],
            ],
            'compatibility_score' => $compatibility['score'],
            'intents' => array_values(array_unique([
                ...$candidate['intents'],
                ...($relationship['intents'] ?? []),
            ])),
            'primary_action' => $this->primaryAction(
                source: $source,
                candidate: $candidate,
                relationship: $relationship,
                direction: $direction,
                returnState: $returnState,
                walkHref: $walkHref,
                tab: $tab,
            ),
            'secondary_actions' => $this->secondaryActions(
                source: $source,
                candidate: $candidate,
                relationship: $relationship,
                direction: $direction,
                returnState: $returnState,
                tab: $tab,
            ),
            'request_form' => $tab === 'discover'
                ? [
                    'action' => 'send-pet-friend-request',
                    'source_pet' => $source,
                    'target' => $target,
                    'default_intent' => $this->defaultIntent($candidate, $intent),
                    'return_state' => $returnState,
                ]
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $relationship
     * @param  array<string, mixed>  $candidate
     * @param  array<string, string>  $returnState
     * @return array<string, mixed>
     */
    private function primaryAction(
        string $source,
        array $candidate,
        ?array $relationship,
        ?string $direction,
        array $returnState,
        ?string $walkHref,
        string $tab,
    ): array {
        $target = (string) $candidate['id'];

        if ($tab === 'discover') {
            return [
                'label' => __('messages.review_profile'),
                'icon' => 'circle-user-round',
                'variant' => 'paper',
                'href' => route($candidate['route_name'], $candidate['route_parameters']),
            ];
        }

        if ($relationship !== null && $relationship['status'] === 'pending') {
            return $direction === 'incoming'
                ? $this->action('accept-pet-friend-request', $source, $target, __('messages.accept'), 'user-check', 'primary', $returnState)
                : $this->action('cancel-pet-friend-request', $source, $target, __('messages.cancel_request'), 'user-x', 'paper', $returnState);
        }

        if ($relationship !== null && $relationship['status'] === 'paused') {
            return $this->action('toggle-pet-friend-pause', $source, $target, __('messages.restore_friendship'), 'play', 'primary', $returnState);
        }

        if ($walkHref !== null && ($tab === 'walks' || in_array('walk', $relationship['intents'] ?? [], true))) {
            return [
                'label' => __('messages.plan_a_walk'),
                'icon' => 'route',
                'variant' => 'primary',
                'href' => $walkHref,
            ];
        }

        if ($candidate['owner_conversation'] !== '') {
            return [
                'label' => __('messages.message_owner'),
                'icon' => 'message-circle',
                'variant' => 'primary',
                'href' => route('messages.index', [
                    'conversation' => $candidate['owner_conversation'],
                ]),
            ];
        }

        return [
            'label' => __('messages.view_profile'),
            'icon' => 'circle-user-round',
            'variant' => 'primary',
            'href' => route($candidate['route_name'], $candidate['route_parameters']),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $relationship
     * @param  array<string, mixed>  $candidate
     * @param  array<string, string>  $returnState
     * @return array<int, array<string, mixed>>
     */
    private function secondaryActions(
        string $source,
        array $candidate,
        ?array $relationship,
        ?string $direction,
        array $returnState,
        string $tab,
    ): array {
        $target = (string) $candidate['id'];
        $actions = [];

        if (
            $relationship !== null
            && in_array($relationship['status'], ['accepted', 'paused'], true)
            && $candidate['owner_conversation'] !== ''
        ) {
            $actions[] = [
                'label' => __('messages.message_prefix').$candidate['owner'],
                'icon' => 'message-circle',
                'variant' => 'paper',
                'href' => route('messages.index', [
                    'conversation' => $candidate['owner_conversation'],
                ]),
            ];
        }

        if ($relationship !== null && $relationship['status'] === 'pending' && $direction === 'incoming') {
            $actions[] = $this->action(
                'decline-pet-friend-request',
                $source,
                $target,
                __('messages.decline_request'),
                'user-x',
                'paper',
                $returnState,
            );
        }

        if ($relationship !== null && in_array($relationship['status'], ['accepted', 'paused'], true)) {
            $actions[] = $this->action(
                'toggle-pet-friend-pause',
                $source,
                $target,
                $relationship['status'] === 'paused' ? __('messages.restore_friendship') : __('messages.pause_friendship'),
                $relationship['status'] === 'paused' ? 'play' : 'pause',
                'paper',
                $returnState,
            );
            $actions[] = $this->action(
                'remove-pet-friendship',
                $source,
                $target,
                __('messages.remove_friendship'),
                'user-minus',
                'quiet',
                $returnState,
            );
        }

        if ($tab === 'discover') {
            $actions[] = $this->action(
                'dismiss-pet-friend-recommendation',
                $source,
                $target,
                __('messages.not_interested'),
                'eye-off',
                'quiet',
                $returnState,
            );
        }

        $actions[] = $this->action(
            'toggle-pet-friend-block',
            $source,
            $target,
            __('messages.block_pet_and_owner'),
            'ban',
            'quiet',
            $returnState,
        );
        $actions[] = [
            'label' => __('messages.report_profile'),
            'icon' => 'flag',
            'variant' => 'quiet',
            'href' => route('compose', [
                'kind' => 'report-profile',
                'target' => $target,
            ]),
        ];

        return $actions;
    }

    /**
     * @param  array<string, string>  $returnState
     * @return array<string, mixed>
     */
    private function action(
        string $action,
        string $source,
        string $target,
        string $label,
        string $icon,
        string $variant,
        array $returnState,
    ): array {
        return [
            'label' => $label,
            'icon' => $icon,
            'variant' => $variant,
            'endpoint' => route('actions.perform'),
            'payload' => [
                'action' => $action,
                'source_pet' => $source,
                'target' => $target,
                'label' => $label,
                ...$returnState,
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function returnState(
        string $tab,
        string $intent,
        string $sort,
        string $query,
    ): array {
        return array_filter([
            'pet_return_tab' => $tab,
            'pet_return_intent' => $intent,
            'pet_return_sort' => $sort,
            'pet_return_q' => $query,
        ], static fn (string $value): bool => $value !== '');
    }

    /**
     * @param  array<string, mixed>|null  $relationship
     * @return array{label: string, icon: string, tone: string}
     */
    private function status(?array $relationship, ?string $direction): array
    {
        if ($relationship === null) {
            return ['label' => __('messages.suggested_friend'), 'icon' => 'sparkles', 'tone' => 'surface'];
        }

        return match ($relationship['status']) {
            'accepted' => ['label' => __('messages.friends'), 'icon' => 'heart-handshake', 'tone' => 'mint'],
            'paused' => ['label' => __('messages.friendship_paused'), 'icon' => 'pause', 'tone' => 'surface'],
            'pending' => $direction === 'incoming'
                ? ['label' => __('messages.wants_to_connect'), 'icon' => 'inbox', 'tone' => 'sun']
                : ['label' => __('messages.request_sent'), 'icon' => 'send', 'tone' => 'surface'],
            default => ['label' => __('messages.suggested_friend'), 'icon' => 'sparkles', 'tone' => 'surface'],
        };
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function matches(array $item, string $intent, string $query): bool
    {
        if ($intent !== 'all' && ! in_array($intent, $item['intents'], true)) {
            return false;
        }

        if ($query === '') {
            return true;
        }

        $haystack = Str::lower(implode(' ', [
            $item['name'],
            $item['owner'],
            $item['species'],
            $item['breed'],
            $item['location'],
            $item['play_style'],
        ]));

        return str_contains($haystack, Str::lower($query));
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    private function compare(array $left, array $right, string $sort): int
    {
        return match ($sort) {
            'name' => strcasecmp((string) $left['name'], (string) $right['name']),
            'recent' => strcmp(
                (string) ($right['relationship']['requested_at'] ?? ''),
                (string) ($left['relationship']['requested_at'] ?? ''),
            ),
            default => $right['compatibility_score'] <=> $left['compatibility_score'],
        };
    }

    /**
     * @return array<int, array{label: string, value: string, detail: string}>
     */
    private function summary(string $source): array
    {
        $relationships = $this->friendState->forPet($source);
        $active = array_filter(
            $relationships,
            static fn (array $item): bool => in_array($item['status'], ['accepted', 'paused'], true),
        );
        $incoming = array_filter(
            $relationships,
            static fn (array $item): bool => $item['status'] === 'pending' && $item['recipient'] === $source,
        );
        $outgoing = array_filter(
            $relationships,
            static fn (array $item): bool => $item['status'] === 'pending' && $item['requester'] === $source,
        );
        $walks = array_filter(
            $active,
            static fn (array $item): bool => $item['status'] === 'accepted'
                && in_array('walk', $item['intents'], true),
        );

        return [
            ['label' => __('messages.friends'), 'value' => (string) count($active), 'detail' => __('messages.accepted_or_paused')],
            ['label' => __('messages.incoming'), 'value' => (string) count($incoming), 'detail' => __('messages.owner_review_needed')],
            ['label' => __('messages.sent'), 'value' => (string) count($outgoing), 'detail' => __('messages.awaiting_a_reply')],
            ['label' => __('messages.walk_ready'), 'value' => (string) count($walks), 'detail' => __('messages.accepted_walk_friends')],
        ];
    }

    /**
     * @param  array<int, array{label: string, value: string, detail: string}>  $summary
     * @return array<int, array<string, mixed>>
     */
    private function tabs(
        string $source,
        string $active,
        string $intent,
        string $sort,
        string $query,
        array $summary,
    ): array {
        $counts = [
            'friends' => $summary[0]['value'],
            'requests' => (string) ((int) $summary[1]['value'] + (int) $summary[2]['value']),
            'walks' => $summary[3]['value'],
        ];

        return array_map(function (array $tab) use ($source, $active, $intent, $sort, $query, $counts): array {
            return [
                ...$tab,
                'active' => $tab['key'] === $active,
                'count' => $counts[$tab['key']] ?? null,
                'href' => route('pet-friends.index', array_filter([
                    'pet' => str_replace('pet-', '', $source),
                    'tab' => $tab['key'],
                    'intent' => $intent,
                    'sort' => $sort,
                    'q' => $query,
                ], static fn (string $value): bool => $value !== '')),
            ];
        }, [
            ['key' => 'friends', 'label' => __('messages.friends'), 'icon' => 'heart-handshake'],
            ['key' => 'requests', 'label' => __('messages.requests'), 'icon' => 'inbox'],
            ['key' => 'discover', 'label' => __('messages.find_friends'), 'icon' => 'search'],
            ['key' => 'walks', 'label' => __('messages.walks'), 'icon' => 'route'],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function petSwitcher(
        string $source,
        string $tab,
        string $intent,
        string $sort,
        string $query,
    ): array {
        $pets = [];

        foreach ($this->catalog->owned() as $record) {
            $pets[] = [
                'label' => $record['name'],
                'image' => $record['image'],
                'image_alt' => $record['image_alt'],
                'active' => $record['id'] === $source,
                'href' => route('pet-friends.index', array_filter([
                    'pet' => $record['slug'],
                    'tab' => $tab,
                    'intent' => $intent,
                    'sort' => $sort,
                    'q' => $query,
                ], static fn (string $value): bool => $value !== '')),
            ];
        }

        return $pets;
    }

    /**
     * @return array<string, string>
     */
    private function intentOptions(): array
    {
        return [
            'all' => __('messages.all_friendship_types'),
            'walk' => __('messages.walk_companions'),
            'play' => __('messages.play_friends'),
            'training' => __('messages.training_partners'),
            'neighbor' => __('messages.nearby_friends'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function sortOptions(): array
    {
        return [
            'compatibility' => __('messages.best_shared_routines'),
            'recent' => __('messages.recent_activity'),
            'name' => __('messages.name'),
        ];
    }

    /**
     * @param  array<string, mixed>  $candidate
     */
    private function defaultIntent(array $candidate, string $selected): string
    {
        if ($selected !== 'all' && in_array($selected, $candidate['intents'], true)) {
            return $selected;
        }

        return $candidate['intents'][0] ?? 'friend';
    }

    /**
     * @param  array<string, mixed>  $candidate
     */
    private function walkHref(array $candidate): ?string
    {
        return in_array($candidate['slug'], ['mochi', 'juniper'], true)
            ? route('compose', ['kind' => 'walk', 'target' => $candidate['slug']])
            : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lastDismissed(
        string $source,
        string $tab,
        string $intent,
        string $sort,
        string $query,
    ): ?array {
        $target = $this->friendState->lastDismissed($source);
        $candidate = $target === null ? null : $this->catalog->find($target);

        if ($candidate === null || ! $this->friendState->recommendationIsDismissed($source, $target)) {
            return null;
        }

        return [
            'name' => $candidate['name'],
            'action' => $this->action(
                'undo-pet-friend-recommendation',
                $source,
                $target,
                __('messages.undo'),
                'undo-2',
                'paper',
                $this->returnState($tab, $intent, $sort, $query),
            ),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lastBlocked(
        string $source,
        string $tab,
        string $intent,
        string $sort,
        string $query,
    ): ?array {
        $target = $this->friendState->lastBlocked($source);
        $candidate = $target === null ? null : $this->catalog->find($target);

        if ($candidate === null || ! $this->state->isActive('blocks', $target)) {
            return null;
        }

        return [
            'name' => $candidate['name'],
            'action' => $this->action(
                'toggle-pet-friend-block',
                $source,
                $target,
                __('messages.unblock'),
                'shield-check',
                'paper',
                $this->returnState($tab, $intent, $sort, $query),
            ),
        ];
    }

    /**
     * @return array{icon: string, title: string, description: string}
     */
    private function emptyState(string $tab, string $pet): array
    {
        return match ($tab) {
            'requests' => [
                'icon' => 'inbox',
                'title' => __('messages.no_open_requests'),
                'description' => __('presentation.no_friendship_requests', ['pet' => $pet]),
            ],
            'discover' => [
                'icon' => 'search-x',
                'title' => __('messages.no_matching_recommendations'),
                'description' => __('messages.try_a_broader_friendship_type_or_clear_the_search'),
            ],
            'walks' => [
                'icon' => 'route-off',
                'title' => __('messages.no_walk_companions_yet'),
                'description' => __('messages.accept_a_walk_friendship_before_planning_a_meetup'),
            ],
            default => [
                'icon' => 'users-round',
                'title' => __('messages.no_friends_in_this_view'),
                'description' => __('messages.use_find_friends_to_start_an_owner_managed_introduction'),
            ],
        };
    }
}

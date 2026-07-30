@props([
    'item',
    'endpoint',
])

<article id="friend-{{ $item['key'] }}" class="pet-friend-card">
    <header class="pet-friend-card__header">
        <x-connection-identity :item="$item" />
        <x-status-badge
            :label="$item['status']['label']"
            :icon="$item['status']['icon']"
            :tone="$item['status']['tone']"
        />
    </header>

    @if ($item['relationship'])
        <div class="pet-friend-card__relationship">
            <span>
                <x-lucide-shield-check class="icon icon--sm" aria-hidden="true" />
                {{ $item['context'] }}
            </span>
            @if ($item['relationship']['intents'] !== [])
                <x-tag-list :items="$item['relationship']['intents']" />
            @endif
        </div>

        @if ($item['relationship']['message'])
            <blockquote class="pet-friend-card__message">
                “{{ $item['relationship']['message'] }}”
            </blockquote>
        @endif
    @endif

    <x-pet-compatibility :compatibility="$item['compatibility']" />

    @if ($item['request_form'])
        <x-pet-friend-request-form :item="$item" :endpoint="$endpoint" />
    @endif

    <footer class="pet-friend-card__actions">
        <x-action-control
            :label="$item['primary_action']['label']"
            :icon="$item['primary_action']['icon']"
            :variant="$item['primary_action']['variant']"
            size="regular"
            :endpoint="$item['primary_action']['endpoint'] ?? null"
            :payload="$item['primary_action']['payload'] ?? []"
            :href="$item['primary_action']['href'] ?? null"
        />

        @if ($item['secondary_actions'] !== [])
            <details class="friend-actions">
                <summary aria-label="{{ __('presentation.more_friendship_actions', ['name' => $item['name']]) }}" title="{{ __('ui.more_actions_f8d46c2570') }}">
                    <x-lucide-ellipsis class="icon" aria-hidden="true" />
                </summary>
                <div class="friend-actions__panel">
                    @forelse ($item['secondary_actions'] as $action)
                        <x-action-control
                            :label="$action['label']"
                            :icon="$action['icon']"
                            :variant="$action['variant']"
                            size="regular"
                            :endpoint="$action['endpoint'] ?? null"
                            :payload="$action['payload'] ?? []"
                            :href="$action['href'] ?? null"
                        />
                    @empty
                    @endforelse
                </div>
            </details>
        @endif
    </footer>
</article>

@props([
    'item',
    'variant' => 'list',
])

@php
    $primary = $item['primary_action'];
@endphp

<article
    id="connection-{{ $item['key'] }}"
    class="connection-card connection-card--{{ $variant }}"
    data-connection="{{ $item['key'] }}"
>
    <div class="connection-card__main">
        <x-connection-identity :item="$item" />

        @if ($item['context'])
            <p class="connection-card__context">
                <x-lucide-info class="icon icon--sm" aria-hidden="true" />
                {{ $item['context'] }}
            </p>
        @endif

        <x-recommendation-reason :item="$item" />
        <x-connection-state :item="$item" />
    </div>

    <div class="connection-card__actions">
        <x-action-control
            :label="$primary['label']"
            :icon="$primary['icon']"
            :variant="$primary['variant']"
            size="regular"
            :endpoint="$primary['endpoint'] ?? null"
            :payload="$primary['payload'] ?? []"
            :href="$primary['href'] ?? null"
            :active="$primary['active'] ?? $item['following']"
            :pressed="$primary['pressed'] ?? ($item['following'] ? true : null)"
        />

        @if ($item['secondary_actions'] !== [] || $item['notification_options'] !== [])
            <details class="connection-menu">
                <summary aria-label="More settings for {{ $item['name'] }}" title="More settings">
                    <x-lucide-ellipsis class="icon" aria-hidden="true" />
                </summary>

                <div class="connection-menu__panel">
                    @if ($item['notification_options'] !== [])
                        <p class="connection-menu__label">Notifications</p>
                        <div class="connection-menu__options">
                            @forelse ($item['notification_options'] as $option)
                                <x-action-form :action="route('actions.perform')" :payload="$option['payload']">
                                    <button
                                        type="submit"
                                        aria-pressed="{{ $option['active'] ? 'true' : 'false' }}"
                                        class="connection-menu__option"
                                    >
                                        @if ($option['active'])
                                            <x-lucide-check class="icon icon--sm" aria-hidden="true" />
                                        @else
                                            <span class="connection-menu__option-space" aria-hidden="true"></span>
                                        @endif
                                        <span>{{ $option['label'] }}</span>
                                    </button>
                                </x-action-form>
                            @empty
                            @endforelse
                        </div>
                    @endif

                    @if ($item['secondary_actions'] !== [])
                        <div class="connection-menu__commands">
                            @forelse ($item['secondary_actions'] as $action)
                                <x-action-control
                                    :label="$action['label']"
                                    :icon="$action['icon']"
                                    :variant="$action['variant']"
                                    size="regular"
                                    :endpoint="$action['endpoint']"
                                    :payload="$action['payload']"
                                />
                            @empty
                            @endforelse
                        </div>
                    @endif
                </div>
            </details>
        @endif
    </div>
</article>

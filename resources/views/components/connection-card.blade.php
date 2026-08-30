@props([
    'item',
    'variant' => 'list',
])

<article
    id="connection-{{ $item['key'] }}"
    class="connection-card connection-card--{{ $variant }}"
    data-connection="{{ $item['key'] }}"
>
    <div class="connection-card__main">
        <x-connection-identity :item="$item" />

        @if ($item['context'])
            <p class="connection-card__context">
                <x-ui-icon name="info" size="sm" />
                {{ $item['context'] }}
            </p>
        @endif

        <x-recommendation-reason :item="$item" />
        <x-connection-state :item="$item" />
    </div>

    <div class="connection-card__actions">
        <x-action-control
            :label="$item['primary_action']['label']"
            :icon="$item['primary_action']['icon']"
            :variant="$item['primary_action']['variant']"
            size="regular"
            :endpoint="$item['primary_action']['endpoint'] ?? null"
            :payload="$item['primary_action']['payload'] ?? []"
            :href="$item['primary_action']['href'] ?? null"
            :active="$item['primary_action']['active'] ?? $item['following']"
            :pressed="$item['primary_action']['pressed'] ?? ($item['following'] ? true : null)"
        />

        @if ($item['secondary_actions'] !== [] || $item['notification_options'] !== [])
            <details class="connection-menu">
                <summary aria-label="{{ __('presentation.more_settings_for', ['name' => $item['name']]) }}" title="{{ __('ui.more_settings') }}">
                    <x-ui-icon name="ellipsis" />
                </summary>

                <div class="connection-menu__panel">
                    @if ($item['notification_options'] !== [])
                        <p class="connection-menu__label">{{ __('ui.notifications') }}</p>
                        <div class="connection-menu__options">
                            @forelse ($item['notification_options'] as $option)
                                <x-action-form :action="route('actions.perform')" :payload="$option['payload']">
                                    <button
                                        type="submit"
                                        aria-pressed="{{ $option['active'] ? 'true' : 'false' }}"
                                        class="connection-menu__option"
                                    >
                                        @if ($option['active'])
                                            <x-ui-icon name="check" size="sm" />
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

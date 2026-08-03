@props([
    'item',
])

<div class="connection-identity">
    <a
        href="{{ $item['href'] }}"
        class="connection-identity__avatar"
        aria-label="{{ __('presentation.open_profile', ['name' => $item['name']]) }}"
    >
        <x-avatar
            :src="$item['image']"
            :alt="$item['image_alt']"
            size="profile"
            lazy
        />
    </a>

    <div class="connection-identity__body">
        <div class="connection-identity__heading">
            <div class="min-w-0">
                <h3 class="connection-identity__name">
                    <a href="{{ $item['href'] }}">{{ $item['name'] }}</a>
                </h3>
                <p class="connection-identity__handle">{{ $item['handle'] }}</p>
            </div>

            <div class="connection-identity__badges">
                @if ($item['verified'])
                    <x-status-badge label="{{ __('ui.verified_4f7838402f') }}" icon="badge-check" tone="mint" />
                @endif
                @if ($item['private'])
                    <x-status-badge label="{{ __('ui.private_c63eb6720c') }}" icon="lock-keyhole" tone="surface" />
                @endif
            </div>
        </div>

        <p class="connection-identity__type">{{ $item['type_label'] }}</p>
        <p class="connection-identity__description">{{ $item['description'] }}</p>

        <div class="connection-identity__meta">
            <span>
                <x-ui-icon name="map-pin" size="sm" />
                {{ $item['location'] }}
            </span>
            <span>
                <x-ui-icon name="users-round" size="sm" />
                {{ $item['followers'] }}
            </span>
        </div>
    </div>
</div>

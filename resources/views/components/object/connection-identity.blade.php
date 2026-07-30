@props([
    'item',
])

<div class="connection-identity">
    <a
        href="{{ $item['href'] }}"
        class="connection-identity__avatar"
        aria-label="Open {{ $item['name'] }} profile"
    >
        <x-ui.avatar
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
                    <x-ui.status-badge label="Verified" icon="badge-check" tone="mint" />
                @endif
                @if ($item['private'])
                    <x-ui.status-badge label="Private" icon="lock-keyhole" tone="surface" />
                @endif
            </div>
        </div>

        <p class="connection-identity__type">{{ $item['type_label'] }}</p>
        <p class="connection-identity__description">{{ $item['description'] }}</p>

        <div class="connection-identity__meta">
            <span>
                <x-lucide-map-pin class="icon icon--sm" aria-hidden="true" />
                {{ $item['location'] }}
            </span>
            <span>
                <x-lucide-users-round class="icon icon--sm" aria-hidden="true" />
                {{ $item['followers'] }}
            </span>
        </div>
    </div>
</div>

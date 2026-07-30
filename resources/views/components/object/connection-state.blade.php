@props([
    'item',
])

@if ($item['following'] || $item['favorite'] || $item['muted'] || $item['notification_level'])
    <div class="connection-state" aria-label="Subscription status">
        @if ($item['following'])
            <x-ui.status-badge label="Following" icon="user-check" tone="mint" />
        @endif
        @if ($item['favorite'])
            <x-ui.status-badge label="Favorite" icon="star" tone="sun" />
        @endif
        @if ($item['muted'])
            <x-ui.status-badge label="Muted" icon="volume-x" tone="surface" />
        @endif
        @if ($item['notification_level'])
            <x-ui.status-badge
                :label="'Alerts: '.str_replace('-', ' ', $item['notification_level'])"
                icon="bell"
                tone="surface"
            />
        @endif
    </div>
@endif

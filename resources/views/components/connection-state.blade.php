@props([
    'item',
])

@if ($item['following'] || $item['favorite'] || $item['muted'] || $item['notification_level'])
    <div class="connection-state" aria-label="{{ __('ui.subscription_status') }}">
        @if ($item['following'])
            <x-status-badge label="{{ __('ui.following') }}" icon="user-check" tone="mint" />
        @endif
        @if ($item['favorite'])
            <x-status-badge label="{{ __('ui.favorite') }}" icon="star" tone="sun" />
        @endif
        @if ($item['muted'])
            <x-status-badge label="{{ __('ui.muted') }}" icon="volume-x" tone="surface" />
        @endif
        @if ($item['notification_level'])
            <x-status-badge
                :label="__('ui.alerts').' '.str_replace('-', ' ', $item['notification_level'])"
                icon="bell"
                tone="surface"
            />
        @endif
    </div>
@endif

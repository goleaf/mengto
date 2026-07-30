@props([
    'item',
])

@if ($item['following'] || $item['favorite'] || $item['muted'] || $item['notification_level'])
    <div class="connection-state" aria-label="{{ __('ui.subscription_status_f0723c4e4a') }}">
        @if ($item['following'])
            <x-status-badge label="{{ __('ui.following_344b4271ca') }}" icon="user-check" tone="mint" />
        @endif
        @if ($item['favorite'])
            <x-status-badge label="{{ __('ui.favorite_ea713ecd85') }}" icon="star" tone="sun" />
        @endif
        @if ($item['muted'])
            <x-status-badge label="{{ __('ui.muted_2346f214ad') }}" icon="volume-x" tone="surface" />
        @endif
        @if ($item['notification_level'])
            <x-status-badge
                :label="__('ui.alerts_b8a08c43c9').' '.str_replace('-', ' ', $item['notification_level'])"
                icon="bell"
                tone="surface"
            />
        @endif
    </div>
@endif

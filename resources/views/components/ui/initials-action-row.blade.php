@props([
    'initials',
    'title',
    'detail',
    'detailIcon' => null,
    'actionLabel',
    'actionIcon',
    'actionEndpoint' => null,
    'actionPayload' => [],
    'active' => false,
    'activeLabel' => null,
    'activeIcon' => null,
])

<div {{ $attributes->class(['mt-auto flex items-center gap-3 pt-5']) }}>
    <x-ui.initials-avatar :initials="$initials" />

    <div class="min-w-0">
        <p class="text-xs font-semibold text-paw-ink">{{ $title }}</p>

        @if ($detailIcon)
            <x-ui.icon-text :icon="$detailIcon" class="mt-0.5">{{ $detail }}</x-ui.icon-text>
        @else
            <p class="mt-0.5 text-xs text-paw-muted">{{ $detail }}</p>
        @endif
    </div>

    <x-ui.action-control
        :label="$actionLabel"
        :icon="$actionIcon"
        :endpoint="$actionEndpoint"
        :payload="$actionPayload"
        :active="$active"
        :active-label="$activeLabel"
        :active-icon="$activeIcon"
        :pressed="$actionEndpoint ? $active : null"
        variant="paper"
        class="ml-auto shrink-0"
    />
</div>

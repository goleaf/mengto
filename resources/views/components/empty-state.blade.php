@props([
    'icon',
    'title',
    'description' => null,
    'compact' => false,
    'href' => null,
    'actionLabel' => 'Clear filters',
    'actionIcon' => 'rotate-ccw',
])

<div
    {{ $attributes->merge(['role' => 'status'])->class([
        'empty-state',
        'empty-state--compact' => $compact,
    ]) }}
>
    <span class="empty-state__icon" aria-hidden="true">
        <x-dynamic-component :component="'lucide-'.$icon" class="icon" aria-hidden="true" />
    </span>
    <h3 class="empty-state__title">{{ $title }}</h3>

    @if ($description)
        <p class="empty-state__description">{{ $description }}</p>
    @endif

    @if ($href)
        <x-action-control
            :href="$href"
            :label="$actionLabel"
            :icon="$actionIcon"
            variant="paper"
            size="regular"
            class="mt-4"
        />
    @endif
</div>

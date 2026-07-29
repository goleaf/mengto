@props([
    'icon',
    'title',
    'description' => null,
    'compact' => false,
])

<div
    {{ $attributes->merge(['role' => 'status'])->class([
        'pc-empty-state',
        'pc-empty-state--compact' => $compact,
    ]) }}
>
    <span class="pc-empty-state__icon" aria-hidden="true">
        <x-dynamic-component :component="'lucide-'.$icon" class="pc-icon" aria-hidden="true" />
    </span>
    <h3 class="pc-empty-state__title">{{ $title }}</h3>

    @if ($description)
        <p class="pc-empty-state__description">{{ $description }}</p>
    @endif
</div>

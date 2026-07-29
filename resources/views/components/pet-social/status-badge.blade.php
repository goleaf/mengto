@props([
    'label',
    'icon' => null,
    'tone' => 'surface',
    'size' => 'compact',
])

<span
    {{ $attributes->class([
        'pc-status-badge',
        'pc-status-badge--'.$tone,
        'pc-status-badge--'.$size,
    ]) }}
>
    @if ($icon)
        <x-dynamic-component :component="'lucide-'.$icon" class="pc-icon pc-icon--sm" aria-hidden="true" />
    @endif
    <span>{{ $label }}</span>
</span>

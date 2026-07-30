@props([
    'label',
    'icon' => null,
    'tone' => 'surface',
    'size' => 'compact',
])

<span
    {{ $attributes->class([
        'status-badge',
        'status-badge--'.$tone,
        'status-badge--'.$size,
    ]) }}
>
    @if ($icon)
        <x-dynamic-component :component="'lucide-'.$icon" class="icon icon--sm" aria-hidden="true" />
    @endif
    <span>{{ $label }}</span>
</span>

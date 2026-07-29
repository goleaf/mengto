@props([
    'label',
    'icon' => null,
    'variant' => 'surface',
    'size' => 'compact',
])

<button type="button" aria-disabled="true" disabled
    {{ $attributes->class([
        'pc-action',
        'pc-action--'.$variant,
        'pc-action--'.$size,
    ]) }}
>
    @if ($icon)
        <x-dynamic-component :component="'lucide-'.$icon" class="pc-icon pc-icon--sm" aria-hidden="true" />
    @endif
    <span>{{ $label }}</span>
</button>

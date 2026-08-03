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
        <x-ui-icon size="sm" :name="$icon" />
    @endif
    <span>{{ $label }}</span>
</span>

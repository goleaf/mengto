@props([
    'label',
    'icon' => null,
    'active' => false,
    'size' => 'compact',
    'name' => 'filter',
    'value' => null,
    'type' => 'button',
])

<button
    type="{{ $type }}"
    name="{{ $name }}"
    value="{{ $value }}"
    aria-pressed="{{ $active ? 'true' : 'false' }}"
    {{ $attributes->class([
        'filter-chip',
        'filter-chip--'.$size,
    ]) }}
>
    @if ($icon || $active)
        <x-ui-icon size="sm" :name="($icon ?? 'check')" />
    @endif
    <span>{{ $label }}</span>
</button>

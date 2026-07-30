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
    value="{{ $value ?? \Illuminate\Support\Str::slug($label) }}"
    aria-pressed="{{ $active ? 'true' : 'false' }}"
    {{ $attributes->class([
        'filter-chip',
        'filter-chip--'.$size,
    ]) }}
>
    @if ($icon || $active)
        <x-dynamic-component :component="'lucide-'.($icon ?? 'check')" class="icon icon--sm" aria-hidden="true" />
    @endif
    <span>{{ $label }}</span>
</button>

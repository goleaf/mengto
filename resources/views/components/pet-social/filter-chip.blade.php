@props([
    'label',
    'icon' => null,
    'active' => false,
    'size' => 'compact',
])

<button
    type="button"
    aria-pressed="{{ $active ? 'true' : 'false' }}"
    aria-disabled="true"
    disabled
    {{ $attributes->class([
        'pc-filter-chip',
        'pc-filter-chip--'.$size,
    ]) }}
>
    @if ($icon || $active)
        <x-dynamic-component :component="'lucide-'.($icon ?? 'check')" class="pc-icon pc-icon--sm" aria-hidden="true" />
    @endif
    <span>{{ $label }}</span>
</button>

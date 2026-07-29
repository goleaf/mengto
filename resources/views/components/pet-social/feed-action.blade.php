@props([
    'label',
    'icon',
])

<button type="button" aria-disabled="true" disabled {{ $attributes->class('pc-feed-action') }}>
    <x-dynamic-component :component="'lucide-'.$icon" class="pc-icon pc-icon--sm" aria-hidden="true" />
    <span>{{ $label }}</span>
</button>

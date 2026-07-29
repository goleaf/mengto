@props(['icon'])

<span {{ $attributes->class(['pc-meta']) }}>
    <x-dynamic-component :component="'lucide-'.$icon" class="pc-icon pc-icon--sm" aria-hidden="true" />
    <span>{{ $slot }}</span>
</span>

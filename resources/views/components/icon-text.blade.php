@props(['icon'])

<span {{ $attributes->class(['meta']) }}>
    <x-dynamic-component :component="'lucide-'.$icon" class="icon icon--sm" aria-hidden="true" />
    <span>{{ $slot }}</span>
</span>

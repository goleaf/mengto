@props([
    'href',
    'icon' => null,
    'variant' => 'inline',
])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'pc-text-link',
        'pc-text-link--'.$variant,
    ]) }}
>
    @if ($icon)
        <x-dynamic-component :component="'lucide-'.$icon" class="pc-icon pc-icon--sm" aria-hidden="true" />
    @endif
    <span>{{ $slot }}</span>
</a>

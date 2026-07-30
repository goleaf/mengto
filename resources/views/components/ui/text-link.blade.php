@props([
    'href' => null,
    'routeName' => null,
    'routeParameters' => [],
    'icon' => null,
    'variant' => 'inline',
])

@php
    $resolvedHref = $href ?? ($routeName ? route($routeName, $routeParameters) : null);
@endphp

<a
    href="{{ $resolvedHref }}"
    {{ $attributes->class([
        'text-link',
        'text-link--'.$variant,
    ]) }}
>
    @if ($icon)
        <x-dynamic-component :component="'lucide-'.$icon" class="icon icon--sm" aria-hidden="true" />
    @endif
    <span>{{ $slot }}</span>
</a>

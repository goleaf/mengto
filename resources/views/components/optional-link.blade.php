@props([
    'href' => null,
    'routeName' => null,
    'routeParameters' => [],
    'variant' => 'inline',
])

@if ($href || $routeName)
    <x-text-link
        :href="$href"
        :route-name="$routeName"
        :route-parameters="$routeParameters"
        :variant="$variant"
        {{ $attributes }}
    >
        {{ $slot }}
    </x-text-link>
@else
    <span>{{ $slot }}</span>
@endif

@props([
    'href' => null,
    'routeName' => null,
    'routeParameters' => [],
    'variant' => 'inline',
])

@if ($href || $routeName)
    <x-ui.text-link
        :href="$href"
        :route-name="$routeName"
        :route-parameters="$routeParameters"
        :variant="$variant"
        {{ $attributes }}
    >
        {{ $slot }}
    </x-ui.text-link>
@else
    <span>{{ $slot }}</span>
@endif
